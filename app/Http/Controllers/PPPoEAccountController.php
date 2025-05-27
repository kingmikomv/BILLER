<?php

namespace App\Http\Controllers;

use App\Models\PPPoEAccount;
use Illuminate\Http\Request;
use App\Models\Freeradius\Radcheck;

class PPPoEAccountController extends Controller
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
        'username' => 'required|unique:pppoe_accounts',
        'password' => 'required',
        'nas_id' => 'required|exists:nas,id',
    ]);

    $user = auth()->user();

    if ($user->pppoeAccounts()->count() >= $user->max_pppoe_accounts) {
        return back()->withErrors(['limit' => 'Limit PPPoE account tercapai']);
    }

    $account = PPPoEAccount::create([
        'user_id' => $user->id,
        'nas_id' => $request->nas_id,
        'username' => $request->username,
        'password' => $request->password,
        'profile' => $request->profile,
    ]);

    Radcheck::create([
        'username' => $request->username,
        'attribute' => 'Cleartext-Password',
        'op' => ':=',
        'value' => $request->password,
    ]);

    return redirect()->route('pppoe-accounts.index')->with('success', 'PPPoE account berhasil ditambahkan');
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
