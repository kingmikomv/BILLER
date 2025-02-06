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
        return view('ROLE.MEMBER.PELANGGAN.index');
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
        //dd($request->input('pilihPaket'));
        $a = PaketPppoe::where('kode_paket', $request->input('kodePaket'))->first();
        //dd($a);

        $uniqueId = auth()->user()->unique_id;
        $kdpkt = $request->input('kodePaket');
        // Ambil data paket berdasarkan kode paket yang dipilih
        $kode_paket = PaketPppoe::where('kode_paket', $kdpkt)->first();
        //dd($kode_paket);
        $routerUsername = $kode_paket->username;  // Ambil username router dari paket
        $mikrotik = Mikrotik::where('username', $routerUsername)->first(); // Ambil data MikroTik


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
        //dd($kode_paket->profile);
        // Membuat koneksi API MikroTik
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
            $query->equal('profile', $profil); // Profile paket

            // Kirim perintah untuk menambahkan PPPoE secret
            $profiles = $client->query($query)->read();
            //dd($profiles);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['mikrotik_pppoe' => 'Gagal menambahkan akun PPPoE: ' . $e->getMessage()]);
        }

        // Jika berhasil, simpan data pelanggan ke database
        $pelanggan = Pelanggan::create([
            'pelanggan_id' => 'PEL_'.rand(100, 9999999),
            'unique_id' => $uniqueId,
            'router_username' => $Username,
            'kode_paket' => $kode_paket2,
            'nama_pelanggan' => $namaPelanggan,
            'akun_pppoe' => $akunPppoe,
            'password_pppoe' => $passPppoe,
            'alamat' => $alamat,
            'nomor_telepon' => $telepon,
        ]);

        return redirect()->route('pelanggan')->with('success', 'Pelanggan berhasil ditambahkan');
    }
}
