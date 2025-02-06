<?php

namespace App\Http\Controllers;

use RouterOS\Query;
use RouterOS\Client;
use App\Models\Mikrotik;
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


            // Ambil port API dan Winbox yang unik
            $portApi = $this->generateUniquePort('port_api');
            $portWinbox = $this->generateUniquePort('port_winbox');


            // Buat query untuk menambahkan NAT API
            $natQuery1 = new Query('/ip/firewall/nat/add');
            $natQuery1->equal('chain', 'dstnat')
                ->equal('protocol', 'tcp')
                ->equal('dst-port', $portApi)
                ->equal('dst-address-list', 'ip-public')
                ->equal('action', 'dst-nat')
                ->equal('to-addresses', $remoteIp)
                ->equal('to-ports', $portApi)
                ->equal('comment', 'BILLER_' . $vpnUsername . '_API');

            $natResponse1 = $client->query($natQuery1)->read();

            // Buat query untuk menambahkan NAT Winbox
            $natQuery2 = new Query('/ip/firewall/nat/add');
            $natQuery2->equal('chain', 'dstnat')
                ->equal('protocol', 'tcp')
                ->equal('dst-port', $portWinbox)
                ->equal('dst-address-list', 'ip-public')
                ->equal('action', 'dst-nat')
                ->equal('to-addresses', $remoteIp)
                ->equal('to-ports', $portWinbox)
                ->equal('comment', 'BILLER_' . $vpnUsername . '_WBX');

            $natResponse2 = $client->query($natQuery2)->read();

            $mikrotik = Mikrotik::create([
                'unique_id' => $uniqueId,
                'site' => $request->site,
                'port_api' => $portApi,
                'port_winbox' => $portWinbox,
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

    private function generateUniquePort($column)
    {
        do {
            $port = rand(2000, 65535);
            $exists = DB::table('mikrotik')->where($column, $port)->exists();
        } while ($exists);

        return $port;
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

    // Mengirim hanya router yang online ke view
    return view('ROLE/MEMBER/IPLAN/PPPOE/index', compact('onlineRouters'));
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

}
