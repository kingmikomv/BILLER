<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\PaketPppoe;
use App\Models\TiketPsb;
use App\Models\Pelanggan;
use Illuminate\Http\Request;

class TeknisiController extends Controller
{
    public function datapsb()
    {
        $dataUniqueId = TiketPsb::where('unique_id', auth()->user()->unique_id)
        ->where('status_tiket', 'Belum Dikonfirmasi')
        ->pluck('no_tiket'); // Ambil hanya 'no_tiket' dalam bentuk array

        $cocokPelanggan = Pelanggan::where('unique_id', auth()->user()->unique_id)
        ->whereIn('no_tiket', $dataUniqueId)
        ->get();

        $riwayatPemasangan = Pelanggan::where('unique_id', auth()->user()->unique_id)
        ->where('status_terpasang', 'Sudah Dipasang')
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
