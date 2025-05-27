<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use Illuminate\Http\Request;
use App\Models\Freeradius\Radcheck;

class VoucherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|unique:vouchers',
            'password' => 'required|string|min:3',
            'nas_id' => 'required|exists:nas,id',
        ]);

        $user = auth()->user();

        // Cek apakah member sudah mencapai batas voucher
        if ($user->vouchers()->count() >= $user->max_vouchers) {
            return back()->withErrors(['limit' => 'Batas maksimal voucher telah tercapai']);
        }

        // Simpan ke tabel vouchers
        $voucher = Voucher::create([
            'user_id' => $user->id,
            'nas_id' => $request->nas_id,
            'username' => $request->username,
            'password' => $request->password,
            'used' => false,
        ]);

        // Insert ke tabel radcheck FreeRADIUS
        Radcheck::create([
            'username' => $request->username,
            'attribute' => 'Cleartext-Password',
            'op' => ':=',
            'value' => $request->password,
        ]);

        return redirect()->route('vouchers.index')->with('success', 'Voucher berhasil dibuat');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
