<?php

namespace App\Http\Controllers;

use RouterOS\Query;
use RouterOS\Client;
use App\Models\Mikrotik;
use App\Models\PaketPppoe;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Helpers\ActivityLogger;
use Illuminate\Support\Facades\DB;

class MikrotikController extends Controller
{

    public function router()
    {
        $user = auth()->user(); // Ambil user yang sedang login
        $userId = $user->id;

        // Cek apakah user adalah member atau bukan
        if ($user->role == 'member') {
            // Jika member, ambil mikrotik berdasarkan user_id
            $mikrotik = Mikrotik::where('user_id', $userId)->get();
        } else {
            // Jika bukan member (misal teknisi), ambil mikrotik berdasarkan parent_id
            $mikrotik = Mikrotik::where('user_id', $user->parent_id)->get();
        }

        // Membuat koneksi API MikroTik
        $client = new Client([
            'host' => 'id-1.aqtnetwork.my.id',
            'user' => 'admin',
            'pass' => 'bakpao1922',
        ]);

        $query = new Query('/ppp/active/print');
        $response = $client->query($query)->read();

        // Array untuk menyimpan status koneksi setiap router
        $routerStatuses = [];

        // Loop untuk mengecek koneksi di active-connection
        foreach ($mikrotik as $router) {
            $vpnUsername = $router->vpn_username; // Ambil vpn_username dari database
            $status = 'Offline'; // Default status adalah Offline

            // Loop untuk mengecek apakah vpn_username ada di active-connection
            foreach ($response as $connection) {
                if (isset($connection['name']) && $connection['name'] === $vpnUsername) {
                    // Jika vpn_username ditemukan di active-connection, set status ke Online
                    $status = 'Online';
                    break;
                }
            }

            // Simpan status router berdasarkan vpn_username
            $routerStatuses[$router->id] = $status;
        }

        // Kembalikan view dengan data router dan status koneksi mereka
        return view('ROLE/MEMBER/ROUTER/router', compact('mikrotik', 'routerStatuses'));
    }


    public function store(Request $request)
    {
        try {
            // Validasi input
            $request->validate([
                'site' => 'required|string|max:255',
            ]);

            // Ambil user_id dari user yang login
            $userId = auth()->user()->id;
            $username = "BILLER_" . rand(1000, 9999) . auth()->user()->id;
            $vpnUsername = date('Y') . date('m') . rand(1000000, 9999999);
            $ur = date('Y') . date('m') . auth()->user()->id . rand(1000000, 9999999);

            // Koneksi ke MikroTik
            $client = new Client([
                'host' => 'id-1.aqtnetwork.my.id',
                'user' => 'admin',
                'pass' => 'bakpao1922',
                'port' => 8728,
            ]);

            // Mengambil semua PPP secrets untuk memeriksa username yang sudah ada
            $queryAllSecrets = new Query('/ppp/secret/print');
            $response = $client->query($queryAllSecrets)->read();

            // Cek apakah username sudah ada
            $existingUsernames = array_column($response, 'name');

            if (in_array($username, $existingUsernames)) {
                session()->flash('error', 'Username sudah ada, silakan gunakan username lain.');
                return redirect()->back();
            }

            // Oktet yang tetap
            $firstOctet = '180';
            $secondOctet = 16;

            // Ambil daftar thirdOctets yang sudah digunakan
            $usedThirdOctets = array_map(function ($secret) {
                return isset($secret['local-address']) ? explode('.', $secret['local-address'])[2] : null;
            }, $response);

            // Hapus nilai null dari daftar $usedThirdOctets
            $usedThirdOctets = array_filter($usedThirdOctets);

            // Tentukan thirdOctet yang baru
            $thirdOctetBase = 11;
            $thirdOctet = $thirdOctetBase;
            while (in_array($thirdOctet, $usedThirdOctets)) {
                $thirdOctet++;
                if ($thirdOctet > 254) {
                    throw new \Exception("Tidak ada third octet yang tersedia untuk IP addresses.");
                }
            }

            // Tentukan fourthOctet untuk lokal dan remote
            $existingCount = count($response);
            $fourthOctetLocal = 1;
            $fourthOctetRemote = 10 + ($existingCount % 255);

            // Generate IP addresses
            $localIp = "$firstOctet.$secondOctet.$thirdOctet.$fourthOctetLocal";
            $remoteIp = "$firstOctet.$secondOctet.$thirdOctet.$fourthOctetRemote";

            // Buat query untuk menambahkan PPP secret
            $query = new Query('/ppp/secret/add');
            $query->equal('name', $vpnUsername)
                ->equal('password', $vpnUsername)
                ->equal('comment', 'VPN BILLER ' . $vpnUsername)
                ->equal('profile', 'IP-Tunnel-VPN')
                ->equal('local-address', $localIp)
                ->equal('remote-address', $remoteIp);

            // Eksekusi query MikroTik
            $response = $client->query($query)->read();
            // Ambil port API, Winbox, dan Remote Web yang unik
            $portApi = $this->generateUniquePort('port_api', $client);
            $portWinbox = $this->generateUniquePort('port_winbox', $client);
            $portRemoteWeb = $this->generateUniquePort('port_remoteweb', $client);

            // Buat query untuk menambahkan NAT API
            $natQueryApi = new Query('/ip/firewall/nat/add');
            $natQueryApi->equal('chain', 'dstnat')
                ->equal('protocol', 'tcp')
                ->equal('dst-port', $portApi)
                ->equal('dst-address-list', 'ip-public')
                ->equal('action', 'dst-nat')
                ->equal('to-addresses', $remoteIp)
                ->equal('to-ports', $portApi)
                ->equal('comment', 'BILLER_' . $vpnUsername . '_API');

            $natResponseApi = $client->query($natQueryApi)->read();

            // Buat query untuk menambahkan NAT Winbox
            $natQueryWinbox = new Query('/ip/firewall/nat/add');
            $natQueryWinbox->equal('chain', 'dstnat')
                ->equal('protocol', 'tcp')
                ->equal('dst-port', $portWinbox)
                ->equal('dst-address-list', 'ip-public')
                ->equal('action', 'dst-nat')
                ->equal('to-addresses', $remoteIp)
                ->equal('to-ports', $portWinbox)
                ->equal('comment', 'BILLER_' . $vpnUsername . '_WBX');

            $natResponseWinbox = $client->query($natQueryWinbox)->read();

            // Buat query untuk menambahkan NAT Remote Web
            $natQueryRemoteWeb = new Query('/ip/firewall/nat/add');
            $natQueryRemoteWeb->equal('chain', 'dstnat')
                ->equal('protocol', 'tcp')
                ->equal('dst-port', $portRemoteWeb)
                ->equal('dst-address-list', 'ip-public')
                ->equal('action', 'dst-nat')
                ->equal('to-addresses', $remoteIp) // Pastikan `remoteIp` diisi dengan IP tujuan
                ->equal('to-ports', $portRemoteWeb)
                ->equal('comment', 'BILLER_' . $vpnUsername . '_RemoteWeb');

            $natResponseRemoteWeb = $client->query($natQueryRemoteWeb)->read();

            $mikrotik = Mikrotik::create([
                'user_id' => $userId,
                'router_id' => 'RO_' . Str::random(3) . Str::random(10),
                'site' => $request->site,
                'port_api' => $portApi,
                'port_winbox' => $portWinbox,
                'port_remoteweb' => $portRemoteWeb,
                'username' => $ur,
                'password' => $ur,
                'vpn_name' => 'BILLER_' . $vpnUsername,
                'vpn_username' => $vpnUsername,
                'vpn_password' => $vpnUsername,
                'local_ip' => $localIp,
                'remote_ip' => $remoteIp,  // Menyimpan remote IP
            ]);
            ActivityLogger::log('Menambahkan Router Baru', 'Nama Site: ' . $request->site);

            return redirect()->back()->with('success', 'Berhasil Menambahkan MikroTik');
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function generateUniquePort($portType, $client)
    {
        $startRange = 0;
        $endRange = 0;

        // Tentukan rentang port berdasarkan tipe
        switch ($portType) {
            case 'port_api':
                $startRange = 50000; // Rentang untuk API
                $endRange = 51999;
                break;
            case 'port_winbox':
                $startRange = 52000; // Rentang untuk Winbox
                $endRange = 53999;
                break;
            case 'port_remoteweb':
                $startRange = 54000; // Rentang untuk Remote Web
                $endRange = 55999;
                break;
            default:
                throw new \Exception("Tipe port '{$portType}' tidak valid. Gunakan 'port_api', 'port_winbox', atau 'port_remoteweb'.");
        }

        // Ambil semua port yang sudah digunakan dari database
        $usedPortsDb = Mikrotik::pluck($portType)->toArray();

        // Ambil semua port yang sudah digunakan dari MikroTik
        try {
            $mikrotikPortsQuery = new Query('/ip/firewall/nat/print');
            $mikrotikPortsResponse = $client->query($mikrotikPortsQuery)->read();
        } catch (\Exception $e) {
            throw new \Exception("Gagal mengambil data dari MikroTik: " . $e->getMessage());
        }

        $usedPortsMikrotik = [];
        foreach ($mikrotikPortsResponse as $rule) {
            if (isset($rule['dst-port']) && is_numeric($rule['dst-port'])) { // Pastikan dst-port adalah angka
                $usedPortsMikrotik[] = (int) $rule['dst-port'];
            }
        }

        // Gabungkan semua port yang sudah digunakan
        $usedPorts = array_merge($usedPortsDb, $usedPortsMikrotik);

        // Filter hanya nilai valid (integer)
        $usedPorts = array_filter($usedPorts, function ($value) {
            return is_int($value) || ctype_digit($value); // Pastikan nilai adalah integer atau string numerik
        });

        // Gunakan array_flip untuk pencarian cepat
        $usedPortsFlipped = array_flip($usedPorts);

        // Cari port yang tidak digunakan dalam rentang
        for ($port = $startRange; $port <= $endRange; $port++) {
            if (!isset($usedPortsFlipped[$port])) { // Pengecekan lebih cepat dengan array_flip
                return $port; // Kembalikan port yang tersedia
            }
        }

        throw new \Exception("Tidak ada port tersedia dalam rentang {$startRange}-{$endRange}.");
    }




    private function generateRandomPassword($length = 12)
    {
        return bin2hex(random_bytes($length / 2));
    }

    public function cekKoneksi($id)
    {
        // Ambil data router dari database berdasarkan ID
        $router = Mikrotik::with('user') // Jika ada relasi dengan user
            ->find($id);

        if (!$router) {
            return response()->json(['message' => 'Router tidak ditemukan!'], 404);
        }

        try {
            // Setup client menggunakan data dari relasi
            $client = new Client([
                'host' => "id-1.aqtnetwork.my.id:" . $router->port_api,
                'user' => $router->username,
                'pass' => $router->password,
            ]);

            // Cek koneksi dengan mengambil identitas router
            $response = $client->query('/system/identity/print')->read();

            return redirect()->back()->with('success', 'Koneksi berhasil ke Router: ' . ($response[0]['name'] ?? 'Tidak diketahui'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Koneksi gagal: ' . $e->getMessage());
        }
    }




    // PPPOE

    public function pppoe()
    {
        $userId = auth()->user()->id;

        // Ambil MikroTik milik user yang memiliki paket PPPoE
        $mikrotikList = Mikrotik::where('user_id', $userId)
            ->whereHas('paketPppoe')
            ->with('paketPppoe')
            ->get();

        // Koneksi ke MikroTik API
        $client = new Client([
            'host' => 'id-1.aqtnetwork.my.id',
            'user' => 'admin',
            'pass' => 'bakpao1922',
        ]);

        $query = new Query('/ppp/active/print');
        $response = $client->query($query)->read();

        $onlinePackages = [];

        // Cek router yang aktif di MikroTik
        foreach ($mikrotikList as $router) {
            foreach ($response as $connection) {
                if (isset($connection['name']) && $connection['name'] === $router->vpn_username) {
                    // Simpan semua paket PPPoE milik router yang online
                    foreach ($router->paketPppoe as $paket) {
                        $onlinePackages[] = $paket;
                    }
                    break;
                }
            }
        }


        return view('ROLE/MEMBER/IPLAN/PPPOE/index', compact('onlinePackages'));
    }

    public function addPppoe()
    {
        $user = auth()->user(); // Ambil user yang sedang login
        $userId = $user->id;

        // Ambil router MikroTik berdasarkan user_id atau parent_id jika teknisi
        if ($user->role == 'member') {
            $mikrotikList = Mikrotik::where('user_id', $userId)->get();
        } else {
            $mikrotikList = Mikrotik::where('user_id', $user->parent_id)->get();
        }
        //dd($mikrotikList);
        // Kembalikan view dengan daftar router saja
        return view('ROLE/MEMBER/IPLAN/PPPOE/tambahpaket', compact('mikrotikList'));
    }







    public function getMikrotikProfiles(Request $request)
    {
        $mikrotik = Mikrotik::find($request->username); // Ambil data MikroTik berdasarkan ID
    
        if (!$mikrotik) {
            return response()->json(['status' => 'error', 'message' => 'MikroTik tidak ditemukan'], 404);
        }
    
        // Koneksi ke MikroTik API
        try {
            $client = new Client([
                'host' => 'id-1.aqtnetwork.my.id:' . $mikrotik->port_api,
                'user' => $mikrotik->username,
                'pass' => $mikrotik->password,
            ]);
            // Ambil daftar profil PPPoE
            $query = new \RouterOS\Query('/ppp/profile/print');
            $profiles = $client->query($query)->read();
    
            return response()->json(['status' => 'success', 'profiles' => $profiles]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
    

public function tambahpaket(Request $request)
{
   //dd($request->username);
    
    $user = auth()->user();

    // Validasi input
    

    // Cari MikroTik berdasarkan username yang dipilih
    $dataMikrotik = $user->mikrotik()->find($request->username);

    // Generate kode unik untuk paket
    $kode_paket = 'PAK-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5));

    // Ambil nama site dari request
    $site = $request->mikrotikSite;

    // Cek apakah paket dengan nama yang sama sudah ada di MikroTik tersebut
    $existingPaket = PaketPppoe::where('mikrotik_id', $dataMikrotik->id)
        ->where('nama_paket', $request->namaPaket . " " . $site)
        ->exists();

    if ($existingPaket) {
        return redirect()->back()->with('error', 'Paket dengan nama ini sudah ada di MikroTik ini.');
    }

    // Simpan data paket PPPoE
    PaketPppoe::create([
        'mikrotik_id' => $dataMikrotik->id,
        'kode_paket' => $kode_paket,
        'site' => $site,
        'profile' => $request->profile,
        'harga_paket' => $request->hargaPaket,
        'nama_paket' => $request->namaPaket . " " . $site, // Tambahkan site ke nama paket
    ]);

    return redirect()->route('member.pppoe')->with('success', 'Paket PPPoE berhasil ditambahkan.');
}

}
