<?php

namespace App\Imports;

use App\Models\Pelanggan;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class PelangganImport implements ToModel, WithStartRow
{
    public function startRow(): int
    {
        return 2; // Lewati baris pertama (header)
    }

    public function model(array $row)
    {
        return new Pelanggan([
            'pelanggan_id' => 'PEL_' . rand(100, 9999999),
            'unique_id' => auth()->user()->unique_id,
            'nama_pelanggan' => $row[1], // Sesuai urutan export
            'router_id' => $row[2],
            'router_username' => $row[3],
            'kode_paket' => ltrim($row[4], "'"), // Hapus tanda kutip dari string
            'profile_paket' => $row[5],
            'akun_pppoe' => $row[6],
            'password_pppoe' => $row[7],
            'alamat' => $row[8],
            'nomor_telepon' => ltrim($row[9], "'"), // Hapus kutip untuk nomor telepon
            'tanggal_daftar' => isset($row[10]) && !empty($row[10])
                ? date('Y-m-d', strtotime($row[10]))
                : null,

            'pembayaran_selanjutnya' => isset($row[11]) && !empty($row[11])
                ? date('Y-m-d', strtotime($row[11]))
                : null,
            'metode_pembayaran' => $row[12],
            'status_pembayaran' => $row[13],
        ]);
    }
}
