<?php

namespace App\Http\Controllers;

use App\Models\Usaha;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

use Intervention\Image\Image;
use App\Helpers\ActivityLogger;
use Illuminate\Support\Facades\File;


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

    $request->validate([
        'nama_usaha' => 'required|string|max:255',
        'alamat_usaha' => 'required|string',
        'telepon_usaha' => 'nullable|string|max:15',
        'deskripsi_usaha' => 'nullable|string',
        'logo_usaha' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:2048',
    ]);

    $usaha = $user->usaha()->first();
    $data = $request->except('logo_usaha');

    if ($request->hasFile('logo_usaha')) {
        $uploadPath = $_SERVER['DOCUMENT_ROOT'] . '/usaha_logos/';

        // Buat folder jika belum ada
        if (!File::exists($uploadPath)) {
            File::makeDirectory($uploadPath, 0755, true);
        }

        // Hapus file lama jika ada
        if ($usaha && $usaha->logo_usaha) {
            $oldPath = $uploadPath . $usaha->logo_usaha;
            if (File::exists($oldPath)) {
                File::delete($oldPath);
            }
        }

        $file = $request->file('logo_usaha');
        $ext = $file->getClientOriginalExtension();
        $filename = 'logo_' . time() . '.' . $ext;
        $file->move($uploadPath, $filename);

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
