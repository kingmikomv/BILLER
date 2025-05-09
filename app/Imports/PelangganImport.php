<?php

namespace App\Imports;

use App\Models\Mikrotik;
use App\Models\Pelanggan;
use App\Models\Tagihan;
use App\Models\Paket; // Pastikan model Paket ada jika diperlukan
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

class PelangganImport implements ToModel, WithStartRow
{
    public function startRow(): int
    {
        return 2;
    }

    public function model(array $row)
    {
        $uniqueId = auth()->user()->unique_member . rand(100, 9999999);
        $setting = DB::table('billing_seting')->first();

        // Cari router & paket
        $mikrotik = Mikrotik::where('router_id', $row[1])->first();
        $kodePaket = isset($row[3]) ? ltrim($row[3], "'") : null;
        $paket = DB::table('paketpppoe')->where('kode_paket', $kodePaket)->first(); // Jika pakai model, ganti dengan Paket::where()

        // Simpan pelanggan baru
        $pelanggan = Pelanggan::create([
            'pelanggan_id' => $uniqueId,
            'mikrotik_id' => $mikrotik?->id,
            'router_id' => $row[1] ?? null,
            'router_username' => $row[2] ?? null,
            'nama_pelanggan' => $row[0] ?? null,
            'kode_paket' => $kodePaket,
            'profile_paket' => $row[4] ?? null,
            'akun_pppoe' => $row[5] ?? null,
            'password_pppoe' => $row[6] ?? null,
            'alamat' => $row[7] ?? null,
            'nomor_telepon' => isset($row[8]) ? ltrim($row[8], "'") : null,
            'tanggal_daftar' => isset($row[9]) && !empty($row[9])
                ? date('Y-m-d', strtotime($row[9]))
                : null,
            'metode_pembayaran' => $row[10] ?? 'Pascabayar',
        ]);

        // Hitung tanggal tagihan
        $tanggalDaftar = $pelanggan->tanggal_daftar ? \Carbon\Carbon::parse($pelanggan->tanggal_daftar) : now();
        $tanggalGenerate = now();
        $tanggalJatuhTempo = $tanggalGenerate->copy()->addDays($setting->default_jatuh_tempo_hari);

        if ($setting->generate_invoice_mode === 'dimajukan' && $setting->dimajukan_hari !== null) {
            $tanggalGenerate = $tanggalDaftar->copy()->subDays($setting->dimajukan_hari);
            $tanggalJatuhTempo = $tanggalGenerate->copy()->addDays($setting->default_jatuh_tempo_hari);
        }

        // Hitung tagihan (prorata atau tidak)
        $jumlahTagihan = 0;
        if ($setting->prorata_enable && $tanggalDaftar && $paket) {
            $hariMulai = $tanggalDaftar->day;
            $jumlahHariBulanIni = $tanggalDaftar->daysInMonth;

            $prorata = $paket->harga_paket / $jumlahHariBulanIni * ($jumlahHariBulanIni - $hariMulai + 1);
            $jumlahTagihan = round($prorata / 1000) * 1000;
        } elseif ($paket) {
            $jumlahTagihan = round($paket->harga_paket / 1000) * 1000;
        }

        // Simpan tagihan
        $pelanggan->tagihan()->create([
            'invoice_id' => 'INV-' . strtoupper(Str::random(10)),
            'tanggal_generate' => $tanggalGenerate,
            'tanggal_jatuh_tempo' => $tanggalJatuhTempo,
            'tanggal_pembayaran' => $tanggalGenerate,
            'jumlah_tagihan' => $jumlahTagihan,
            'prorata' => $setting->prorata_enable,
            'status' => 'Belum Lunas',
        ]);

        return $pelanggan;
    }
}

