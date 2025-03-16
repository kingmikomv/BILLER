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
        $this->middleware(['auth','verified']);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = auth()->user();
    
        // Ambil semua router yang dimiliki oleh user berdasarkan router_id
        $routerIds = $user->routers ? $user->routers->pluck('router_id') : collect();
        $mikrotikRouters = Mikrotik::whereIn('router_id', $routerIds)->get();
    
        // Ambil semua pelanggan terkait router yang dimiliki user
        $plg = Pelanggan::whereIn('router_id', $routerIds)->with('paket')->get();
    
        // Inisialisasi array untuk menyimpan status pelanggan
        $onlineStatus = [];
    
        foreach ($mikrotikRouters as $mikrotik) {
            try {
                // Buat koneksi ke MikroTik API
                $client = new Client([
                    'host' => 'id-1.aqtnetwork.my.id:' . $mikrotik->port_api,
                    'user' => $mikrotik->username,
                    'pass' => $mikrotik->password,
                ]);
    
                // Ambil daftar active connections
                $query = new Query('/ppp/active/print');
                $activeConnections = collect($client->query($query)->read());
    
                // Proses setiap pelanggan di router ini
                foreach ($plg->where('router_id', $mikrotik->router_id) as $pelanggan) {
                    // Cari koneksi aktif berdasarkan akun PPPoE
                    $activeConnection = $activeConnections->firstWhere('name', $pelanggan->akun_pppoe);
                    $isOnline = !is_null($activeConnection);
    
                    // Ambil IP pelanggan dari koneksi aktif (jika ada)
                    $ipAddress = $activeConnection['address'] ?? null;
    
                    // Cek apakah IP pelanggan dimulai dengan '172' (Isolir)
                    $isIsolir = $ipAddress && str_starts_with($ipAddress, '172');
    
                    // Tentukan status pelanggan
                    $onlineStatus[$pelanggan->akun_pppoe] = $isIsolir ? 'Isolir' : ($isOnline ? 'Online' : 'Offline');
                }
            } catch (\Exception $e) {
                // Jika gagal koneksi, tandai semua pelanggan pada router ini offline
                foreach ($plg->where('router_id', $mikrotik->router_id) as $pelanggan) {
                    $onlineStatus[$pelanggan->akun_pppoe] = 'Offline';
                }
            }
        }
    
        // Gabungkan data pelanggan dengan status online
        foreach ($plg as $pelanggan) {
            $pelanggan->status = $onlineStatus[$pelanggan->akun_pppoe] ?? 'Offline';
        }
    
        // Hitung jumlah pelanggan Online, Offline, dan Isolir
        $totalOnline = collect($onlineStatus)->filter(fn($status) => $status === 'Online')->count();
        $totalOffline = collect($onlineStatus)->filter(fn($status) => $status === 'Offline')->count();
        $totalIsolir = collect($onlineStatus)->filter(fn($status) => $status === 'Isolir')->count();
    
        // Check user role dan kirim data ke view sesuai peran pengguna
        if ($user->hasRole('superadmin')) {


            return response()->json(['message' => 'superadmin']);
            
        } elseif ($user->hasRole('member')) {
            $totalPelanggan = $plg->count();
            $totalPelangganAktif = $plg->where('status', 'Aktif')->count();
            $riwayatPemasangan = $plg->where('status_terpasang', 'Sudah Dipasang')->sortByDesc('tanggal_terpasang');
            $totalRouter = $mikrotikRouters->count();
    
            return view('ROLE.MEMBER.index', compact(
                'riwayatPemasangan', 'totalPelanggan', 'totalPelangganAktif',
                'totalOnline', 'totalOffline', 'totalIsolir', 'totalRouter'
            ));
        } elseif ($user->hasRole('teknisi')) {
            return view('ROLE.PEKERJA.index');
        } elseif ($user->hasRole('penagih')) {
            return response()->json(['message' => 'penagih']);
        } elseif ($user->hasRole('cs')) {
            return response()->json(['message' => 'cs']);
        }
    
        // Default view jika peran tidak terdefinisi
        return redirect()->route('home');
    }
    

    
    
}
