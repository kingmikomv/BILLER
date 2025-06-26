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

    // Validasi Input
   

    $usaha = $user->usaha()->first();
    $data = $request->except('logo_usaha');

    if ($request->hasFile('logo_usaha')) {
        // Path folder upload
        $uploadPath = $_SERVER['DOCUMENT_ROOT'] . '/usaha_logos/';

        // Buat folder jika belum ada
        if (!File::exists($uploadPath)) {
            File::makeDirectory($uploadPath, 0755, true);
        }

        // Hapus logo lama jika ada
        if ($usaha && $usaha->logo_usaha) {
            $oldPath = $uploadPath . $usaha->logo_usaha;
            if (File::exists($oldPath)) {
                File::delete($oldPath);
            }
        }

        $file = $request->file('logo_usaha');
        $ext = strtolower($file->getClientOriginalExtension());

        // Buka gambar dengan GD
        switch ($ext) {
            case 'jpeg':
            case 'jpg':
                $src = imagecreatefromjpeg($file);
                break;
            case 'png':
                $src = imagecreatefrompng($file);
                break;
            case 'gif':
                $src = imagecreatefromgif($file);
                break;
            case 'webp':
                $src = imagecreatefromwebp($file);
                break;
            default:
                return back()->with('error', 'Format gambar tidak didukung.');
        }

        // Buat nama file unik
        $filename = 'Logo.png';
        $fullPath = $uploadPath . $filename;

        // Simpan sebagai PNG
        if (!imagepng($src, $fullPath)) {
            return back()->with('error', 'Gagal menyimpan file gambar.');
        }

        // Bebaskan memori
        imagedestroy($src);

        // Simpan ke database
        $data['logo_usaha'] = $filename;
    }

    // Simpan atau update
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
