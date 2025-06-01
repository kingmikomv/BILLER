<?php

namespace App\Http\Controllers;

use RouterOS\Query;
use RouterOS\Client;
use App\Models\VpnRadius;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RadiusController extends Controller
{
    public function index()
    {
         $vpnUsers = VpnRadius::where('user_id', auth()->user()->id)->get(); // ambil semua user VPN
    //return view('vpn.index', compact('vpnUsers'));
        return view('ROLE.MEMBER.RADIUS.index', compact('vpnUsers'));
    }
    public function tambahVpnRadius(Request $request)
    {
       

    try {
        $mikrotik = [
            'host' => 'id-1.aqtnetwork.my.id',
            'port_api' => 8728,
            'username' => 'admin',
            'password' => 'bakpao1922',
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
            return back()->with('error', 'Username tersebut sudah ada di Mikrotik.');
        }

        // // Ambil semua PPP secret yang sudah ada
        // $allSecrets = $client->query(new Query('/ppp/secret/print'))->read();
        // $usedLocals = collect($allSecrets)->pluck('local-address')->toArray();

        // // Cari pasangan IP yang belum dipakai
        // $base = '172.50';
        // $local = null;
        // $remote = null;

        // for ($x = 10; $x < 255; $x++) {
        //     $localCandidate = "$base.$x.1";
        //     $remoteCandidate = "$base.$x.10";

        //     if (!in_array($localCandidate, $usedLocals)) {
        //         $local = $localCandidate;
        //         $remote = $remoteCandidate;
        //         break;
        //     }
        // }

        // if (!$local || !$remote) {
        //     return back()->with('error', 'Tidak ada IP yang tersedia untuk PPP Secret.');
        // }

        // Ambil semua PPP secret yang sudah ada
$allSecrets = $client->query(new Query('/ppp/secret/print'))->read();
$usedLocals = collect($allSecrets)->pluck('local-address')->toArray();

// Cari pasangan IP yang belum dipakai
$base = '172.50';
$remoteOffset = 10;
$local = null;
$remote = null;

for ($x = 10; $x < 255; $x++) {
    $localCandidate = "$base.$x.1";
    $remoteCandidate = "$base.$x." . (1 + $remoteOffset); // misal jadi 11, 12, 13 dst

    if (!in_array($localCandidate, $usedLocals)) {
        $local = $localCandidate;
        $remote = $remoteCandidate;
        break;
    }
}

if (!$local || !$remote) {
    return back()->with('error', 'Tidak ada IP yang tersedia untuk PPP Secret.');
}


        // Tambahkan user baru ke Mikrotik
        $query = new Query('/ppp/secret/add');
        $query->equal('name', $request->username)
            ->equal('password', $request->password)
            ->equal('service', 'pptp')
            ->equal('comment', 'VPN_RADIUS')
            ->equal('profile', 'RADIUS')
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
}
