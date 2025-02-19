<?php
namespace App\Exports;

use App\Models\Pelanggan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PelangganExport implements FromCollection, WithHeadings
{
    /**
     * Mengambil data pelanggan dari database
     */
    public function collection()
    {
        return Pelanggan::select(
            'pelanggan_id', 
            'nama_pelanggan', 
            'router_id', 
            'router_username', 
            'kode_paket', 
            'profile_paket', 
            'akun_pppoe', 
            'password_pppoe', 
            'alamat', 
            'nomor_telepon', 
            'tanggal_daftar', 
            'pembayaran_selanjutnya', 
            'status_pembayaran'
        )->get();
    }

    /**
     * Menentukan header kolom dalam file Excel
     */
    public function headings(): array
    {
        return [
            'Kode Pelanggan', 
            'Nama Pelanggan', 
            'Router ID', 
            'Router Username', 
            'Kode Paket', 
            'Profil Paket', 
            'Akun PPPoE', 
            'Password PPPoE', 
            'Alamat', 
            'Nomor Telepon', 
            'Tanggal Daftar', 
            'Pembayaran Selanjutnya', 
            'Status Pembayaran'
        ];
    }
}
