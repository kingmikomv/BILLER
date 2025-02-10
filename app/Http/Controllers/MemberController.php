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

class MemberController extends Controller
{
    public function pekerja()
    {
        return view('ROLE.MEMBER.PEKERJA.index');
    }
    public function addPekerja(Request $request)
    {


        // Simpan data ke database
        $pekerja = new User();
        $pekerja->unique_id = auth()->user()->unique_id;
        $pekerja->unique_id_pekerja = 'PEK_' . Str::uuid();
        $pekerja->name = $request->input('namaPekerja');
        $pekerja->email = $request->input('emailPekerja');
        $pekerja->password = Hash::make($request->input('passwordPekerja'));
        $pekerja->role = $request->input('posisiPekerja');
        $pekerja->phone = $request->input('noTeleponPekerja');
        $pekerja->email_verified_at = now();
        $pekerja->save();
        dd($pekerja);
        // Redirect atau response JSON
        //return response()->json(['message' => 'Pekerja berhasil ditambahkan'], 200);
    }
}
