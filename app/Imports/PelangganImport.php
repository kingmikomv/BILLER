<?php
namespace App\Imports;

use App\Models\Pelanggan;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

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
            'router_id' => $row[0], 
            'router_username' => $row[1], 
            'kode_paket' => $row[2], 
            'profile_paket' => $row[3], 
            'nama_pelanggan' => $row[4], 
            'akun_pppoe' => $row[5], 
            'password_pppoe' => $row[6], 
            'alamat' => $row[7], 
            'nomor_telepon' => $row[8], 
            'tanggal_daftar' => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject(floor((float) $row[9]))->format('Y-m-d'), // Konversi format tanggal
            'pembayaran_selanjutnya' => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject(floor((float) $row[10]))->format('Y-m-d'),
            'status_pembayaran' => $row[11]
        ]);
    }
}

