<?php

namespace App\Http\Controllers;

use RouterOS\Query;
use RouterOS\Client;
use App\Models\Invoice;
use App\Models\Mikrotik;
use App\Models\TiketPsb;
use App\Models\Pelanggan;
use App\Models\PaketPppoe;
use App\Helpers\ActivityLogger;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Foundation\Mix;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class PelangganController extends Controller
{
    public function index()
    {
        $user = auth()->user(); // Ambil user yang sedang login
        $userId = $user->id;
    
        // Cek apakah user adalah member atau bukan
        if ($user->role == 'member') {
            // Jika member, ambil router berdasarkan user_id
            $mikrotikRouters = Mikrotik::where('user_id', $userId)->get();
        } else {
            // Jika bukan member (misal teknisi), ambil router berdasarkan parent_id
            $mikrotikRouters = Mikrotik::where('user_id', $user->parent_id)->get();
        }
    
        // Ambil semua pelanggan terkait router yang dimiliki user
        $plg = Pelanggan::whereIn('mikrotik_id', $mikrotikRouters->pluck('id'))
            ->with('paket')->get();
    
        // Inisialisasi array untuk menyimpan status pelanggan
        $onlineStatus = [];
    
        // Buat mapping router berdasarkan ID untuk akses cepat
        $routerMapping = $mikrotikRouters->keyBy('id');
    
        foreach ($mikrotikRouters as $mikrotik) {
            try {
                // Buat koneksi ke MikroTik API
                $client = new Client([
                    'host' => 'id-1.aqtnetwork.my.id:' . $mikrotik->port_api,
                    'user' => $mikrotik->username,
                    'pass' => $mikrotik->password,
                ]);
    
                // Ambil daftar active connections
                $query = new Query('/ppp/active/print');
                $activeConnections = collect($client->query($query)->read());
    
                // Proses setiap pelanggan yang ada di router ini
                foreach ($plg->where('mikrotik_id', $mikrotik->id) as $pelanggan) {
                    // Cari koneksi aktif berdasarkan akun PPPoE
                    $activeConnection = $activeConnections->firstWhere('name', $pelanggan->akun_pppoe);
    
                    // Cek apakah pelanggan sedang online
                    $isOnline = !is_null($activeConnection);
    
                    // Ambil IP pelanggan dari koneksi aktif (jika ada)
                    $ipAddress = $activeConnection['address'] ?? null;
    
                    // Cek apakah IP pelanggan dimulai dengan '172'
                    $isIsolir = $ipAddress && str_starts_with($ipAddress, '172');
    
                    // Tentukan status pelanggan
                    $onlineStatus[$pelanggan->akun_pppoe] = $isIsolir ? 'Isolir' : ($isOnline ? 'Online' : 'Offline');
                }
            } catch (\Exception $e) {
                // Jika gagal koneksi, tandai semua pelanggan pada router ini offline
                foreach ($plg->where('mikrotik_id', $mikrotik->id) as $pelanggan) {
                    $onlineStatus[$pelanggan->akun_pppoe] = 'Offline';
                }
            }
        }
    
        // Gabungkan data pelanggan dengan status online
        foreach ($plg as $pelanggan) {
            $pelanggan->status = $onlineStatus[$pelanggan->akun_pppoe] ?? 'Offline';
        }
    
        // Return ke view dengan data pelanggan
        return view('ROLE.MEMBER.PELANGGAN.index', compact('plg', 'mikrotikRouters'));
    }
    


    public function formulir()
    {
        $user = auth()->user(); // Ambil user yang sedang login
        $userId = $user->id;
    
        // Cek apakah user adalah member atau bukan
        if ($user->role == 'member') {
            // Jika member, ambil router berdasarkan user_id
            $mikrotik = Mikrotik::where('user_id', $userId)->get();
        } else {
            // Jika bukan member (misal teknisi), ambil router berdasarkan parent_id
            $mikrotik = Mikrotik::where('user_id', $user->parent_id)->get();
        }
    
        // Membuat koneksi API MikroTik
        try {
            $client = new Client([
                'host' => 'id-1.aqtnetwork.my.id',
                'user' => 'admin',
                'pass' => 'bakpao1922',
            ]);
    
            $query = new Query('/ppp/active/print');
            $response = $client->query($query)->read();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal terhubung ke MikroTik API');
        }
    
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
    
        // Mengubah $onlineRouters menjadi Collection
        $onlineRoutersCollection = collect($onlineRouters);
    
        // Jika ada router yang online, ambil data dari model PaketPPPoE
        if ($onlineRoutersCollection->isNotEmpty()) {
            // Mengambil data paket PPPoE yang sesuai dari model PaketPPPoE
            $paketPPPoEs = PaketPPPoE::whereIn('mikrotik_id', $onlineRoutersCollection->pluck('id'))->get();
    
            // Mengirim data paket PPPoE dan router yang online ke view
            return view('ROLE.MEMBER.PELANGGAN.formulir', compact('paketPPPoEs', 'onlineRouters'));
        } else {
            return redirect()->back()->with('error', 'Tidak Ada Mikrotik Yang Aktif');
        }
    }
    

    function generateKodePSB()
    {
        return now()->format('ymd') . mt_rand(100, 999);
    }
    public function addPelanggan(Request $request)
{
    //\Log::info($request->all());

    $user = auth()->user(); // Ambil user yang login
    $kodePaket = $request->input('kodePaket');
   
    // Ambil paket berdasarkan kode_paket
    $paket = PaketPppoe::where('kode_paket', $kodePaket)->firstOrFail();

    // Ambil router dari user berdasarkan username di paket
    $mikrotik = $user->mikrotik()->where('id', $paket->mikrotik_id)->firstOrFail();

    // Data pelanggan dari request
    $akunPppoe = $request->input('akunPppoe');
    $passPppoe = $request->input('passwordPppoe');
    $ssidWifi = $request->input('ssid');
    $passWifi = $request->input('passwifi');
    $tanggalinginpasang = $request->input('tip');
   // dd($mikrotik);
    $uniqueId = $user->unique_id;
    try {
        // Koneksi ke MikroTik API
        $client = new Client([
            'host' => 'id-1.aqtnetwork.my.id:' . $mikrotik->port_api,
            'user' => $mikrotik->username,
            'pass' => $mikrotik->password,
        ]);

        // Tambahkan akun PPPoE di MikroTik
        $query = new Query('/ppp/secret/add');
        $query->equal('name', $akunPppoe);
        $query->equal('password', $passPppoe);
        $query->equal('service', 'pppoe');
        $query->equal('profile', $paket->profile);

        $client->query($query)->read();
    } catch (\Exception $e) {
        return redirect()->back()->withErrors(['mikrotik_pppoe' => 'Gagal menambahkan akun PPPoE: ' . $e->getMessage()]);
    }

    // Status pembayaran
    $tanggalDaftar = now();
    $tanggalPembayaranSelanjutnya = $tanggalDaftar->copy()->addMonth();
    $statusPembayaran = $request->input('metode_pembayaran') === 'Prabayar' ? 'Sudah Dibayar' : 'Belum Dibayar';
    $uuniq = rand(100, 9999999);
    $kodePsb = $this->generateKodePSB(); // Kode Tiket PSB

    // **Buat data pelanggan menggunakan relasi**
    $pelanggan = $mikrotik->pelanggan()->create([
        'pelanggan_id' => $user->unique_member . $uuniq,
        'router_id' => $mikrotik->router_id,

        'no_tiket' => $kodePsb,
        'nama_ssid' => $ssidWifi,
        'password_ssid' => $passWifi,
        'router_username' => $mikrotik->username,
        'kode_paket' => $kodePaket,
        'profile_paket' => $paket->profile,
        'nama_pelanggan' => $request->input('namaPelanggan'),
        'akun_pppoe' => $akunPppoe,
        'password_pppoe' => $passPppoe,
        'alamat' => $request->input('alamat'),
        'nomor_telepon' => $request->input('telepon'),
        'tanggal_daftar' => $tanggalDaftar,
        'tanggal_ingin_pasang' => $tanggalinginpasang,
        'pembayaran_selanjutnya' => $tanggalPembayaranSelanjutnya,
        'metode_pembayaran' => $request->input('metode_pembayaran'),
        'status_pembayaran' => $statusPembayaran,
    ]);

    // **Buat tiket PSB terkait pelanggan**
    $tiket = $pelanggan->tiket()->create([
        'no_tiket' => $kodePsb,
        'status_tiket' => 'Belum Dikonfirmasi',
        'serialnumber' => $request->input('serialnumber'),
        
        // Relasi pelanggan
        'pelanggan_id' => $pelanggan->id,
        'parent_id' => $user->id,
        // Relasi router
        'mikrotik_id' => $mikrotik->id,
        'router_username' => $mikrotik->username,
    
        // Relasi paket
        'paket_id' => $paket->id, // Menggunakan ID paket sesuai schema
    
        // Data PPPoE
        'akun_pppoe' => $akunPppoe,
        'password_pppoe' => $passPppoe,
    
        // Informasi pelanggan
        'alamat' => $request->input('alamat'),
        'nomor_telepon' => $request->input('telepon'),
    
        // Tanggal & pembayaran
        'tanggal_daftar' => $tanggalDaftar,
        'pembayaran_selanjutnya' => $tanggalPembayaranSelanjutnya,
        'pembayaran_yang_akan_datang' => null,
        'tanggal_ingin_pasang' => $tanggalinginpasang,
        'tanggal_terpasang' => null, // Sesuai schema
    
        // Informasi tambahan
        'nama_ssid' => $ssidWifi,
        'password_ssid' => $passWifi,
        'mac_address' => $request->input('macadress'),
        'odp' => $request->input('odp'),
        'olt' => $request->input('olt'),
    ]);
    

    // Log aktivitas
    ActivityLogger::log('Menambahkan Pelanggan Baru', 'Nama Pelanggan : '.$pelanggan->nama_pelanggan. " Dengan Nomor Tiket PSB : " .$kodePsb);

    // Redirect dengan sukses
    return redirect()->back()->with('success', 'Pelanggan berhasil ditambahkan.');
}


public function showPelanggan($id)
{
    $pelanggan = Pelanggan::with('paket')->find($id);

    if (!$pelanggan) {
        return response()->json(['error' => 'Pelanggan tidak ditemukan'], 404);
    }

    $routerId = $pelanggan->router_id;
    $mikrotik = Mikrotik::where('router_id', $routerId)->first();
    $akunPelanggan = $pelanggan->akun_pppoe;

    if (!$mikrotik) {
        return redirect()->back()->with('error', 'Router tidak ditemukan');
    }

    try {
        // Membuat koneksi ke MikroTik API
        $client = new Client([
            'host' => 'id-1.aqtnetwork.my.id:' . $mikrotik->port_api, // Pastikan port benar
            'user' => $mikrotik->username, // Username MikroTik
            'pass' => $mikrotik->password, // Password MikroTik
        ]);

        // Query untuk mengambil data koneksi PPPoE
        $query = new Query('/interface/pppoe-server/print');
        $activeConnections = $client->query($query)->read();

        if (!$activeConnections || count($activeConnections) == 0) {
            return redirect()->back()->with('error', 'Tidak ada koneksi PPPoE aktif di server');
        }

        // Filter data untuk mencari koneksi dengan nama yang sesuai dengan akun PPPoE
        $filteredConnections = array_filter($activeConnections, function ($connection) use ($akunPelanggan) {
            return isset($connection['name']) && $connection['name'] === "<pppoe-" . $akunPelanggan.">";
        });

        // Jika tidak ditemukan, tampilkan pesan error
        if (empty($filteredConnections)) {
            return redirect()->back()->with('error', 'Akun Ini Tidak Terhubung Dengan Server !');
        }

        // Ambil elemen pertama dari hasil filter
        $connectionData = array_shift($filteredConnections);

        // Pastikan key 'uptime' ada sebelum mengaksesnya
        if (!isset($connectionData['uptime'])) {
            return redirect()->back()->with('error', 'Data uptime tidak ditemukan');
        }

        $formattedUptime = $this->formatUptime($connectionData['uptime']); // Format uptime

        // Jika diminta untuk menampilkan halaman, kirimkan data pelanggan
        return view('ROLE.MEMBER.PELANGGAN.data', [
            'pelanggan' => $pelanggan,
            'mikrotik' => $mikrotik,
            'formattedUptime' => $formattedUptime,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Terjadi kesalahan saat menghubungkan ke MikroTik API: ' . $e->getMessage()
        ], 500);
    }
}


    public function getBandwidth($id)
    {
        $pelanggan = Pelanggan::find($id);
        $akunPelanggan = $pelanggan->akun_pppoe;

        try {
            $mikrotik = Mikrotik::where('router_id', $pelanggan->router_id)->first();

            $client = new Client([
                'host' => 'id-1.aqtnetwork.my.id:' . $mikrotik->port_api,
                'user' => $mikrotik->username,
                'pass' => $mikrotik->password,
            ]);

            // Query untuk mengambil data interface
            $query = new Query('/interface/print');
            $data = $client->query($query)->read();

            // Filter data untuk mencari koneksi sesuai dengan akun PPPoE
            $filteredConnections = array_filter($data, function ($connection) use ($akunPelanggan) {
                return isset($connection['name']) && $connection['name'] === "<pppoe-" . $akunPelanggan . ">";
            });

            // Menghitung RX dan TX bytes
            $totalRxBytes = 0;
            $totalTxBytes = 0;

            foreach ($filteredConnections as $interface) {
                if (isset($interface['rx-byte']) && isset($interface['tx-byte'])) {
                    $totalRxBytes += $interface['rx-byte'];
                    $totalTxBytes += $interface['tx-byte'];
                }
            }

            $totRx = $this->formatBytes($totalRxBytes); // Konversi RX bytes
            $totTx = $this->formatBytes($totalTxBytes); // Konversi TX bytes

            return response()->json([
                'totRx' => $totRx,
                'totTx' => $totTx,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan saat menghubungkan ke MikroTik API: ' . $e->getMessage()], 500);
        }
    }

    private function formatBytes($bytes)
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' Bytes';
        }
    }

    private function formatUptime($uptime)
    {
        $days = $hours = $minutes = 0;

        // Mencari jumlah hari, jam, dan menit dari string uptime
        if (preg_match('/(\d+)d/', $uptime, $matches)) {
            $days = (int)$matches[1];
        }
        if (preg_match('/(\d+)h/', $uptime, $matches)) {
            $hours = (int)$matches[1];
        }
        if (preg_match('/(\d+)m/', $uptime, $matches)) {
            $minutes = (int)$matches[1];
        }

        $formatted = [];
        if ($days > 0) {
            $formatted[] = "{$days} Hari";
        }
        if ($hours > 0) {
            $formatted[] = "{$hours} Jam";
        }
        if ($minutes > 0) {
            $formatted[] = "{$minutes} Menit";
        }

        return implode(' ', $formatted);  // Gabungkan dengan spasi
    }

    public function getTrafficData(Request $request)
    {
        $routerId = $request->input('router_id');
        $akunPppoe = $request->input('akun_pppoe');

        if (!$routerId || !$akunPppoe) {
            return response()->json(['error' => 'Parameter router_id dan akun_pppoe wajib diisi'], 400);
        }

        $mikrotik = Mikrotik::where('router_id', $routerId)->first();

        if (!$mikrotik) {
            return response()->json(['error' => 'Router tidak ditemukan'], 404);
        }

        try {
            $client = new Client([
                'host' => 'id-1.aqtnetwork.my.id:' . $mikrotik->port_api,
                'user' => $mikrotik->username,
                'pass' => $mikrotik->password,
            ]);

            $response = $client->query(
                (new Query('/interface/monitor-traffic'))
                    ->equal('interface', "<pppoe-" . $akunPppoe . ">")
                    ->equal('once')
            )->read();

            if (empty($response) || !isset($response[0]['tx-bits-per-second'], $response[0]['rx-bits-per-second'])) {
                return response()->json(['error' => 'Tidak dapat mengambil data traffic'], 500);
            }

            return response()->json([
                'tx' => (int) ($response[0]['tx-bits-per-second'] ?? 0),
                'rx' => (int) ($response[0]['rx-bits-per-second'] ?? 0),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function cekPing($akunPppoe)
    {
        try {
            $pelanggan = Pelanggan::where('akun_pppoe', $akunPppoe)->first();
            $mikrotik = Mikrotik::where('router_id', $pelanggan->router_id)->first();

            if (!$pelanggan) {
                return response()->json(['error' => 'Pelanggan tidak ditemukan'], 404);
            }

            $akunPelanggan = $pelanggan->akun_pppoe;
            $client = new Client([
                'host' => 'id-1.aqtnetwork.my.id:' . $mikrotik->port_api, // Pastikan port benar
                'user' => $mikrotik->username, // Username MikroTik
                'pass' => $mikrotik->password, // Password MikroTik
            ]);

            // Query untuk mengambil data koneksi PPPoE
            $query = new Query('/ppp/active/print');
            $activeConnections = $client->query($query)->read();

            // Periksa apakah query mengembalikan array atau objek yang valid
            if (!is_array($activeConnections)) {
                return response()->json(['error' => 'Gagal mendapatkan data koneksi PPPoE'], 500);
            }

            // Filter data untuk mencari koneksi dengan nama yang sesuai dengan akun PPPoE
            $filteredConnections = array_filter($activeConnections, function ($connection) use ($akunPelanggan) {
                return isset($connection['name']) && $connection['name'] === $akunPelanggan;
            });

            // Periksa apakah koneksi ditemukan
            if (empty($filteredConnections)) {
                return response()->json(['error' => 'Koneksi PPPoE tidak ditemukan untuk akun ' . $akunPelanggan], 404);
            }

            // Ambil IP address dari koneksi yang ditemukan
            $ipAddress = null;
            foreach ($filteredConnections as $connection) {
                if (isset($connection['address'])) {
                    $ipAddress = $connection['address'];
                    // $ipAddress = '192.168.9.4';

                    break;
                }
            }

            $ip = $ipAddress;

            if ($ipAddress) {
                // Melakukan ping ke IP address yang ditemukan
                $query = new Query('/ping');
                $query->equal('address', $ipAddress);
                $query->equal('count', 30);  // Mengatur jumlah ping yang akan dilakukan

                $pingResults = $client->query($query)->read();

                $formattedPingResults = [];
                foreach ($pingResults as $result) {
                    // Cek apakah ada 'time' atau status 'timeout'
                    if (isset($result['time'])) {
                        // Jika statusnya ada, tambahkan "time"
                        $formattedPingResults[] = "{$result['time']}";
                    } elseif (isset($result['status']) && $result['status'] == 'timeout') {
                        // Jika status adalah timeout, tambahkan "Timeout"
                        $formattedPingResults[] = "Timeout";
                    }
                }

                if (!empty($formattedPingResults)) {
                    // Mengembalikan hasil ping beserta IP address
                    return response()->json(['success' => true, 'pingResults' => $formattedPingResults, 'ip' => $ipAddress]);
                } else {
                    return response()->json(['success' => false, 'error' => 'Gagal melakukan ping ke IP address']);
                }
            } else {
                return response()->json(['success' => false, 'error' => 'IP address tidak ditemukan']);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }


    public function cekModem(Request $request)
    {
        \Log::info('Request: ' . json_encode($request->all()));
        // Ambil input dari form
        $pppoeAkun = $request->input('pppoe_akun');
        $remotePort = $request->input('remote_port');
        $roId = $request->input('router_id');

        // Cari data MikroTik berdasarkan router_id
        $mikrotik = Mikrotik::where('router_id', $roId)->first();

    
        // Jika router tidak ditemukan, kembalikan error
        if (!$mikrotik) {
            return redirect()->back()->with('error', 'Router tidak ditemukan.');
        }
    
        // Cari data pelanggan berdasarkan mikrotik_id dan akun PPPoE
        $dataPppoe = Pelanggan::where('mikrotik_id', $mikrotik->id)
            ->where('akun_pppoe', $pppoeAkun)
            ->first();
    
        // Jika data PPPoE tidak ditemukan, kembalikan error
        if (!$dataPppoe) {
            return redirect()->back()->with('error', 'Data pelanggan tidak ditemukan.');
        }
    
        try {
            // Membuat koneksi ke MikroTik API
            $client = new Client([
                'host' => 'id-1.aqtnetwork.my.id:' . $mikrotik->port_api,
                'user' => $mikrotik->username,
                'pass' => $mikrotik->password,
            ]);
    
            // Ambil IP address dari Active Connection berdasarkan akun PPPoE
            $query = (new Query('/ppp/active/print'))
                ->where('name', $dataPppoe->akun_pppoe);
    
            $activeConnections = $client->query($query)->read();
    
            if (empty($activeConnections)) {
                return redirect()->back()->with('error', 'Koneksi aktif tidak ditemukan untuk pelanggan.');
            }
    
            // Ambil IP address dari koneksi aktif
            $ipAddress = $activeConnections[0]['address'] ?? null;
    
            if (!$ipAddress) {
                return redirect()->back()->with('error', 'IP address tidak ditemukan untuk pelanggan.');
            }
    
            // Cari dan update rule NAT dengan komentar "Biller_remod"
            $natQuery = (new Query('/ip/firewall/nat/print'))
                ->where('comment', 'Biller_Remod');
    
            $natRules = $client->query($natQuery)->read();
    
            if (empty($natRules)) {
                return redirect()->back()->with('error', 'Rule NAT dengan komentar "Biller_remod" tidak ditemukan.');
            }
    
            // Update rule NAT dengan IP address yang diambil
            $natRuleId = $natRules[0]['.id']; // Pastikan Anda mengambil .id dengan benar
    
            // Update rule NAT dengan parameter yang valid
            $updateQuery = (new Query('/ip/firewall/nat/set'))
                ->equal('.id', $natRuleId)
                ->equal('to-addresses', $ipAddress)
                ->equal('to-ports', $remotePort);
    
            // Kirim query untuk memperbarui NAT
            $response = $client->query($updateQuery)->read();
    
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghubungkan ke MikroTik: ' . $e->getMessage());
        }
    
        // Redirect kembali dengan pesan sukses
        return redirect()->back()->with('success_script', "
            Swal.fire({
                title: 'Berhasil',
                text: 'Modem berhasil di-remote.',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.open('http://id-1.aqtnetwork.my.id:" . $mikrotik->port_remoteweb . "', '_blank');
                }
            });
        ");
    }
    




    public function restartUser(Request $request)
    {
        $ids = $request->input('ids', []);

        // Validasi apakah ada ID yang dipilih
        if (empty($ids)) {
            return response()->json(['message' => 'Tidak ada pengguna yang dipilih.'], 400);
        }

        // Ambil data pelanggan berdasarkan ID
        $pelanggan = Pelanggan::whereIn('id', $ids)->get();

        if ($pelanggan->isEmpty()) {
            return response()->json(['message' => 'Pengguna tidak ditemukan.'], 404);
        }

        // Grouping pelanggan berdasarkan router_id
        $groupedDetails = [];
        foreach ($pelanggan as $plg) {
            $routerId = $plg->router_id;

            $groupedDetails[$routerId][] = [
                'akun_pppoe' => $plg->akun_pppoe,
                'id_pelanggan' => $plg->id,
            ];
        }

        // Ambil data MikroTik berdasarkan router_id
        $routerIds = array_keys($groupedDetails);
        $mikrotikData = Mikrotik::whereIn('router_id', $routerIds)->get()->keyBy('router_id');

        // Menyiapkan detail MikroTik untuk setiap router_id
        $mikrotikDetails = [];
        foreach ($mikrotikData as $mikrotik) {
            $mikrotikDetails[$mikrotik->router_id] = [
                'id' => $mikrotik->id,
                'port_api' => $mikrotik->port_api,
                'username' => $mikrotik->username,
                'password' => $mikrotik->password,
            ];
        }

        $apiResponses = [];
        foreach ($mikrotikDetails as $routerId => $router) {
            try {
                // Koneksi ke MikroTik menggunakan RouterOS Client
                $client = new \RouterOS\Client([
                    'host' => 'id-1.aqtnetwork.my.id',
                    'user' => $router['username'],
                    'pass' => $router['password'],
                    'port' => (int) $router['port_api'],
                ]);

                // Proses setiap pelanggan pada router ini
                if (isset($groupedDetails[$routerId]) && is_array($groupedDetails[$routerId])) {
                    foreach ($groupedDetails[$routerId] as $detail) {
                        $pppoeAkun = trim($detail['akun_pppoe']); // Pastikan akun PPPoE valid

                        if ($pppoeAkun) {
                            try {
                                $client = new \RouterOS\Client([
                                    'host' => 'id-1.aqtnetwork.my.id',
                                    'user' => $router['username'],
                                    'pass' => $router['password'],
                                    'port' => (int) $router['port_api'],
                                ]);
                                // Query untuk mendapatkan daftar pengguna PPPoE aktif
                                $query = new Query('/ppp/active/print');
                                $pppActiveConnections = $client->query($query)->read();

                                if (!empty($pppActiveConnections)) {
                                    $found = false;

                                    // Cari akun PPPoE berdasarkan nama
                                    foreach ($pppActiveConnections as $connection) {
                                        if ($connection['name'] === $pppoeAkun) {
                                            $pppId = $connection['.id'];

                                            // Hapus PPPoE aktif berdasarkan ID
                                            $removeQuery = new Query('/ppp/active/remove');
                                            $removeQuery->equal('.id', $pppId);
                                            $client->query($removeQuery)->read();

                                            $apiResponses[$routerId][] = [
                                                'status' => 'success',
                                                'message' => "Berhasil menghapus pengguna PPPoE: $pppoeAkun",
                                            ];

                                            $found = true;
                                            break;
                                        }
                                    }

                                    if (!$found) {
                                        $apiResponses[$routerId][] = [
                                            'status' => 'error',
                                            'message' => "Akun PPPoE tidak ditemukan: $pppoeAkun",
                                        ];
                                    }
                                } else {
                                    $apiResponses[$routerId][] = [
                                        'status' => 'error',
                                        'message' => "Tidak ada pengguna PPPoE aktif pada router: $routerId",
                                    ];
                                }
                            } catch (\Exception $e) {
                                $apiResponses[$routerId][] = [
                                    'status' => 'error',
                                    'message' => "Error saat memproses akun PPPoE: $pppoeAkun - " . $e->getMessage(),
                                ];
                            }
                        } else {
                            $apiResponses[$routerId][] = [
                                'status' => 'error',
                                'message' => 'Data akun_pppoe tidak valid untuk router ID: ' . $routerId,
                            ];
                        }
                    }
                } else {
                    \Log::warning("Grouped details tidak ditemukan untuk router ID: $routerId");
                }
            } catch (\Exception $e) {
                \Log::error('Error saat koneksi ke router: ' . $e->getMessage());
                $apiResponses[$routerId][] = [
                    'status' => 'error',
                    'message' => "Error saat koneksi ke router ID: $routerId - " . $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'sessions' => 'success',
            'message' => 'Proses restart PPPoE selesai.',
            'api_responses' => $apiResponses,
        ]);
    }

    public function kirimTagihan(Request $request)
    {
        $ids = $request->input('ids', []);
        // Logika untuk mengirim tagihan
        return response()->json(['message' => 'Tagihan berhasil dikirim.']);
    }

    public function isolir(Request $request)
    {
        $ids = $request->input('ids', []);

        // Validasi apakah ada ID yang dipilih
        if (empty($ids)) {
            return response()->json(['message' => 'Tidak ada pengguna yang dipilih.'], 400);
        }

        // Ambil data pelanggan berdasarkan ID
        $pelanggan = Pelanggan::whereIn('id', $ids)->get();

        if ($pelanggan->isEmpty()) {
            return response()->json(['message' => 'Pengguna tidak ditemukan.'], 404);
        }

        // Grouping pelanggan berdasarkan router_id
        $groupedDetails = [];
        foreach ($pelanggan as $plg) {
            $routerId = $plg->router_id;

            $groupedDetails[$routerId][] = [
                'akun_pppoe' => $plg->akun_pppoe,
                'id_pelanggan' => $plg->id,
            ];
        }

        // Ambil data MikroTik berdasarkan router_id
        $routerIds = array_keys($groupedDetails);
        $mikrotikData = Mikrotik::whereIn('router_id', $routerIds)->get()->keyBy('router_id');

        // Menyiapkan detail MikroTik untuk setiap router_id
        $mikrotikDetails = [];
        foreach ($mikrotikData as $mikrotik) {
            $mikrotikDetails[$mikrotik->router_id] = [
                'id' => $mikrotik->id,
                'port_api' => $mikrotik->port_api,
                'username' => $mikrotik->username,
                'password' => $mikrotik->password,
            ];
        }

        $apiResponses = [];
        foreach ($mikrotikDetails as $routerId => $router) {
            try {
                // Koneksi ke MikroTik menggunakan RouterOS Client
                $client = new \RouterOS\Client([
                    'host' => 'id-1.aqtnetwork.my.id',
                    'user' => $router['username'],
                    'pass' => $router['password'],
                    'port' => (int) $router['port_api'],
                ]);

                // Proses setiap pelanggan pada router ini
                if (isset($groupedDetails[$routerId]) && is_array($groupedDetails[$routerId])) {
                    foreach ($groupedDetails[$routerId] as $detail) {
                        $pppoeAkun = trim($detail['akun_pppoe']); // Pastikan akun PPPoE valid

                        if ($pppoeAkun) {
                            try {
                                // Ambil daftar pengguna PPPoE dari /ppp/secret
                                $querySecret = new Query('/ppp/secret/print');
                                $secretUsers = $client->query($querySecret)->read();

                                // Ambil daftar koneksi PPPoE aktif dari /ppp/active
                                $queryActive = new Query('/ppp/active/print');
                                $pppActiveConnections = $client->query($queryActive)->read();

                                $updated = false;

                                // Ubah profil PPPoE jika pengguna ditemukan di /ppp/secret
                                if (!empty($secretUsers)) {
                                    foreach ($secretUsers as $user) {
                                        if ($user['name'] === $pppoeAkun) {
                                            $pId = $user['.id'];

                                            // Edit profil PPPoE ke "isolir-biller"
                                            $updateProfileQuery = new Query('/ppp/secret/set');
                                            $updateProfileQuery->equal('.id', $pId);
                                            $updateProfileQuery->equal('profile', 'isolir-biller');
                                            $client->query($updateProfileQuery)->read();

                                            $apiResponses[$routerId][] = [
                                                'status' => 'success',
                                                'message' => "Profil PPPoE berhasil diubah: $pppoeAkun",
                                            ];

                                            $updated = true;
                                            break;
                                        }
                                    }
                                }

                                if (!$updated) {
                                    $apiResponses[$routerId][] = [
                                        'status' => 'error',
                                        'message' => "Akun PPPoE tidak ditemukan di /ppp/secret: $pppoeAkun",
                                    ];
                                }

                                $removed = false;

                                // Hapus koneksi PPPoE aktif jika ditemukan di /ppp/active
                                if (!empty($pppActiveConnections)) {
                                    foreach ($pppActiveConnections as $connection) {
                                        if ($connection['name'] === $pppoeAkun) {
                                            $pppId = $connection['.id'];

                                            // Hapus koneksi aktif
                                            $removeActiveQuery = new Query('/ppp/active/remove');
                                            $removeActiveQuery->equal('.id', $pppId);
                                            $client->query($removeActiveQuery)->read();

                                            $apiResponses[$routerId][] = [
                                                'status' => 'success',
                                                'message' => "Koneksi PPPoE berhasil dihapus: $pppoeAkun",
                                            ];

                                            $removed = true;
                                            break;
                                        }
                                    }
                                }

                                if (!$removed) {
                                    $apiResponses[$routerId][] = [
                                        'status' => 'error',
                                        'message' => "Akun PPPoE tidak ditemukan di /ppp/active: $pppoeAkun",
                                    ];
                                }
                            } catch (\Exception $e) {
                                $apiResponses[$routerId][] = [
                                    'status' => 'error',
                                    'message' => "Error saat memproses akun PPPoE: $pppoeAkun - " . $e->getMessage(),
                                ];
                            }
                        } else {
                            $apiResponses[$routerId][] = [
                                'status' => 'error',
                                'message' => 'Data akun_pppoe tidak valid untuk router ID: ' . $routerId,
                            ];
                        }
                    }
                } else {
                    \Log::warning("Grouped details tidak ditemukan untuk router ID: $routerId");
                }
            } catch (\Exception $e) {
                \Log::error('Error saat koneksi ke router: ' . $e->getMessage());
                $apiResponses[$routerId][] = [
                    'status' => 'error',
                    'message' => "Error saat koneksi ke router ID: $routerId - " . $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'sessions' => 'success',
            'message' => 'Proses Isolir PPPoE selesai.',
            'api_responses' => $apiResponses,
        ]);
    }


    public function bukaIsolir(Request $request)
    {
        $ids = $request->input('ids', []);

        // Validasi apakah ada ID yang dipilih
        if (empty($ids)) {
            return response()->json(['message' => 'Tidak ada pengguna yang dipilih.'], 400);
        }

        // Ambil data pelanggan berdasarkan ID
        $pelanggan = Pelanggan::whereIn('id', $ids)->get();

        if ($pelanggan->isEmpty()) {
            return response()->json(['message' => 'Pengguna tidak ditemukan.'], 404);
        }

        // Grouping pelanggan berdasarkan router_id
        $groupedDetails = [];
        foreach ($pelanggan as $plg) {
            if (!$plg->profile_paket) {
                return response()->json(['message' => "Profil paket tidak tersedia untuk pelanggan ID {$plg->id}."], 400);
            }

            $routerId = $plg->router_id;
            $groupedDetails[$routerId][] = [
                'akun_pppoe' => trim($plg->akun_pppoe),
                'id_pelanggan' => $plg->id,
                'profile_paket' => $plg->profile_paket,
            ];
        }

        // Ambil data MikroTik berdasarkan router_id
        $routerIds = array_keys($groupedDetails);
        $mikrotikData = Mikrotik::whereIn('router_id', $routerIds)->get()->keyBy('router_id');

        $apiResponses = [];
        foreach ($mikrotikData as $routerId => $router) {
            try {
                // Koneksi ke MikroTik menggunakan RouterOS Client
                $client = new \RouterOS\Client([
                    'host' => 'id-1.aqtnetwork.my.id',
                    'user' => $router->username,
                    'pass' => $router->password,
                    'port' => (int) $router->port_api,
                ]);

                // Proses setiap pelanggan pada router ini
                if (!isset($groupedDetails[$routerId]) || !is_array($groupedDetails[$routerId])) {
                    \Log::warning("Tidak ada pelanggan untuk router ID: $routerId");
                    continue;
                }

                // Ambil daftar pengguna PPPoE dari /ppp/secret
                $querySecret = new Query('/ppp/secret/print');
                $secretUsers = collect($client->query($querySecret)->read());

                // Ambil daftar koneksi PPPoE aktif dari /ppp/active
                $queryActive = new Query('/ppp/active/print');
                $pppActiveConnections = collect($client->query($queryActive)->read());

                foreach ($groupedDetails[$routerId] as $detail) {
                    $pppoeAkun = $detail['akun_pppoe'];
                    $profilePaket = $detail['profile_paket'];
                    $updated = false;
                    $removed = false;

                    if (!$pppoeAkun) {
                        $apiResponses[$routerId][] = [
                            'status' => 'error',
                            'message' => "Data akun PPPoE kosong untuk pelanggan ID {$detail['id_pelanggan']}",
                        ];
                        continue;
                    }

                    // Ubah profil PPPoE jika pengguna ditemukan di /ppp/secret
                    $user = $secretUsers->firstWhere('name', $pppoeAkun);
                    if ($user) {
                        $updateProfileQuery = new Query('/ppp/secret/set');
                        $updateProfileQuery->equal('.id', $user['.id']);
                        $updateProfileQuery->equal('profile', $profilePaket);
                        $client->query($updateProfileQuery)->read();

                        $apiResponses[$routerId][] = [
                            'status' => 'success',
                            'message' => "Profil PPPoE diubah untuk: $pppoeAkun",
                        ];
                        $updated = true;
                    } else {
                        $apiResponses[$routerId][] = [
                            'status' => 'error',
                            'message' => "Akun PPPoE tidak ditemukan di /ppp/secret: $pppoeAkun",
                        ];
                    }

                    // Hapus koneksi PPPoE aktif jika ditemukan di /ppp/active
                    $activeConnection = $pppActiveConnections->firstWhere('name', $pppoeAkun);
                    if ($activeConnection) {
                        $removeActiveQuery = new Query('/ppp/active/remove');
                        $removeActiveQuery->equal('.id', $activeConnection['.id']);
                        $client->query($removeActiveQuery)->read();

                        $apiResponses[$routerId][] = [
                            'status' => 'success',
                            'message' => "Koneksi PPPoE berhasil dihapus: $pppoeAkun",
                        ];
                        $removed = true;
                    } else {
                        $apiResponses[$routerId][] = [
                            'status' => 'error',
                            'message' => "Akun PPPoE tidak ditemukan di /ppp/active: $pppoeAkun",
                        ];
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Error saat koneksi ke router: ' . $e->getMessage());
                $apiResponses[$routerId][] = [
                    'status' => 'error',
                    'message' => "Error saat koneksi ke router ID: $routerId - " . $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'sessions' => 'success',
            'message' => 'Proses Buka Isolir PPPoE selesai.',
            'api_responses' => $apiResponses,
        ]);
    }


    public function broadcastWA(Request $request)
    {
        $ids = $request->input('ids', []);
        $pesan = $request->input('message'); // Ambil pesan dari request
        \Log::info('Data request:', $request->all());
        if (empty($ids)) {
            return response()->json(['message' => 'Tidak ada pengguna yang dipilih.'], 400);
        }

        if (!$pesan) {
            return response()->json(['message' => 'Pesan WhatsApp tidak boleh kosong.'], 400);
        }

        $pelanggan = Pelanggan::whereIn('id', $ids)->get();
        if ($pelanggan->isEmpty()) {
            return response()->json(['message' => 'Pengguna tidak ditemukan.'], 404);
        }

        // Grouping pelanggan berdasarkan router_id
        $groupedDetails = [];
        foreach ($pelanggan as $plg) {
            if (!$plg->profile_paket) {
                \Log::warning("Profil paket tidak tersedia untuk pelanggan ID {$plg->id}, dilewati.");
                continue; // Lewati pelanggan tanpa profil paket
            }

            $routerId = $plg->router_id;
            $groupedDetails[$routerId][] = [
                'akun_pppoe' => trim($plg->akun_pppoe),
                'id_pelanggan' => $plg->id,
                'profile_paket' => $plg->profile_paket,
            ];
        }

        $routerIds = array_keys($groupedDetails);
        $mikrotikData = Mikrotik::whereIn('router_id', $routerIds)->get()->keyBy('router_id');

        $apiResponses = [];
        $token = 'g3ZXCoCHeR1y75j4xJoz';

        if (!$token) {
            return response()->json(['success' => false, 'message' => 'Token API tidak ditemukan.'], 400);
        }

        foreach ($mikrotikData as $routerId => $router) {
            try {
                $client = new \RouterOS\Client([
                    'host' => 'id-1.aqtnetwork.my.id',
                    'user' => $router->username,
                    'pass' => $router->password,
                    'port' => (int) $router->port_api,
                ]);

                if (!isset($groupedDetails[$routerId]) || !is_array($groupedDetails[$routerId])) {
                    \Log::warning("Tidak ada pelanggan untuk router ID: $routerId");
                    continue;
                }

                // Ambil daftar pengguna PPPoE dari MikroTik
                $querySecret = new Query('/ppp/secret/print');
                $secretUsers = collect($client->query($querySecret)->read());

                foreach ($groupedDetails[$routerId] as $pelanggan) {
                    $matchedUser = $secretUsers->firstWhere('name', $pelanggan['akun_pppoe']);

                    if ($matchedUser) {
                        $telepon = Pelanggan::where('id', $pelanggan['id_pelanggan'])->value('nomor_telepon');

                        if (!$telepon) {
                            \Log::warning("Nomor telepon tidak ditemukan untuk pelanggan ID: {$pelanggan['id_pelanggan']}");
                            continue;
                        }

                        // Kirim pesan WA melalui API Fonnte
                        $response = Http::withHeaders([
                            'Authorization' => $token
                        ])->post('https://api.fonnte.com/send', [
                            'target' => $telepon,
                            'message' => $pesan,
                            'countryCode' => '62', // Kode Negara (62 untuk Indonesia)
                        ]);

                        $apiResponses[] = [
                            'id_pelanggan' => $pelanggan['id_pelanggan'],
                            'telepon' => $telepon,
                            'status' => $response->successful() ? 'success' : 'failed',
                            'message' => $response->successful() ? 'Pesan berhasil dikirim' : 'Gagal mengirim pesan'
                        ];
                    }
                }
            } catch (\Exception $e) {
                \Log::error("Error saat koneksi ke router ID: $routerId - " . $e->getMessage());
                $apiResponses[] = [
                    'router_id' => $routerId,
                    'status' => 'info',
                    'message' => $e->getMessage(),
                ];
            }
        }
        ActivityLogger::log('Melakukan Broadcast Whatsapp', '');

        return response()->json([
            'status' => 'success',
            'message' => 'Proses pengiriman WA selesai.',
            'api_responses' => $apiResponses,
        ]);
    }


    public function broadcastWAPS(Request $request)
    {

        $mikrotikId = $request->mikrotikId;
        $message = $request->message;
        $token = 'g3ZXCoCHeR1y75j4xJoz';

        // Ambil informasi MikroTik dari database
        $mikrotik = Mikrotik::where('router_id', $mikrotikId)->where('unique_id', auth()->user()->unique_id)->first();

        if (!$mikrotik) {
            return response()->json([
                'status' => 'error',
                'message' => 'MikroTik tidak ditemukan dalam database.',
            ], 404);
        }

        try {
            // Koneksi ke MikroTik dengan RouterOS API
            $client = new \RouterOS\Client([
                'host' => 'id-1.aqtnetwork.my.id',
                'user' => $mikrotik->username,
                'pass' => $mikrotik->password,
                'port' => (int) $mikrotik->port_api,
            ]);

            // Ambil data dari /ppp/secret
            $query = new Query('/ppp/secret/print');
            $pppSecrets = $client->query($query)->read();

            if (empty($pppSecrets)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tidak ada data PPP Secret yang ditemukan.',
                ], 404);
            }

            // Lakukan broadcast WA ke setiap pengguna PPP Secret
            foreach ($pppSecrets as $secret) {
                $username = $secret['name'] ?? null;

                if ($username) {
                    // Cek apakah ada pelanggan dengan router_id dan unique_id yang sesuai
                    $pelanggan = Pelanggan::where('router_id', $mikrotikId)
                        ->where('unique_id', auth()->user()->unique_id)
                        ->where('akun_pppoe', $username)
                        ->first();

                    if ($pelanggan) {
                        $phoneNumber = $pelanggan->nomor_telepon; // Ambil nomor HP pelanggan

                        if ($phoneNumber) {

                            // Kirim pesan WA melalui API Fonnte
                            $response = Http::withHeaders([
                                'Authorization' => $token
                            ])->post('https://api.fonnte.com/send', [
                                'target' => $phoneNumber,
                                'message' => $message,
                                'countryCode' => '62', // Kode Negara (62 untuk Indonesia)
                            ]);
                        }
                    }
                }
            }
            ActivityLogger::log('Melakukan Broadcast WhatsApp Per Site', '');

            return response()->json([
                'status' => 'success',
                'message' => "Pesan berhasil dikirim ke semua pengguna di MikroTik ID: $mikrotikId",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data dari MikroTik atau mengirim pesan.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
