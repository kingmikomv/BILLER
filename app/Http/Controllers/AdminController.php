<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Mikrotik;
use App\Models\Pelanggan;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function manageUsers()
    {
        if (auth()->user()->role !== 'superadmin') {
            abort(403, 'Unauthorized action.');
        }

        // Logika untuk mengelola pengguna
    }
    public function pelangganaqt()
{
    // Ambil data user dengan role 'member' dan MikroTik yang dimiliki
    $dataMikrotik = User::where('role', 'member')
        ->with(['mikrotik' => function ($query) {
            $query->withCount('pelanggan'); // Hitung jumlah pelanggan per MikroTik
        }])
        ->get();

    return view('ROLE.SUMIN.pelanggan', compact('dataMikrotik'));
}




}
