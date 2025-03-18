<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Mikrotik;
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
    $dataMikrotik = User::whereHas('mikrotik', function ($query) {
        $query->where('email', 'support-noc@aqtnetwork.my.id');
    })->get();

    dd($dataMikrotik);
}

}
