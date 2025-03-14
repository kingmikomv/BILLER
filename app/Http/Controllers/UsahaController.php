<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usaha;
use Illuminate\Support\Facades\File;


class UsahaController extends Controller
{
    public function index()
    {
        $usaha = auth()->user()->usaha; // Ambil usaha milik user
        return view('ROLE.MEMBER.PROFIL.USAHA.index', compact('usaha'));
    }

    public function create()
    {
        return view('usaha.create');
    }

    public function storeOrUpdate(Request $request)
    {
        $user = auth()->user();
    
        // Validasi Input
        $request->validate([
            'nama_usaha' => 'required|string|max:255',
            'alamat_usaha' => 'required|string',
            'telepon_usaha' => 'nullable|string|max:15',
            'deskripsi_usaha' => 'nullable|string',
            'logo_usaha' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Maks 2MB
        ]);
    
        // Ambil data usaha yang sudah ada
        $usaha = $user->usaha()->first();
    
        // Siapkan data kecuali logo
        $data = $request->except('logo_usaha');
    
        // Cek apakah ada file logo yang diunggah
        if ($request->hasFile('logo_usaha')) {
            // Hapus logo lama jika ada
            if ($usaha && $usaha->logo_usaha) {
                File::delete(public_path('usaha_logos/' . $usaha->logo_usaha));
            }
        
            // Simpan logo baru ke public/usaha_logos/
            $file = $request->file('logo_usaha');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('usaha_logos'), $filename);
            
            $data['logo_usaha'] = $filename;
        }
        
        if ($usaha) {
            $usaha->update($data);
            $message = 'Profil usaha berhasil diperbarui.';
        } else {
            $user->usaha()->create($data);
            $message = 'Profil usaha berhasil ditambahkan.';
        }
    
        return redirect()->route('profil.usaha')->with('success', $message);
    }
    
    

}
