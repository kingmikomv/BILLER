<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use Illuminate\Http\Request;
use App\Exports\PelangganExport;
use App\Imports\PelangganImport;
use App\Models\PaketPppoe;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Response;


class BillingController extends Controller
{
    public function unpaid(){

    }
    public function paid(){
        
    }
    public function riwayat(){
        
    }
    public function bil_pelanggan(){
        $pelanggan = Pelanggan::where('unique_id', auth()->user()->unique_id)
        ->orderBy('id', 'desc') // Mengurutkan dari yang terbaru
        ->get(); // Jangan lupa panggil get() untuk mengambil data
        $paketpppoe = PaketPppoe::where('unique_id', auth()->user()->unique_id)->get();
    return view('ROLE.MEMBER.BILLING.bill_pelanggan', compact('pelanggan', 'paketpppoe'));
    
    }

    public function kirimwa(Request $request) {
        $nomor = $request->nomor;
        $pesan = $request->pesan;
        $token = 'g3ZXCoCHeR1y75j4xJoz';

        if (!$token) {
            return response()->json(['success' => false, 'message' => 'Token API tidak ditemukan.'], 400);
        }

        $response = Http::withHeaders([
            'Authorization' => $token
        ])->post('https://api.fonnte.com/send', [
            'target' => $nomor,
            'message' => $pesan,
            'countryCode' => '62', // Kode Negara (62 untuk Indonesia)
        ]);

        if ($response->successful()) {
            return response()->json(['success' => true, 'message' => 'Pesan berhasil dikirim.']);
        } else {
            return response()->json(['success' => false, 'message' => 'Gagal mengirim pesan.']);
        }
    }



    public function bcwa(){
        
    }













    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        Excel::import(new PelangganImport, $request->file('file'));

        return redirect()->back()->with('success', 'Data berhasil diimport!');
    }
    public function exportExcel()
    {
        return Excel::download(new PelangganExport, 'data_pelanggan.xlsx');
    }
   
}
