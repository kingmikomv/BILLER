<?php

namespace App\Http\Controllers;

use App\Models\User;
use RouterOS\Client;
use App\Models\Undian;
use App\Models\Mikrotik;
use App\Models\Pelanggan;
use Illuminate\Http\Request;
use RouterOS\Query;

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
        // Ambil data user dengan role 'member' dan MikroTik yang dimiliki
        $dataMikrotik = User::where('role', 'member')
            ->with([
                'mikrotik' => function ($query) {
                    $query->withCount('pelanggan'); // Hitung jumlah pelanggan per MikroTik
                }
            ])
            ->get();

        return view('ROLE.SUMIN.pelanggan', compact('dataMikrotik'));
    }
    public function daftarundian()
    {

        $mikrotiks = Mikrotik::with('user')
            ->whereHas('user', function ($query) {
                $query->where('email', 'support-noc@aqtnetwork.my.id');
            })
            ->get();
        $dftrundian = Undian::orderBy('created_at', 'desc')->get();
        // dd($mikrotiks);
        return view('ROLE.SUMIN.daftarundian', compact('mikrotiks', 'dftrundian'));
    }
    public function tambahundian(Request $request)
    {
        // Validasi Input
        $request->validate([
            'mikrotik_id' => 'required|exists:mikrotik,id',
            'nama_undian' => 'required|string|max:255',
            'foto_undian' => 'nullable|image|mimes:jpg,jpeg,png|max:2048', // Hanya gambar
            'tanggal_kocok' => 'required|date',
        ]);

        // Proses Upload Gambar
        $fotoPath = null;
        if ($request->hasFile('foto_undian')) {
            $foto = $request->file('foto_undian');
            
            // Tentukan path penyimpanan di dalam folder public
            $destinationPath = public_path('undian/undian');
            
            // Pastikan folder tujuan ada
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }
            
            // Simpan file dengan nama unik
            $fotoName = time() . '_' . $foto->getClientOriginalName();
            $foto->move($destinationPath, $fotoName);
            
            // Simpan path yang dapat diakses secara publik
            $fotoPath = 'undian/undian/' . $fotoName;
        }
        

        // Simpan Data Undian ke Database
        Undian::create([
            'mikrotik_id' => $request->mikrotik_id,
            'kode_undian' => 'UND-' . strtoupper(uniqid()), // Generate kode unik
            'nama_undian' => $request->nama_undian,
            'foto_undian' => $fotoPath, // Simpan path gambar
            'tanggal_kocok' => $request->tanggal_kocok,
        ]);

        return redirect()->back()->with('success', 'Undian berhasil ditambahkan!');
    }
    public function kocok()
    {

        $mikrotik = Undian::whereNull('pemenang')->orWhere('pemenang', '')->get();
        // Ambil semua undian yang berelasi dengan MikroTik tersebut
        //$undians = $mikrotik->undians;


        return view('ROLE.SUMIN.kocok', compact('mikrotik'));
    }
    public function spinner(Request $request)
    {
        $kode_undian = $request->input('kode');

        $kode_undian = Undian::where('kode_undian', $kode_undian)->first();

        if ($kode_undian->pemenang == null) {


            $mikrotik_id = $kode_undian->mikrotik_id;
            // Ambil data MikroTik berdasarkan ID
            $mikrotik = Mikrotik::findOrFail($mikrotik_id);

            // Koneksi ke MikroTik
            $client = new Client([
                'host' => 'id-1.aqtnetwork.my.id:' . $mikrotik->port_api,
                'user' => $mikrotik->username,
                'pass' => $mikrotik->password,
            ]);

            // Ambil daftar PPPoE aktif
            $query = new Query('/ppp/active/print');
            $activeConnections = $client->query($query)->read();

            // Ambil hanya username dari daftar pengguna aktif

            // Ambil username dari daftar
            $usernames = array_map(fn($user) => $user['name'] ?? 'Unknown', $activeConnections);

            return view('ROLE.SUMIN.spinner', compact('usernames', 'kode_undian'));
        } else {
            return redirect()->back()->with('error', 'Undian sudah memiliki pemenang!');
        }
    }


    public function updateWinner(Request $request)
    {
        $winner = $request->input('winner');
        $kodeUndian = $request->input('kode_undian');

        // Simpan pemenang undian
        Undian::where('kode_undian', $kodeUndian)->update([
            'pemenang' => $winner,
        ]);


        return response()->json([
            'message' => 'Pemenang berhasil diterima!',
            'winner' => $winner,
            'kode_undian' => $kodeUndian
        ]);
    }


}
