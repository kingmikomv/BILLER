<?php

namespace App\Http\Controllers;

use RouterOS\Query;
use App\Models\User;
use RouterOS\Client;
use App\Models\Mikrotik;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


class MemberController extends Controller
{
    public function pekerja()
    {
        return view('ROLE.MEMBER.PEKERJA.index');
    }
    public function addPekerja(Request $request)
{
    // Validasi input
    $validator = Validator::make($request->all(), [
        'namaPekerja' => ['required', 'string', 'max:255'],
        'usernamePekerja' => ['required', 'string', 'max:50', 'unique:users,username'],
        'emailPekerja' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
        'passwordPekerja' => ['required', 'string', 'min:8', 'confirmed'],
        'posisiPekerja' => ['required', 'in:teknisi,cs,penagih'],
        'noTeleponPekerja' => ['required', 'string', 'max:15'],
    ]);

    // Jika validasi gagal, kembalikan error
    if ($validator->fails()) {
        return redirect()->back()->withErrors($validator)->withInput();
    }

    // Simpan data ke database
    $pekerja = new User();
    $pekerja->unique_id = auth()->user()->unique_id;
    $pekerja->unique_id_pekerja = 'PEK_' . Str::uuid();
    $pekerja->name = $request->input('namaPekerja');
    $pekerja->username = $request->input('usernamePekerja'); // Simpan username
    $pekerja->email = $request->input('emailPekerja');
    $pekerja->password = Hash::make($request->input('passwordPekerja'));
    $pekerja->role = $request->input('posisiPekerja');
    $pekerja->phone = $request->input('noTeleponPekerja');
    $pekerja->email_verified_at = now();
    $pekerja->save();

    // Redirect atau response JSON
    return redirect()->back()->with('success', 'Pekerja berhasil ditambahkan');
}
}
