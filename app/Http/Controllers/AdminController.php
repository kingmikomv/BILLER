<?php

namespace App\Http\Controllers;

use RouterOS\Query;
use App\Models\User;
use RouterOS\Client;
use App\Models\Undian;
use App\Models\Mikrotik;
use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $pelanggan = Pelanggan::whereIn('pelanggan_id', $dftrundian->pluck('pemenang'))->get()->keyBy('pelanggan_id');
        //dd($pelanggan);
        return view('ROLE.SUMIN.daftarundian', compact('mikrotiks', 'dftrundian', 'pelanggan'));
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

            // Path penyimpanan di dalam folder subdomain
            $destinationPath = $_SERVER['DOCUMENT_ROOT'] . '/undian/undian';

            // Pastikan folder tujuan ada
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }

            // Simpan file dengan nama unik
            $fotoName = time() . '_' . preg_replace('/\s+/', '_', strtolower($foto->getClientOriginalName()));

            // Pindahkan file ke folder tujuan
            $foto->move($destinationPath, $fotoName);

            // URL akses gambar di subdomain
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
            $pelanggan_id = Pelanggan::where('mikrotik_id', $mikrotik_id)->get();


            // dd($pelanggan_id);

            return view('ROLE.SUMIN.spinner', compact('pelanggan_id', 'kode_undian'));
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


    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:undian,id',
            'kode_undian' => 'required|string|max:255',
            'nama_undian' => 'required|string|max:255',
            'tanggal_kocok' => 'required|date',
        ]);

        // Cari undian berdasarkan ID
        $undian = Undian::findOrFail($request->id);

        // Update data undian
        $undian->kode_undian = $request->kode_undian;
        $undian->nama_undian = $request->nama_undian;
        $undian->tanggal_kocok = $request->tanggal_kocok;
        $undian->save();

        return redirect()->back()->with('success', 'Data undian berhasil diperbarui.');
    }

    public function destroy($id)
    {
        // Cari data undian berdasarkan ID
        $undian = Undian::findOrFail($id);
    
        // Jika ada foto pemenang, hapus dari penyimpanan
        if ($undian->foto_pemenang) {
            $fotoPath = public_path('undian/pemenang/' . $undian->foto_pemenang);
            if (file_exists($fotoPath)) {
                unlink($fotoPath); // Hapus file dari folder
            }
        }
    
        // Hapus data undian dari database
        $undian->delete();
    
        return redirect()->back()->with('success', 'Data undian berhasil dihapus.');
    }
    

























    /////////////////////// API UNDIAN ///////////////////////

    public function getUndianApi(Request $request)
    {
        // Ambil token dari header Authorization
        $token = $request->header('Authorization');

        // Token yang valid
        $validToken = "Bearer 123456";

        // Cek apakah token sesuai
        if ($token !== $validToken) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Ambil data undian dari database
        $undian = DB::table('undian')->orderBy('id', 'DESC')->get(); // Sesuaikan dengan nama tabel

        return response()->json($undian);
    }

    public function uploadFotoPemenang(Request $request)
    {
        // Validasi request
        $request->validate([
            'undian_id' => 'required|exists:undian,id',
            'foto_pemenang' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Ambil data undian berdasarkan ID
        $undian = Undian::findOrFail($request->undian_id);

        if ($request->hasFile('foto_pemenang')) {
            $file = $request->file('foto_pemenang');

            // Buat nama file unik
            $filename = 'pemenang_' . time() . '.' . $file->getClientOriginalExtension();

            // Path penyimpanan di subdomain (public_html/biller/undian/pemenang)
            $destinationPath = $_SERVER['DOCUMENT_ROOT'] . '/undian/pemenang';

            // Buat folder jika belum ada
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }

            // Pindahkan file ke folder tujuan
            $file->move($destinationPath, $filename);

            // Update database dengan nama file baru
            $undian->foto_pemenang = $filename;
            $undian->save();

            return redirect()->back()->with('success', 'Foto pemenang berhasil diunggah.');
        }

        return redirect()->back()->with('error', 'Gagal mengunggah foto pemenang.');
    }

}
