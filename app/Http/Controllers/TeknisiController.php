<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\TiketPsb;
use App\Models\Pelanggan;
use App\Models\PaketPppoe;
use App\Helpers\ActivityLogger;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TeknisiController extends Controller
{

    
    public function datapsb()
    {
        $user = auth()->user();
        $userId = $user->parent_id;
    
        // Ambil semua pelanggan_id dari TiketPsb yang terkait dengan teknisi (berdasarkan parent_id)
        $pelangganID = TiketPsb::whereHas('mikrotik', function ($query) use ($userId) {
                $query->where('parent_id', $userId);
            })
            ->pluck('pelanggan_id');
    
        // Ambil nomor tiket yang belum dikonfirmasi
        $dataUniqueId = TiketPsb::whereHas('mikrotik', function ($query) use ($userId) {
                $query->where('parent_id', $userId);
            })
            ->where('status_tiket', 'Belum Dikonfirmasi')
            ->pluck('no_tiket');
    
        // Ambil pelanggan yang tiketnya belum dikonfirmasi
        $cocokPelanggan = Pelanggan::whereIn('id', $pelangganID) // Gunakan 'id' bukan 'pelanggan_id' jika primary key tabel pelanggan adalah 'id'
            ->whereIn('no_tiket', $dataUniqueId)
            ->with(['mikrotik', 'paket'])
            ->get();
    
        // Ambil pelanggan yang sudah terpasang
        $riwayatPemasangan = Pelanggan::whereIn('id', $pelangganID) 
            ->where('status_terpasang', 'Sudah Dipasang')
            ->with(['mikrotik', 'paket'])
            ->get();
    
        return view('ROLE.PEKERJA.TEKNISI.datapsb', compact('cocokPelanggan', 'riwayatPemasangan'));
    }
    
    
    

    
    public function konfirmasiPemasangan($tiket_id)
    {
        $tid = $tiket_id;
        // Cari data berdasarkan nomor tiket
        $psb = TiketPsb::where('no_tiket', $tid)->first();
        $plg = Pelanggan::where('no_tiket', $tid)->first();
        $harga = PaketPppoe::where('kode_paket', $plg->kode_paket)->first();

        if($psb->no_tiket == $tid || $plg->no_tiket == $tid){
            $psb->tanggal_terpasang = now();
            $psb->status_tiket = 'Sudah Dikonfirmasi';
            $psb->save();
            $plg->status_terpasang = 'Sudah Dipasang';
            $plg->tanggal_terpasang = now();
            $plg->dipasang_oleh = auth()->user()->name;
            $plg->save();

            if($plg->metode_pembayaran == 'Prabayar'){
                $inv = Invoice::create([
                    'pelanggan_id' => $plg->id,
                    'jumlah' => $harga->harga_paket,
                    'status' => 'Lunas',
                    'tanggal_pembuatan' => now(),
                ]);
            }elseif($plg->metode_pembayaran == 'Pascabayar'){
                $inv = Invoice::create([
                    'pelanggan_id' => $plg->id,
                    'jumlah' => $harga->harga_paket,
                    'status' => 'Belum Lunas',
                    'tanggal_pembuatan' => now(),
                ]);
            }
            $token = 'g3ZXCoCHeR1y75j4xJoz';
            $message = "Yth. ".$plg->nama_pelanggan.",\n\n"
    . "Selamat! Pemasangan WiFi Anda telah selesai dan kini sudah dapat digunakan.\n\n"
    . "Berikut detail layanan Anda:\n"
    . "- Nama Pelanggan: ".$plg->nama_pelanggan."\n"
    . "- ID Pelanggan: ".$plg->pelanggan_id."\n"
    . "- Paket: ".$plg->profile_paket."\n"
    . "- Tanggal Aktivasi: ".$plg->tanggal_terpasang."\n\n"
    . "Silakan cek koneksi internet Anda dan pastikan semuanya berjalan lancar. Jika ada kendala atau pertanyaan, jangan ragu untuk menghubungi tim support kami.\n\n"
    . "Terima kasih telah memilih layanan kami. Selamat menikmati koneksi internet yang cepat dan stabil! 🚀\n\n"
    . "Hormat kami,\n"
    . "AQT Network";

            $pelanggan = Pelanggan::where('no_tiket', $psb->no_tiket)
                        ->where('no_tiket', $plg->no_tiket)
                        ->where('akun_pppoe', $plg->akun_pppoe)
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
                





        ActivityLogger::log('Teknisi '. auth()->user()->name .' Mengkonfirmasi PSB', 'Nomor Tiket PSB : ' .$tid);

            return redirect()->back()->with('success', 'Status pemasangan telah diperbarui!');
        } else {
            return redirect()->back()->with('error', 'Tiket tidak ditemukan!');
        }
    }
    public function show($tiket)
{
    $riwayat = Pelanggan::where('no_tiket', $tiket)->first();

    if ($riwayat) {
        return response()->json([
            'success' => true,
            'riwayat' => $riwayat
        ]);
    } else {
        return response()->json([
            'success' => false,
            'message' => 'Data tidak ditemukan'
        ]);
    }
}
}
