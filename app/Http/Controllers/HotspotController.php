<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
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
          $vouchers = Voucher::with('hotspotProfile.user')->latest()->get(); // include user dari profile
    $profiles = HotspotProfile::with('user')->get();
        return view('ROLE.MEMBER.HOTSPOT.USER.index', compact('profiles', 'vouchers'));

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
        // Validasi data
        $request->validate([
            'hotspot_profile_id' => 'required|exists:hotspot_profiles,id',
            'quantity' => 'required|integer|min:1|max:1000',
            'user_model' => 'required|in:username_equals_password,username_plus_password',
            'char_type' => 'required|in:uppercase,lowercase,numbers,uppercase_numbers',
            'prefix' => 'nullable|string|max:10',
            'length' => 'required|integer|min:4|max:12',
        ]);

        try {
            $hotspotProfileId = $request->hotspot_profile_id;
            $quantity = (int) $request->quantity;
            $userModel = $request->user_model;
            $charType = $request->char_type;
            $prefix = $request->prefix ?? '';
            $length = (int) $request->length;

            $profile = HotspotProfile::findOrFail($hotspotProfileId);

            $vouchers = [];
            $radcheckInsert = [];
            $radusergroupInsert = [];
            $radreplyInsert = [];

            for ($i = 0; $i < $quantity; $i++) {
                $randomStr = $this->generateRandomString($charType, $length);
                $username = $prefix . $randomStr;
                $password = ($userModel === 'username_equals_password') ? $username : $randomStr;

                $expiredAt = null;
                if ($profile->validity) {
                    $expiredAt = now()->addDays($profile->validity);
                } elseif ($profile->uptime) {
                    $expiredAt = now()->addHours($profile->uptime);
                }

                $vouchers[] = [
                    'user_id' => auth()->id(),
                    'hotspot_profile_id' => $profile->id,
                    'username' => $username,
                    'password' => $password,
                    'user_model' => $userModel,
                    'char_type' => $charType,
                    'prefix' => $prefix,
                    'status' => 'active',
                    'expired_at' => $expiredAt,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $radcheckInsert[] = [
                    'username' => $username,
                    'attribute' => 'Cleartext-Password',
                    'op' => ':=',
                    'value' => $password,
                ];

                $radusergroupInsert[] = [
                    'username' => $username,
                    'groupname' => $profile->groupname,
                    'priority' => 1,
                ];

                
                // Tambahkan Session-Timeout jika ada uptime
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

            // Simpan ke database Laravel
            DB::table('vouchers')->insert($vouchers);

            // Simpan ke FreeRADIUS
            DB::connection('freeradius')->table('radcheck')->insert($radcheckInsert);
            DB::connection('freeradius')->table('radusergroup')->insert($radusergroupInsert);
            if (!empty($radreplyInsert)) {
                DB::connection('freeradius')->table('radreply')->insert($radreplyInsert);
            }

            return redirect()->back()->with('success', "$quantity voucher berhasil dibuat dan disimpan di FreeRADIUS.");
        } catch (\Throwable $e) {
            Log::error('Gagal membuat voucher', [
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
