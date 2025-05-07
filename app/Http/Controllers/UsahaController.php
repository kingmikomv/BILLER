<?php

namespace App\Http\Controllers;

use App\Models\Usaha;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

use Intervention\Image\Image;
use App\Helpers\ActivityLogger;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;


class UsahaController extends Controller
{
    public function index()
    {
        $logs = ActivityLog::with('user')->latest()->paginate(25);
        $usaha = auth()->user()->usaha; // Ambil usaha milik user
        return view('ROLE.MEMBER.PROFIL.USAHA.index', compact('usaha', 'logs'));
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
        
            // Buat manager dan proses gambar ke JPEG
            $manager = new ImageManager(new GdDriver());
                $image = $manager->read($request->file('logo_usaha'))->toPng(); // encode to JPEG
        
            $filename = 'Logo.jpeg';
            $path = public_path('usaha_logos/' . $filename);
            $image->save($path);
        
            $data['logo_usaha'] = $filename;
        }
        
        if ($usaha) {
            $usaha->update($data);
            $message = 'Profil usaha berhasil diperbarui.';
        } else {
            $user->usaha()->create($data);
            $message = 'Profil usaha berhasil ditambahkan.';
        }
        ActivityLogger::log('Mengupdate Profil Usaha', '');

        return redirect()->route('profil.usaha')->with('success', $message);
    }
    
    

}
