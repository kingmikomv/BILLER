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
    //private $apiBase = 'http://103.160.63.163:3000/api';
private $apiBase = 'http://103.160.63.163:3000/api';


    public function index()
    {
        return view('ROLE.MEMBER.WHATSAPP.index');
    }



    public function getStatus(Request $request)
    {
        $sessionId = auth()->user()->unique_member;
        $response = Http::get("{$this->apiBase}/status", ['session_id' => $sessionId]);
        return response()->json($response->json());
    }

    public function getQRCode(Request $request)
    {
        $sessionId = auth()->user()->unique_member;
        $response = Http::get("{$this->apiBase}/qr", ['session_id' => $sessionId]);
        return response()->json($response->json());
    }

    public function startSession(Request $request)
    {
        $sessionId = auth()->user()->unique_member;
        $response = Http::get("{$this->apiBase}/start", ['session_id' => $sessionId]);
        return response()->json($response->json());
    }

    public function disconnectSession(Request $request)
    {
        $sessionId = auth()->user()->unique_member;
        $response = Http::get("{$this->apiBase}/disconnect", ['session_id' => $sessionId]);
        return response()->json($response->json());
    }


















    public function template()
    {
        $templates = \App\Models\MessageTemplate::all()->keyBy('name');

        return view('ROLE.MEMBER.WHATSAPP.template', compact('templates'));
    }
    public function store(Request $request)
    {

        $user = auth()->user();

        $template = MessageTemplate::updateOrCreate(
            [
                'user_id' => $user->id,
                'name' => $request->name,
                'tipe' => 'invoice',
            ],
            [
                'content' => $request->content,
            ]
        );

        return redirect()->back()->with('success', 'Template saved successfully!');
    }
    public function bulkStore(Request $request)
    {
        $user = auth()->user();
        $templates = $request->input('templates', []);

        foreach ($templates as $name => $content) {
            MessageTemplate::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'name' => $name,
                    'tipe' => 'invoice',
                ],
                [
                    'content' => $content,
                ]
            );
        }

        return redirect()->back()->with('success', 'Semua template berhasil disimpan.');
    }
    public function saveAdminNumber(Request $request)
    {

        // Simpan ke database
        WhatsappSession::updateOrCreate(
            ['session_id' => $request->session_id],
            ['admin_number' => $request->admin_number]
        );

        return response()->json(['message' => 'Data berhasil disimpan']);
    }
}
