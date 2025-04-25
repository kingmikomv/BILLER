<?php

namespace App\Http\Controllers;

use App\Models\Psbsales;
use App\Models\User;
use Illuminate\Http\Request;

class SalesController extends Controller
{
    public function data_sales(){
        $memberID = auth()->user()->parent_id;
        $idmember = User::where('id', $memberID)->first();
        $data = Psbsales::where('parent_id', $idmember->id)->get();
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
        $psb->status = 0; // Status default
        $psb->save();

        return redirect()->back()->with('success', 'Data PSB Sales berhasil ditambahkan.');
    }
}
