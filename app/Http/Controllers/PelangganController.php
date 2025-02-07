<?php

namespace App\Http\Controllers;

use RouterOS\Query;
use RouterOS\Client;
use App\Models\Mikrotik;
use App\Models\Pelanggan;
use App\Models\PaketPppoe;
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
    
}
