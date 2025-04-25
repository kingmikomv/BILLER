<?php

namespace App\Http\Controllers;

use RouterOS\Query;
use App\Models\User;
use RouterOS\Client;
use App\Models\Mikrotik;
use App\Models\Psbsales;
use App\Models\PaketPppoe;
use Illuminate\Http\Request;
use App\Helpers\ActivityLogger;

class SalesController extends Controller
{
    public function data_sales(){
        $memberID = auth()->user();
        $idmember = User::where('id', $memberID->id)->first();
        if(auth()->user()->role == 'sales') {
            $data = Psbsales::where('parent_id', $idmember->parent_id)->get();
        }else{
            $data = Psbsales::where('parent_id', $idmember->id)->get();
        }
        return view('ROLE.SALES.data_psb_sales', compact('data'));
    }
    public function tambah_psb_sales(){
        return view('ROLE.SALES.tambah_psb_sales');
    }

    public function upload_psb_sales(Request $request)
    {
        if(auth()->user()->role !== 'sales') {
            return redirect()->back()->with('error', 'Unauthorized access.');
        }
        // Simpan data ke database
        $psb = new Psbsales();
        $psb->parent_id = auth()->user()->parent_id;
        $psb->sales = auth()->user()->name;
        $psb->nama_psb = $request->input('nama_psb');
        $psb->alamat_psb = $request->input('alamat_psb');


        if ($request->hasFile('foto_lokasi_psb')) {
            $file = $request->file('foto_lokasi_psb');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images/psbsales'), $filename);
            $psb->foto_lokasi_psb = '/images/psbsales/' . $filename;
        }
        $psb->paket_psb = $request->input('paket_psb');
        $psb->tanggal_ingin_pasang = $request->input('tanggal_ingin_pasang');
        $psb->telepon = $request->input('telepon');
        $psb->alasan = $request->input('alasan');

        $psb->status_pemasangan = 'Belum Dikonfirmasi'; // Status default
        $psb->status = 'Belum Dikonfirmasi'; // Status default
        $psb->save();

        return redirect()->back()->with('success', 'Data PSB Sales berhasil ditambahkan.');
    }
    public function acc_psb($id){
        $psb = Psbsales::find($id);
        //dd($psb);
        if ($psb) {
            $psb->status = 'Jadi';
            $psb->save();
            return redirect()->back()->with('success', 'PSB Jadi');
        } else {
            return redirect()->back()->with('error', 'PSB tidak ditemukan.');
        }
    }





    // MEMBER CS

    public function acc($id){
     
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
                $paketPPPoEs = PaketPppoe::whereIn('mikrotik_id', $onlineRoutersCollection->pluck('id'))->get();
                $psb = Psbsales::find($id);
                //return view('ROLE.SALES.transfer', compact('psb'));
                // Mengirim data paket PPPoE dan router yang online ke view
                return view('ROLE.SALES.transfer', compact('psb','paketPPPoEs', 'onlineRouters'));
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
    $user = auth()->user(); // Ambil user yang login
    $userId = $user->id;

    // Ambil data paket berdasarkan kode_paket
    $kodePaket = $request->input('kodePaket');
    $paket = PaketPppoe::where('kode_paket', $kodePaket)->firstOrFail();

    // Tentukan router berdasarkan role user
    if ($user->role === 'member') {
        $mikrotikQuery = Mikrotik::where('user_id', $userId);
    } else {
        // Untuk role teknisi atau cs
        $mikrotikQuery = Mikrotik::where('user_id', $user->parent_id);
    }

    $mikrotik = $mikrotikQuery->where('id', $paket->mikrotik_id)->first();

    if (!$mikrotik) {
        return redirect()->back()->withErrors(['mikrotik' => 'Router tidak ditemukan untuk user atau paket yang dipilih.']);
    }

    // Data input dari request
    $akunPppoe = $request->input('akunPppoe');
    $passPppoe = $request->input('passwordPppoe');
    $ssidWifi = $request->input('ssid');
    $passWifi = $request->input('passwifi');
    $tanggalinginpasang = $request->input('tip');
    $tanggalDaftar = now();
    $tanggalPembayaranSelanjutnya = $tanggalDaftar->copy()->addMonth();
    $statusPembayaran = $request->input('metode_pembayaran') === 'Prabayar' ? 'Sudah Dibayar' : 'Belum Dibayar';

    try {
        // Koneksi ke MikroTik API
        $client = new Client([
            'host' => 'id-1.aqtnetwork.my.id:' . $mikrotik->port_api,
            'user' => $mikrotik->username,
            'pass' => $mikrotik->password,
        ]);

        // Tambahkan akun PPPoE
        $query = (new Query('/ppp/secret/add'))
            ->equal('name', $akunPppoe)
            ->equal('password', $passPppoe)
            ->equal('service', 'pppoe')
            ->equal('profile', $paket->profile);

        $client->query($query)->read();

    } catch (\Exception $e) {
        return redirect()->back()->withErrors(['mikrotik_pppoe' => 'Gagal menambahkan akun PPPoE: ' . $e->getMessage()]);
    }

    // Buat data pelanggan
    $kodePsb = $this->generateKodePSB();
    $uniqueId = $user->unique_member . rand(100, 9999999);

    $pelanggan = $mikrotik->pelanggan()->create([
        'pelanggan_id' => $uniqueId,
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
    ]);

    if ($user->role === 'member') {
        $pelanggan->tiket()->create([
            'no_tiket' => $kodePsb,
            'status_tiket' => 'Belum Dikonfirmasi',
            'serialnumber' => $request->input('serialnumber'),
            'pelanggan_id' => $pelanggan->id,
            'parent_id' => $userId,
            'mikrotik_id' => $mikrotik->id,
            'router_username' => $mikrotik->username,
            'paket_id' => $paket->id,
            'akun_pppoe' => $akunPppoe,
            'password_pppoe' => $passPppoe,
            'alamat' => $request->input('alamat'),
            'nomor_telepon' => $request->input('telepon'),
            'tanggal_daftar' => $tanggalDaftar,
            'pembayaran_selanjutnya' => $tanggalPembayaranSelanjutnya,
            'tanggal_ingin_pasang' => $tanggalinginpasang,
            'tanggal_terpasang' => null,
            'pembayaran_yang_akan_datang' => null,
            'nama_ssid' => $ssidWifi,
            'password_ssid' => $passWifi,
            'mac_address' => $request->input('macadress'),
            'odp' => $request->input('odp'),
            'olt' => $request->input('olt'),
        ]);
    }else{
        $pelanggan->tiket()->create([
            'no_tiket' => $kodePsb,
            'status_tiket' => 'Belum Dikonfirmasi',
            'serialnumber' => $request->input('serialnumber'),
            'pelanggan_id' => $pelanggan->id,
            'parent_id' => $user->parent_id,
            'mikrotik_id' => $mikrotik->id,
            'router_username' => $mikrotik->username,
            'paket_id' => $paket->id,
            'akun_pppoe' => $akunPppoe,
            'password_pppoe' => $passPppoe,
            'alamat' => $request->input('alamat'),
            'nomor_telepon' => $request->input('telepon'),
            'tanggal_daftar' => $tanggalDaftar,
            'pembayaran_selanjutnya' => $tanggalPembayaranSelanjutnya,
            'tanggal_ingin_pasang' => $tanggalinginpasang,
            'tanggal_terpasang' => null,
            'pembayaran_yang_akan_datang' => null,
            'nama_ssid' => $ssidWifi,
            'password_ssid' => $passWifi,
            'mac_address' => $request->input('macadress'),
            'odp' => $request->input('odp'),
            'olt' => $request->input('olt'),
        ]);
    }
    $dataPsbSales = Psbsales::find($request->id);
    $dataPsbSales->status_pemasangan = 'Sudah Dikonfirmasi';
    $dataPsbSales->save();
    // Kirim notifikasi ke WhatsApp
    // Log aktivitas
    ActivityLogger::log(
        'Menambahkan Pelanggan Baru',
        'Nama Pelanggan : ' . $pelanggan->nama_pelanggan . " Dengan Nomor Tiket PSB : " . $kodePsb
    );

    return redirect()->back()->with('success', 'Pelanggan berhasil ditambahkan.');
}








       
    
}
