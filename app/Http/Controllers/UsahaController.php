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
        $realPath = $file->getRealPath();

        // Buat gambar resource dari input
        switch ($ext) {
            case 'jpeg':
            case 'jpg':
                $src = imagecreatefromjpeg($realPath);
                break;
            case 'png':
                $src = imagecreatefrompng($realPath);
                break;
            case 'gif':
                $src = imagecreatefromgif($realPath);
                break;
            case 'webp':
                $src = imagecreatefromwebp($realPath);
                break;
            default:
                return back()->with('error', 'Format gambar tidak didukung.');
        }

        // Cek ukuran asli
        $width = imagesx($src);
        $height = imagesy($src);

        // Buat canvas baru dengan transparansi
        $canvas = imagecreatetruecolor($width, $height);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefill($canvas, 0, 0, $transparent);

        // Copy gambar asli ke canvas
        imagecopy($canvas, $src, 0, 0, 0, 0, $width, $height);
        imagedestroy($src);

        // Buat nama file unik dan simpan sebagai PNG
        $filename = 'Logo.png';
        $fullPath = $uploadPath . $filename;

        if (!imagepng($canvas, $fullPath)) {
            imagedestroy($canvas);
            $err = error_get_last();
            return back()->with('error', 'Gagal menyimpan gambar: ' . $err['message']);
        }

        imagedestroy($canvas);

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
