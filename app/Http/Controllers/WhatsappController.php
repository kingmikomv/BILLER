<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WhatsappController extends Controller
{
    //
    
    public function index(){
        return view('ROLE.MEMBER.WHATSAPP.index');
    }
    public function startSession(Request $request)
    {
        $sessionId = auth()->user()->unique_member;

        $response = Http::get("http://127.0.0.1:3000/api/start", [
            'session_id' => $sessionId,
        ]);

        return $response->body(); // akan mengembalikan HTML QR dari Node.js
    }
}
