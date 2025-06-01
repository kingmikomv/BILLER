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

    public function index()
    {
        return view('ROLE.MEMBER.WHATSAPP.index');
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
