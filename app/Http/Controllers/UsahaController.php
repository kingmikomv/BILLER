<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usaha;

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

    public function store(Request $request)
    {
        $request->validate([
            'nama_usaha' => 'required|string|max:255',
            'alamat' => 'required|string',
            'telepon' => 'nullable|string|max:15',
            'deskripsi' => 'nullable|string',
        ]);

        auth()->user()->usaha()->create($request->all());

        return redirect()->route('profil.usaha')->with('success', 'Profil usaha berhasil ditambahkan.');
    }
}
