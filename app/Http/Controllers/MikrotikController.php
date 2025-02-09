<?php

namespace App\Http\Controllers;

use RouterOS\Query;
use RouterOS\Client;
use App\Models\Mikrotik;
use App\Models\PaketPppoe;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MikrotikController extends Controller
{

    public function router()
    {
        $uniqueId = auth()->user()->unique_id;
        $mikrotik = Mikrotik::where('unique_id', $uniqueId)->get();

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

        // Debug untuk melihat hasil status koneksi
        //dd($routerStatuses);

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

            // Ambil unique_id dari user yang login
            $uniqueId = auth()->user()->unique_id;
            $username = "BILLER_" . rand(1000, 9999) . auth()->user()->unique_id;
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
                ->equal('comment', 'VPN BILLER ' . $uniqueId)
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

                'unique_id' => $uniqueId,
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


            return response()->json(['message' => 'MikroTik configuration saved and applied successfully!'], 200);
        } catch (\Exception $e) {
            // Tangani error
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
        // Ambil data router dari database berdasarkan $routerId
        $router = Mikrotik::find($id);

        //dd($router->username);
        if (!$router) {
            return response()->json(['message' => 'Router tidak ditemukan!'], 404);
        }

        try {
            // Setup client
            $client = new Client([
                'host' => 'id-1.aqtnetwork.my.id:' . $router->port_api,
                'user' => $router->username,
                'pass' => $router->password,
                //'port' => (int)$router->port_api,
            ]);

            // Try to get some basic information to check the connection
            $response = $client->query('/system/identity/print')->read();

            // If we receive a response, it means the connection is working
            return redirect()->back()->with('success', 'Koneksi berhasil ke Router: ' . $response[0]['name']);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Koneksi gagal: ' . $e->getMessage());
        }
    }



    // PPPOE

    public function pppoe()
    {
        $uniqueId = auth()->user()->unique_id;
        $mikrotik = Mikrotik::where('unique_id', $uniqueId)->get();

        // Membuat koneksi API MikroTik
        $client = new Client([
            'host' => 'id-1.aqtnetwork.my.id',
            'user' => 'admin',
            'pass' => 'bakpao1922',
        ]);

        $query = new Query('/ppp/active/print');
        $response = $client->query($query)->read();

        // Array untuk menyimpan router yang online
        $onlineRouters = [];

        // Loop untuk mengecek koneksi di active-connection
        foreach ($mikrotik as $router) {
            $vpnUsername = $router->vpn_username; // Ambil vpn_username dari database

            // Loop untuk mengecek apakah vpn_username ada di active-connection
            foreach ($response as $connection) {
                if (isset($connection['name']) && $connection['name'] === $vpnUsername) {
                    // Jika vpn_username ditemukan di active-connection, router dianggap online
                    $onlineRouters[] = $router; // Menyimpan router yang statusnya Online
                    break;
                }
            }
        }

        $paket = PaketPppoe::where('unique_id', auth()->user()->unique_id)->get();
        // Mengirim hanya router yang online ke view
        return view('ROLE/MEMBER/IPLAN/PPPOE/index', compact('onlineRouters', 'paket'));
    }

    public function getMikrotikProfiles(Request $request)
    {
        $username = $request->input('username');

        // Cari data MikroTik berdasarkan unique_id
        $mikrotik = MikroTik::where('username', $username)->first();

        if (!$mikrotik) {
            return response()->json(['status' => 'error', 'message' => 'MikroTik tidak ditemukan']);
        }

        $portApi = $mikrotik->port_api;
        $Username = $mikrotik->username;
        $Password = $mikrotik->password;

        try {
            $client = new Client([
                'host' => 'id-1.aqtnetwork.my.id:' . $portApi,
                'user' => $Username,
                'pass' => $Password,
            ]);

            // Query untuk mendapatkan profil PPPoE
            $query = new Query('/ppp/profile/print');
            $profiles = $client->query($query)->read();

            if (empty($profiles)) {
                return response()->json(['status' => 'error', 'message' => 'Tidak ada profil PPPoE ditemukan']);
            }

            // Format ulang respons untuk memastikan hanya data yang dibutuhkan
            $formattedProfiles = array_map(function ($profile) {
                return [
                    'name' => $profile['name'] ?? 'Unknown',
                ];
            }, $profiles);

            return response()->json(['status' => 'success', 'profiles' => $formattedProfiles]);
        } catch (\Exception $e) {
            // Menangkap kesalahan koneksi dengan MikroTik dan mengirim pesan yang jelas
            return response()->json(['status' => 'error', 'message' => 'Gagal terhubung ke MikroTik. Pastikan kredensial dan koneksi API sudah benar.']);
        }
    }

    public function tambahpaket(Request $request)
    {
        // Mendapatkan unique_id dari pengguna yang sedang login
        $uniqueId = auth()->user()->unique_id;

        // Mendapatkan input dari form
        $kode_paket = Str::random(10); // Anda bisa menyesuaikan dengan input yang ada
        $username = $request->input('username');

        $dataMikrotik = Mikrotik::where('username', $username)->first();
        $site = $dataMikrotik->site;

        $profile = $request->input('profile');
        $harga_paket = $request->input('hargaPaket'); // Mengambil data hargaPaket dari form
        $nama_paket = $request->input('namaPaket'); // Mengambil data namaPaket dari form

        // Validasi data jika diperlukan
        $validated = $request->validate([
            'username' => 'required|string',
            'profile' => 'required|string',
            'namaPaket' => 'required|string',
            'hargaPaket' => 'required|numeric|max:1000000',
        ]);


        // Menyimpan data ke dalam database
        DB::table('paketpppoe')->insert([
            'unique_id' => $uniqueId,
            'router_id' => $dataMikrotik->router_id,
            'kode_paket' => $kode_paket,
            'site' => $site,
            'username' => $username,
            'profile' => $profile,
            'harga_paket' => $harga_paket,
            'nama_paket' => $nama_paket,
            'created_at' => now(),
            'updated_at' => now()

        ]);

        // Redirect atau memberikan respon sesuai kebutuhan
        return redirect()->route('member.pppoe')->with('success', 'Paket berhasil ditambahkan');
    }
}
