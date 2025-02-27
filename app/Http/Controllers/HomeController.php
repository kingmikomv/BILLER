<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth','verified']);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Check user role
        if (auth()->user()->hasRole('superadmin')) {
            dd('superadmin');
        } elseif (auth()->user()->hasRole('member')) {
            $riwayatPemasangan = Pelanggan::where('unique_id', auth()->user()->unique_id)
            ->where('status_terpasang', 'Sudah Dipasang')->orderBy('tanggal_terpasang', 'desc')
            ->get();
            return view('ROLE/MEMBER/index', compact('riwayatPemasangan'));
        } elseif (auth()->user()->hasRole('teknisi')) {
            return view('ROLE/PEKERJA/index');

        } elseif (auth()->user()->hasRole('penagih')) {
            dd('penagih');
        }elseif (auth()->user()->hasRole('cs')) {
            dd('cs');
        }
    
        // // Default view if no role matches
        // return view('home');
    }
    
}
