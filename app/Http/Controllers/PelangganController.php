<?php

namespace App\Http\Controllers;

use RouterOS\Query;
use RouterOS\Client;
use App\Models\Mikrotik;
use App\Models\Pelanggan;
use App\Models\PaketPppoe;
use Illuminate\Foundation\Mix;
use Illuminate\Http\Request;

class PelangganController extends Controller
{
    public function index()
    {
        $uniqueId = auth()->user()->unique_id;

        // Ambil semua router yang dimiliki oleh user
        $mikrotikRouters = Mikrotik::where('unique_id', $uniqueId)->get();

        // Ambil semua pelanggan terkait router yang dimiliki user
        $plg = Pelanggan::whereIn('router_id', $mikrotikRouters->pluck('router_id'))
            ->with('paket')->get();

        $onlineStatus = [];

        foreach ($mikrotikRouters as $mikrotik) {
            try {
                // Membuat koneksi ke MikroTik API
                $client = new Client([
                    'host' => 'id-1.aqtnetwork.my.id:' . $mikrotik->port_api,
                    'user' => $mikrotik->username,
                    'pass' => $mikrotik->password,
                ]);

                // Ambil daftar active connections
                $query = new Query('/ppp/active/print');
                $activeConnections = $client->query($query)->read();

                // Cek status online pelanggan
                foreach ($plg as $pelanggan) {
                    if ($pelanggan->router_id == $mikrotik->router_id) {
                        $isOnline = collect($activeConnections)->contains(function ($connection) use ($pelanggan) {
                            return isset($connection['name']) && $connection['name'] === $pelanggan->akun_pppoe;
                        });
                        $onlineStatus[$pelanggan->akun_pppoe] = $isOnline ? 'Online' : 'Offline';
                    }
                }
            } catch (\Exception $e) {
                // Jika gagal koneksi, tandai semua pelanggan pada router ini offline
                foreach ($plg as $pelanggan) {
                    if ($pelanggan->router_id == $mikrotik->router_id) {
                        $onlineStatus[$pelanggan->akun_pppoe] = 'Offline';
                    }
                }
            }
        }

        // Gabungkan data pelanggan dengan status online
        foreach ($plg as $pelanggan) {
            $pelanggan->status = $onlineStatus[$pelanggan->akun_pppoe] ?? 'Offline';
        }
        //dd($plg);
        // Return ke view dengan data pelanggan
        return view('ROLE.MEMBER.PELANGGAN.index', compact('plg'));
    }


    public function formulir()
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
        //dd($onlineRouters);
        // Mengubah $onlineRouters menjadi Collection
        $onlineRoutersCollection = collect($onlineRouters);

        // Jika ada router yang online, ambil data dari model PaketPPPoE
        if ($onlineRoutersCollection->count() > 0) {
            // Mengambil data paket PPPoE yang sesuai dari model PaketPPPoE
            $paketPPPoEs = PaketPPPoE::whereIn('unique_id', $onlineRoutersCollection->pluck('unique_id'))->get();
            //dd($paketPPPoEs);
            // Mengirim data paket PPPoE dan router yang online ke view
            return view('ROLE.MEMBER.PELANGGAN.formulir', compact('paketPPPoEs', 'onlineRouters'));
        }

        // Jika tidak ada router yang online, kirimkan pesan atau tampilan lain
        //return view('ROLE.MEMBER.PELANGGAN.formulir', ['message' => 'Tidak ada router yang online']);
    }


    public function addPelanggan(Request $request)
    {
        $a = PaketPppoe::where('kode_paket', $request->input('kodePaket'))->first();

        $uniqueId = auth()->user()->unique_id;
        $kdpkt = $request->input('kodePaket');
        $kode_paket = PaketPppoe::where('kode_paket', $kdpkt)->first();

        $routerUsername = $kode_paket->username;
        $mikrotik = Mikrotik::where('username', $routerUsername)->first();

        $namaPelanggan = $request->input('namaPelanggan');
        $akunPppoe = $request->input('akunPppoe');
        $passPppoe = $request->input('passwordPppoe');
        $alamat = $request->input('alamat');
        $telepon = $request->input('telepon');

        $portApi = $mikrotik->port_api;
        $Username = $mikrotik->username;
        $Password = $mikrotik->password;
        $profil = $kode_paket->profile;
        $kode_paket2 = $kode_paket->kode_paket;

        try {
            $client = new Client([
                'host' => 'id-1.aqtnetwork.my.id:' . $portApi,
                'user' => $Username,
                'pass' => $Password,
            ]);

            $query = new Query('/ppp/secret/add');
            $query->equal('name', $akunPppoe);
            $query->equal('password', $passPppoe);
            $query->equal('service', 'pppoe');
            $query->equal('profile', $profil);

            $client->query($query)->read();
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['mikrotik_pppoe' => 'Gagal menambahkan akun PPPoE: ' . $e->getMessage()]);
        }

        // Status pembayaran
        $tanggalDaftar = now(); // Tanggal pendaftaran
        $tanggalPembayaranSelanjutnya = $tanggalDaftar->copy()->addMonth(); // Pembayaran selanjutnya bulan depan
        $statusPembayaran = now()->format('Y-m') === $tanggalDaftar->format('Y-m') ? 'Sudah Dibayar' : 'Belum Dibayar';

        $pelanggan = Pelanggan::create([
            'pelanggan_id' => 'PEL_' . rand(100, 9999999),
            'router_id' => $mikrotik->router_id,
            'unique_id' => $uniqueId,
            'router_username' => $Username,
            'kode_paket' => $kode_paket2,
            'nama_pelanggan' => $namaPelanggan,
            'akun_pppoe' => $akunPppoe,
            'password_pppoe' => $passPppoe,
            'alamat' => $alamat,
            'nomor_telepon' => $telepon,
            'tanggal_daftar' => $tanggalDaftar,
            'pembayaran_selanjutnya' => $tanggalPembayaranSelanjutnya,
            'status_pembayaran' => $statusPembayaran,
        ]);

        return redirect()->route('pelanggan')->with('success', 'Pelanggan berhasil ditambahkan');
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
    
            // Filter data untuk mencari koneksi dengan nama yang sesuai dengan akun PPPoE
            $filteredConnections = array_filter($activeConnections, function ($connection) use ($akunPelanggan) {
                return isset($connection['name']) && $connection['name'] === "<pppoe-" . $akunPelanggan . ">";
            });
    
            if (empty($filteredConnections)) {
                return response()->json(['error' => 'Tidak ada koneksi PPPoE yang ditemukan untuk akun ini'], 404);
            }
    
            $uptime = $filteredConnections[0]['uptime'];
            $formattedUptime = $this->formatUptime($uptime); // Format uptime
    
            // Jika diminta untuk menampilkan halaman, kirimkan data pelanggan
            return view('ROLE.MEMBER.PELANGGAN.data', [
                'pelanggan' => $pelanggan,
                'mikrotik' => $mikrotik,
                'formattedUptime' => $formattedUptime,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan saat menghubungkan ke MikroTik API: ' . $e->getMessage()], 500);
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






    // public function getPelangganData($id)
    // {
    //     $pelanggan = Pelanggan::with('paket')->find($id);

    //     if (!$pelanggan) {
    //         return response()->json(['error' => 'Pelanggan tidak ditemukan'], 404);
    //     }

    //     $pelId = $pelanggan->pelanggan_id;
    //     $routerId = $pelanggan->router_id;
    //     $akunPppoe = $pelanggan->akun_pppoe;
    //     $namaPelanggan = $pelanggan->nama_pelanggan;
    //     $alamat = $pelanggan->alamat;

    //     $mikrotik = Mikrotik::where('router_id', $routerId)->first();

    //     if (!$mikrotik) {
    //         return response()->json(['error' => 'Router tidak ditemukan'], 404);
    //     }



    //     try {

    //         $client = new Client([
    //             'host' => 'id-1.aqtnetwork.my.id:' . $mikrotik->port_api,
    //             'user' => $mikrotik->username,
    //             'pass' => $mikrotik->password,
    //         ]);

    //         if (!$client->connect()) {
    //             return response()->json(['error' => 'Tidak dapat terhubung ke MikroTik'], 500);
    //         }

    //         // Ambil data akun PPPoE aktif
    //         $response = $client->query(
    //             (new Query('/ppp/active/print'))
    //         )->read();

    //         // Filter data berdasarkan 'name'
    //         $filteredResponse = array_filter($response, function ($item) use ($akunPppoe) {
    //             return isset($item['name']) && $item['name'] === $akunPppoe;
    //         });



    //         if (empty($filteredResponse)) {
    //             return response()->json(['error' => 'Akun PPPoE tidak ditemukan di MikroTik'], 404);
    //         }

    //         $ipAddress = $filteredResponse[0]['address'] ?? null;

    //         if (!$ipAddress) {
    //             return response()->json(['error' => 'IP Address untuk akun PPPoE tidak ditemukan'], 404);
    //         }

    //         // Ping ke IP address akun PPPoE
    //         $pingResponse = $client->query(
    //             (new Query('/ping'))
    //                 ->equal('address', 'youtube.com')
    //                 ->equal('count', 10)
    //         )->read();
    //             dd($pingResponse);
    //         // $ping = isset($pingResponse[0]['time']) ? $pingResponse[0]['time'] : null;
    //         // $packetLoss = isset($pingResponse[0]['packet-loss']) ? $pingResponse[0]['packet-loss'] : null;

    //         // $data = [
    //         //     'pelanggan_id' => $pelId,
    //         //     'nama_pelanggan' => $namaPelanggan,
    //         //     'alamat' => $alamat,
    //         //     'ping_stats' => [
    //         //         'ip_address' => $ipAddress,
    //         //         'ping' => $ping,
    //         //         'packet_loss' => $packetLoss,
    //         //     ],
    //         // ];

    //         return view('ROLE/MEMBER/PELANGGAN/data', compact('data'));
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
    //     }
    // }

}
