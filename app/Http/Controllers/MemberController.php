<?php

namespace App\Http\Controllers;

use RouterOS\Query;
use App\Models\User;
use RouterOS\Client;
use App\Models\Mikrotik;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Helpers\ActivityLogger;
use App\Models\ProfilPerusahaan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


class MemberController extends Controller
{
    public function pekerja()
    {
        $pekerja = User::where('parent_id', auth()->user()->id)->where('role', '!=', 'superadmin')->get();
        return view('ROLE.MEMBER.PEKERJA.index', compact('pekerja'));
    }
    public function addPekerja(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'namaPekerja' => ['required', 'string', 'max:255'],
            'usernamePekerja' => ['required', 'string', 'max:50', 'unique:users,username'],
            'emailPekerja' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'passwordPekerja' => ['required', 'string', 'min:8', 'confirmed'],
            'posisiPekerja' => ['required', 'in:teknisi,cs,penagih,sales'],
            'noTeleponPekerja' => ['required', 'string', 'max:15'],
        ]);

        // Jika validasi gagal, kembalikan error
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Simpan data ke database
        $pekerja = new User();
        $pekerja->parent_id = auth()->user()->id;
        $pekerja->name = $request->input('namaPekerja');
        $pekerja->username = $request->input('usernamePekerja'); // Simpan username
        $pekerja->email = $request->input('emailPekerja');
        $pekerja->password = Hash::make($request->input('passwordPekerja'));
        $pekerja->role = $request->input('posisiPekerja');
        $pekerja->phone = $request->input('noTeleponPekerja');
        $pekerja->email_verified_at = now();
        $pekerja->save();
        ActivityLogger::log('Menambahkan Pekerja Baru', 'Nama Pekerja : '.$request->input('namaPekerja'). " Posisi : ". $request->input('posisiPekerja'));

        // Redirect atau response JSON
        return redirect()->back()->with('success', 'Pekerja berhasil ditambahkan');
    }



    public function company()
    {
        $user = Auth::user(); // Ambil user yang login
        $profil = ProfilPerusahaan::where('user_id', $user->id)->first(); 
        return view('ROLE.MEMBER.COMPANY.company', compact('profil'));
    }
    public function updateCompany(Request $request, $id)
    {
        // Validasi data input
        $request->validate([
            'nama_perusahaan' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'nomor_izin_isp' => 'nullable|string|max:255',
            'nomor_izin_jartaplok' => 'nullable|string|max:255',
            'npwp' => 'nullable|string|max:255',
            'alamat' => 'nullable|string',
            'website' => 'nullable|string|max:255',
            'email_perusahaan' => 'required|email|max:255',
            'nomor_telepon' => 'nullable|string|max:20',
            'nomor_whatsapp' => 'nullable|string|max:20',
            'nama_owner' => 'nullable|string|max:255',
            'nama_finance' => 'nullable|string|max:255',
            'nomor_telepon_finance' => 'nullable|string|max:20',
        ]);

        // Cari data profil berdasarkan ID
        $profil = ProfilPerusahaan::find($id);
        if($profil == null) {
            $profil = ProfilPerusahaan::create([
                'user_id' => Auth::user()->id,
                'nama_perusahaan' => $request->nama_perusahaan,
                'brand' => $request->brand,
                'nomor_izin_isp' => $request->nomor_izin_isp,
                'nomor_izin_jartaplok' => $request->nomor_izin_jartaplok,
                'npwp' => $request->npwp,
                'alamat' => $request->alamat,
                'website' => $request->website,
                'email_perusahaan' => $request->email_perusahaan,
                'nomor_telepon' => $request->nomor_telepon,
                'nomor_whatsapp' => $request->nomor_whatsapp,
                'nama_owner' => $request->nama_owner,
                'nama_finance' => $request->nama_finance,
                'nomor_telepon_finance' => $request->nomor_telepon_finance,
            ]);

        } else {
            // Update data profil
            $profil->update([
                'nama_perusahaan' => $request->nama_perusahaan,
                'brand' => $request->brand,
                'nomor_izin_isp' => $request->nomor_izin_isp,
                'nomor_izin_jartaplok' => $request->nomor_izin_jartaplok,
                'npwp' => $request->npwp,
                'alamat' => $request->alamat,
                'website' => $request->website,
                'email_perusahaan' => $request->email_perusahaan,
                'nomor_telepon' => $request->nomor_telepon,
                'nomor_whatsapp' => $request->nomor_whatsapp,
                'nama_owner' => $request->nama_owner,
                'nama_finance' => $request->nama_finance,
                'nomor_telepon_finance' => $request->nomor_telepon_finance,
                
            ]);

        }

        return redirect()->back()->with('success', 'Profil perusahaan berhasil diperbarui!');
    }
}
