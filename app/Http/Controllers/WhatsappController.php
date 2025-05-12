<?php

namespace App\Http\Controllers;

use session;
use Illuminate\Http\Request;
use App\Models\MessageTemplate;
use App\Models\WhatsappSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class WhatsappController extends Controller
{
    //
    
    public function index(){
        return view('ROLE.MEMBER.WHATSAPP.index');
    }
    public function template(){
        $templates = \App\Models\MessageTemplate::all()->keyBy('name');

        return view('ROLE.MEMBER.WHATSAPP.template', compact('templates'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $user = Auth::user();

        // Update if already exists, else create
        $template = MessageTemplate::updateOrCreate(
            ['user_id' => $user->id, 'name' => $request->name],
            ['content' => $request->content]
        );

        return redirect()->back()->with('success', 'Template saved successfully!');
    }
    public function saveAdminNumber(Request $request)
    {
       
        // Simpan ke database
        WhatsappSession::updateOrCreate(
            ['session_id' => $request->session_id],
            ['admin_number' =>  $request->admin_number]
        );

        return response()->json(['message' => 'Data berhasil disimpan']);
    }
}
