<?php

namespace App\Http\Controllers;

use RouterOS\Query;
use RouterOS\Client;
use App\Models\Mikrotik;
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
        $this->middleware(['auth', 'verified']);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
{
    $user = auth()->user();

    // Ambil Mikrotik milik user + user, pelanggan, paket, dan OLT-nya
    $mikrotikRouters = Mikrotik::with(['user', 'pelanggan.paket', 'olt'])
        ->where('user_id', $user->id)
        ->get();

    $onlineStatus = collect();
    $allPelanggans = collect();

    foreach ($mikrotikRouters as $router) {
        $routerPelanggans = $router->pelanggan ?? collect();
        $allPelanggans = $allPelanggans->merge($routerPelanggans);

        try {
            $client = new Client([
                'host' => 'id-1.aqtnetwork.my.id:' . $router->port_api,
                'user' => $router->username,
                'pass' => $router->password,
            ]);

            $query = new Query('/ppp/active/print');
            $activeConnections = collect($client->query($query)->read());

            foreach ($routerPelanggans as $pelanggan) {
                $active = $activeConnections->firstWhere('name', $pelanggan->akun_pppoe);
                $ip = $active['address'] ?? null;
                $isOnline = !is_null($active);
                $isIsolir = $ip && str_starts_with($ip, '172');

                $status = $isIsolir ? 'Isolir' : ($isOnline ? 'Online' : 'Offline');
                $onlineStatus[$pelanggan->akun_pppoe] = $status;
            }
        } catch (\Exception $e) {
            foreach ($routerPelanggans as $pelanggan) {
                $onlineStatus[$pelanggan->akun_pppoe] = 'Offline';
            }
        }
    }

    foreach ($allPelanggans as $pelanggan) {
        $pelanggan->status = $onlineStatus[$pelanggan->akun_pppoe] ?? 'Offline';
    }

    // Statistik
    $totalOnline = $onlineStatus->filter(fn($status) => $status === 'Online')->count();
    $totalOffline = $onlineStatus->filter(fn($status) => $status === 'Offline')->count();
    $totalIsolir = $onlineStatus->filter(fn($status) => $status === 'Isolir')->count();

    // 🔧 Ambil OLT
    $allOlts = $mikrotikRouters->pluck('olt')->filter()->unique('id');
    $totalOlt = $allOlts->count();

    // View superadmin
    if ($user->hasRole('superadmin')) {
        return view('ROLE.SUMIN.index', compact('totalOlt', 'allOlts'));
    }

    // View member
    if ($user->hasRole('member')) {
        $totalPelanggan = $allPelanggans->count();
        $totalPelangganAktif = $allPelanggans->where('status', 'Aktif')->count();
        $riwayatPemasangan = $allPelanggans->where('status_terpasang', 'Sudah Dipasang')
                                           ->sortByDesc('tanggal_terpasang');
        $totalRouter = $mikrotikRouters->count();

        return view('ROLE.MEMBER.index', compact(
            'riwayatPemasangan',
            'totalPelanggan',
            'totalPelangganAktif',
            'totalOnline',
            'totalOffline',
            'totalIsolir',
            'totalRouter',
            'totalOlt',
            'allOlts'
        ));
    }

    if ($user->hasRole('teknisi')) {
        return view('ROLE.PEKERJA.index');
    }

    if ($user->hasRole('penagih')) {
        return response()->json(['message' => 'penagih']);
    }

    if ($user->hasRole('cs')) {
        return view('ROLE.CS.index');
    }

    if ($user->hasRole('sales')) {
        return view('ROLE.SALES.index');
    }

    return redirect()->route('home');
}

    
}
