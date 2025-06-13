<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use App\Models\VpnRadius;
use Illuminate\Http\Request;
use App\Models\HotspotProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class HotspotController extends Controller
{
    //

    public function userHotspot()
    {
        $userId = Auth::id(); // Ambil ID user yang login

        $vouchers = Voucher::with('hotspotProfile.user')->latest()->get(); // include user dari profile
        $nasList = DB::table('vpn_radius')->where('user_id', $userId)->get();

        $profiles = HotspotProfile::with('user')->get();
        return view('ROLE.MEMBER.HOTSPOT.USER.index', compact('profiles', 'vouchers', 'nasList'));

    }
    public function profileHotspot()
    {
        return view('ROLE.MEMBER.HOTSPOT.PROFILE.index');
    }
    public function uploadProfile(Request $request)
    {
        // Ambil input manual
        $name = $request->input('name');
        $price = $request->input('price');
        $resellerPrice = $request->input('reseller_price');
        $rateUp = $request->input('rate_up');
        $rateDown = $request->input('rate_down');
        $uptime = $request->input('uptime');
        $validity = $request->input('validity'); // Tetap disimpan di Laravel untuk digunakan saat generate voucher
        $groupname = $request->input('groupname');

        // Validasi minimal
        if (!$name || !$price || !$rateUp || !$rateDown || !$groupname) {
            return back()->withErrors('Field wajib harus diisi');
        }

        // Simpan ke tabel Laravel hotspot_profiles
        $profile = HotspotProfile::create([
            'user_id' => auth()->id(),
            'name' => $name,
            'price' => $price,
            'reseller_price' => $resellerPrice,
            'rate_up' => $rateUp,
            'rate_down' => $rateDown,
            'uptime' => $uptime,
            'validity' => $validity,
            'groupname' => $groupname,
        ]);

        // Prepare data untuk radgroupreply
        $rate_limit = $rateDown . 'M/' . $rateUp . 'M';
        $groupReply = [
            [
                'groupname' => $groupname,
                'attribute' => 'Mikrotik-Rate-Limit',
                'op' => ':=',
                'value' => $rate_limit,
            ],
        ];

        if ($uptime) {
            $groupReply[] = [
                'groupname' => $groupname,
                'attribute' => 'Session-Timeout',
                'op' => ':=',
                'value' => $uptime * 3600, // convert jam ke detik
            ];
        }

        // Hapus bagian Expiration dari sini, agar hanya dimasukkan per-voucher

        // Insert ke tabel radgroupreply di FreeRADIUS
        DB::connection('freeradius')->table('radgroupreply')->insert($groupReply);

        return redirect()->back()->with('success', 'Hotspot Profile berhasil ditambahkan!');
    }



    public function tambahVoucher(Request $request)
    {
        try {
            \Log::info('Mulai tambahVoucher', [
                'request' => $request->all()
            ]);

            $nasId = $request->nas_id;
            //dd($nasId);
            $hotspotProfileId = $request->hotspot_profile_id;
            $quantity = (int) $request->quantity;
            $userModel = $request->user_model;
            $charType = $request->char_type;
            $prefix = $request->prefix ?? '';
            $length = (int) $request->length;

            \Log::info('Parameter sudah diambil', compact('nasId', 'hotspotProfileId', 'quantity', 'userModel', 'charType', 'prefix', 'length'));

            $profile = HotspotProfile::findOrFail($hotspotProfileId);

            \Log::info('Profile hotspot ditemukan', ['profile' => $profile]);

            $vouchers = [];
            $radcheckInsert = [];
            $radusergroupInsert = [];
            $radreplyInsert = [];

            // Jika bukan all, ambil NAS dari database freeradius
            $nas = null;
            if ($nasId !== 'all') {
                $nas = DB::connection('freeradius')->table('nas')->where('nasname', $nasId)->first();
                if (!$nas) {
                    \Log::error("NAS dengan ID $nasId tidak ditemukan di database FreeRADIUS.");
                    return back()->withErrors("NAS dengan ID $nasId tidak ditemukan di database FreeRADIUS.");
                }
                \Log::info('NAS ditemukan', ['nas' => $nas]);
            }

            $dataNASRemote = VpnRadius::with('user')
                ->where('remote_address', $nasId)
                ->first();
            for ($i = 0; $i < $quantity; $i++) {
                $randomStr = $this->generateRandomString($charType, $length);
                $username = $prefix . $randomStr;
                $password = ($userModel === 'username_equals_password') ? $username : $randomStr;

                // $expiredAt = null;
                // if ($profile->validity) {
                //     $expiredAt = now()->addDays($profile->validity);
                // } elseif ($profile->uptime) {
                //     $expiredAt = now()->addHours($profile->uptime);
                // }

                $remoteAddress = ($nasId === 'all') ? null : $nas->nasname;

                $vouchers[] = [
                    'user_id' => auth()->id(),
                    'hotspot_profile_id' => $profile->id,
                    'nas' => $dataNASRemote->username,
                    'username' => $username,
                    'password' => $password,
                    'user_model' => $userModel,
                    'char_type' => $charType,
                    'prefix' => $prefix,
                    'status' => 'active',
                    'expired_at' => NULL,
                    'login_at' => NULL,
                    'delete_at' => NULL,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $radcheckInsert[] = [
                    'username' => $username,
                    'attribute' => 'Cleartext-Password',
                    'op' => ':=',
                    'value' => $password,
                ];


                if ($nasId !== 'all') {
    $radcheckInsert[] = [
        'username' => $username,
        'attribute' => 'NAS-IP-Address',
        'op' => ':=',
        'value' => $nas->nasname,
    ];
}


                $radusergroupInsert[] = [
                    'username' => $username,
                    'groupname' => $profile->groupname,
                    'priority' => 1,
                ];

                if ($profile->uptime) {
                    $timeoutInSeconds = $profile->uptime * 3600;
                    $radreplyInsert[] = [
                        'username' => $username,
                        'attribute' => 'Session-Timeout',
                        'op' => ':=',
                        'value' => $timeoutInSeconds,
                    ];
                }
            }

            \Log::info('Data voucher dan radcheck sudah siap', [
                'vouchers_count' => count($vouchers),
                'radcheck_count' => count($radcheckInsert),
                'radusergroup_count' => count($radusergroupInsert),
                'radreply_count' => count($radreplyInsert),
            ]);

            DB::table('vouchers')->insert($vouchers);
            DB::connection('freeradius')->table('radcheck')->insert($radcheckInsert);
            DB::connection('freeradius')->table('radusergroup')->insert($radusergroupInsert);

            if (!empty($radreplyInsert)) {
                DB::connection('freeradius')->table('radreply')->insert($radreplyInsert);
            }

            \Log::info('Voucher berhasil disimpan');

            return redirect()->back()->with('success', "$quantity voucher berhasil dibuat dan disimpan di FreeRADIUS.");
        } catch (\Throwable $e) {
            \Log::error('Gagal membuat voucher', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withErrors('Terjadi kesalahan saat membuat voucher. Cek log untuk detail.');
        }
    }






    private function generateRandomString($type, $length)
    {

        $characters = match ($type) {
            'uppercase' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'lowercase' => 'abcdefghijklmnopqrstuvwxyz',
            'numbers' => '0123456789',
            'uppercase_numbers' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',
            default => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',
        };

        $result = '';
        $max = strlen($characters) - 1;

        for ($i = 0; $i < $length; $i++) {
            $result .= $characters[random_int(0, $max)];
        }

        return $result;
    }

}
