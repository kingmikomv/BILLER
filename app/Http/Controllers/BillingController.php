<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function unpaid(){

    }
    public function paid(){
        
    }
    public function riwayat(){
        
    }
    public function bil_pelanggan(){
        $pelanggan = Pelanggan::where('unique_id', auth()->user()->unique_id)
        ->orderBy('created_at', 'desc') // Mengurutkan dari yang terbaru
        ->get(); // Jangan lupa panggil get() untuk mengambil data
    
    return view('ROLE.MEMBER.BILLING.bill_pelanggan', compact('pelanggan'));
    
    }
    public function bcwa(){
        
    }
}
