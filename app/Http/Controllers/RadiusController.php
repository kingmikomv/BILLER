<?php

namespace App\Http\Controllers;

use RouterOS\Query;
use RouterOS\Client;
use App\Models\VpnRadius;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RadiusController extends Controller
{
    public function index()
    {
        $vpnUsers = VpnRadius::where('user_id', auth()->user()->id)->get(); // ambil semua user VPN
        //return view('vpn.index', compact('vpnUsers'));
        $nasList = DB::connection('freeradius')->table('nas')
            ->where('user_id', Auth::id())
            ->get();
        return view('ROLE.MEMBER.RADIUS.index', compact('vpnUsers', 'nasList'));
    }
    public function tambahVpnRadius(Request $request)
    {


        try {
            $mikrotik = [
                'host' => 'id-2.aqtnetwork.my.id',
                'port_api' => 8728,
                'username' => 'teknisi1922',
                'password' => 'teknisi1922',
            ];

            // Buat koneksi ke Mikrotik
            $client = new Client([
                'host' => $mikrotik['host'] . ':' . $mikrotik['port_api'],
                'user' => $mikrotik['username'],
                'pass' => $mikrotik['password'],
            ]);

            // Cek apakah username sudah ada di Mikrotik
            $query = (new Query('/ppp/secret/print'))->where('name', $request->username);
            $existing = $client->query($query)->read();

            if (!empty($existing)) {
                return back()->with('error', 'Username tersebut sudah terdaftar');
            }

            // Ambil semua PPP secret yang sudah ada
            $allSecrets = $client->query(new Query('/ppp/secret/print'))->read();
            $usedRemotes = collect($allSecrets)->pluck('remote-address')->toArray();

            $local = '172.20.0.1'; // Gateway tetap
            $remote = null;

            // Loop X dari 0 sampai 254
            for ($x = 0; $x <= 254; $x++) {
                // Loop Y hanya GENAP dari 2 sampai 254
                for ($y = 2; $y <= 254; $y += 2) {
                    $remoteCandidate = "172.20.$x.$y";

                    if (!in_array($remoteCandidate, $usedRemotes)) {
                        $remote = $remoteCandidate;
                        break 2; // keluar dari kedua loop saat ditemukan IP tersedia
                    }
                }
            }

            if (!$remote) {
                return back()->with('error', 'Tidak ada IP remote yang tersedia untuk PPP Secret.');
            }

            // Tambahkan user baru ke Mikrotik
            $query = new Query('/ppp/secret/add');
            $query->equal('name', $request->username)
                ->equal('password', $request->password)
                ->equal('service', 'any')
                ->equal('comment', 'VPN_RADIUS_' . auth()->user()->name)
                ->equal('profile', 'RADVPN')
                ->equal('local-address', $local)
                ->equal('remote-address', $remote);

            $client->query($query)->read();


            // Simpan ke database lokal Laravel
            VpnRadius::create([
                'user_id' => Auth::id(),
                'username' => $request->username,
                'password' => $request->password,
                'profile' => 'RADIUS',
                'nas' => 'null',
                'local_address' => $local,
                'remote_address' => $remote,
            ]);

            return back()->with('success', 'Akun VPN berhasil ditambahkan ke Mikrotik dan database.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal koneksi ke Mikrotik: ' . $e->getMessage());
        }
    }
    public function tambahNasRadius(Request $request)
    {


        // Cek apakah nasname sudah ada di DB freeradius
        $exists = DB::connection('freeradius')->table('nas')
            ->where('nasname', $request->nasname)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'IP NAS sudah ada di database.');
        }

        // Insert jika belum ada
        DB::connection('freeradius')->table('nas')->insert([
            'nasname' => $request->nasname,
            'shortname' => $request->shortname,
            'type' => $request->type ?? 'other',
            'secret' => $request->secret,
            'description' => $request->description,
            'user_id' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'NAS berhasil ditambahkan.');
    }

    public function hapusVpnRadius($id)
    {
        // Ambil data user dari database
        $user = VpnRadius::findOrFail($id);

        // Koneksi ke Mikrotik
        $client = new Client([
            'host' => 'id-2.aqtnetwork.my.id',
            'port' => 8728,
            'user' => 'teknisi1922',
            'pass' => 'teknisi1922',
        ]);

        try {
            // Cari user berdasarkan remote-address
            $query = (new Query('/ppp/secret/print'))->where('remote-address', $user->remote_address);
            $results = $client->query($query)->read();

            if (count($results) > 0) {
                $mikrotikId = $results[0]['.id'];

                // Hapus dari Mikrotik
                $deleteQuery = new Query('/ppp/secret/remove');
                $deleteQuery->equal('.id', $mikrotikId);
                $client->query($deleteQuery)->read();
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus dari Mikrotik: ' . $e->getMessage());
        }

        // Hapus dari database
        $user->delete();

        return redirect()->back()->with('success', 'User VPN berhasil dihapus dari database & Mikrotik.');
    }
     public function hapusNasRadius($id)
    {
        $userId = Auth::id();

        // Cari NAS berdasarkan id dan user_id agar tidak sembarangan menghapus NAS user lain
        $nas = DB::connection('freeradius')->table('nas')
            ->where('user_id', $userId)
            
            ->first();
        $nasname = $nas->nasname;

        if (!$nas) {
            return redirect()->back()->with('error', 'NAS tidak ditemukan atau Anda tidak memiliki izin menghapusnya.');
        }

        // Hapus NAS dari database freeradius
        DB::connection('freeradius')->table('nas')
            ->where('nasname', $nasname)
            ->where('user_id', $userId)
            ->delete();

        return redirect()->back()->with('success', 'NAS berhasil dihapus.');
    }
}
