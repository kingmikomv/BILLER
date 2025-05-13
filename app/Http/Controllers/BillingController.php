<?php

namespace App\Http\Controllers;

use App\Models\Mikrotik;
use App\Models\Pelanggan;
use App\Models\PaketPppoe;
use Illuminate\Http\Request;
use App\Models\UnpaidInvoice;
use App\Exports\PelangganExport;
use App\Imports\PelangganImport;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Response;


class BillingController extends Controller
{
    public function unpaid()
{
    $unpaidInvoices = UnpaidInvoice::with('pelanggan', 'pelanggan.mikrotik', 'pelanggan.paket')
        ->where('sudah_dibayar', false)
        ->get();

    return view("ROLE.MEMBER.BILLING.unpaid", compact('unpaidInvoices'));

}




    public function paid()
    {
    }
    public function riwayat()
    {
    }



    public function bil_pelanggan()
    {
        $user = auth()->user();

        // Ambil semua MikroTik yang dimiliki user ini
        $mikrotikIds = $user->mikrotik()->pluck('id');

        // Ambil pelanggan berdasarkan `mikrotik_id`
        $pelanggan = Pelanggan::whereIn('mikrotik_id', $mikrotikIds)
            ->orderBy('id', 'desc')
            ->get();

        // Ambil paket PPPoE berdasarkan `mikrotik_id`
        $paketpppoe = PaketPppoe::whereIn('mikrotik_id', $mikrotikIds)->get();

        return view('ROLE.MEMBER.BILLING.bill_pelanggan', compact('pelanggan', 'paketpppoe'));
    }
    public function hapusData($id)
    {
        $data = Pelanggan::find($id);
        if (!$data) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }
    
        $mikrotik_id = $data->mikrotik_id;
        $mikrotik = Mikrotik::find($mikrotik_id);
    
        if (!$mikrotik) {
            return response()->json(['error' => 'MikroTik tidak ditemukan'], 404);
        }
    
        try {
            // Koneksi ke MikroTik
            $client = new \RouterOS\Client([
                'host' => 'id-1.aqtnetwork.my.id',  // Sesuaikan dengan model Anda
                'user' => $mikrotik->username,
                'pass' => $mikrotik->password,
                'port' => (int) $mikrotik->port_api,
            ]);
    
            $akun_pppoe = $data->akun_pppoe;
    
            // **Ambil daftar PPP Secret berdasarkan name**
            $querySecret = (new \RouterOS\Query('/ppp/secret/print'))
                ->where('name', $akun_pppoe);
            $secrets = $client->query($querySecret)->read();
    
            \Log::info('Data PPP Secret: ' . json_encode($secrets));
    
            if (empty($secrets)) {
                return response()->json(['error' => 'PPP Secret tidak ditemukan untuk akun: ' . $akun_pppoe], 404);
            }
    
            // **Ambil daftar PPP Active berdasarkan name**
            $queryActive = (new \RouterOS\Query('/ppp/active/print'))
                ->where('name', $akun_pppoe);
            $actives = $client->query($queryActive)->read();
    
            \Log::info('Data PPP Active: ' . json_encode($actives));
    
            // **Hapus PPP SECRET berdasarkan .id**
            foreach ($secrets as $secret) {
                $client->query((new \RouterOS\Query('/ppp/secret/remove'))
                    ->equal('.id', $secret['.id']))
                    ->read();
            }
    
            // **Hapus PPP ACTIVE berdasarkan name**
            foreach ($actives as $active) {
                $client->query((new \RouterOS\Query('/ppp/active/remove'))
                    ->equal('.id', $active['.id']))
                    ->read();
            }
    
            // Hapus data pelanggan dari database
            $data->delete();
    
            return response()->json(['success' => 'Data berhasil dihapus']);
    
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal menghapus data: ' . $e->getMessage()], 500);
        }
    }
    






    public function kirimwa(Request $request)
    {
        $nomor = $request->nomor;
        $pesan = $request->pesan;
        $token = 'g3ZXCoCHeR1y75j4xJoz';

        if (!$token) {
            return response()->json(['success' => false, 'message' => 'Token API tidak ditemukan.'], 400);
        }

        $response = Http::withHeaders([
            'Authorization' => $token
        ])->post('https://api.fonnte.com/send', [
                    'target' => $nomor,
                    'message' => $pesan,
                    'countryCode' => '62', // Kode Negara (62 untuk Indonesia)
                ]);

        if ($response->successful()) {
            return response()->json(['success' => true, 'message' => 'Pesan berhasil dikirim.']);
        } else {
            return response()->json(['success' => false, 'message' => 'Gagal mengirim pesan.']);
        }
    }



    public function bcwa()
    {
    }













    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        Excel::import(new PelangganImport, $request->file('file'));

        return redirect()->back()->with('success', 'Data berhasil diimport!');
    }
    public function exportExcel()
    {
        return Excel::download(new PelangganExport, 'data_pelanggan.xlsx');
    }
}
