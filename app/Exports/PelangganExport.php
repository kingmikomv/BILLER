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
            'status_pembayaran',
            'metode_pembayaran'
        )->get()->map(function ($item) {
            return [
                'Kode Pelanggan' => $item->pelanggan_id,
                'Nama Pelanggan' => $item->nama_pelanggan ?? '-',
                'Router ID' => $item->router_id ?? '-',
                'Router Username' => $item->router_username ?? '-',
                'Kode Paket' => "'" . ($item->kode_paket ?? '-'), // Menjaga format angka sebagai string
                'Profil Paket' => $item->profile_paket ?? '-',
                'Akun PPPoE' => $item->akun_pppoe,
                'Password PPPoE' => $item->password_pppoe,
                'Alamat' => $item->alamat,
                'Nomor Telepon' => "'" . ($item->nomor_telepon ?? '-'), // Menjaga angka nol di awal
                'Tanggal Daftar' => $item->tanggal_daftar ? \Carbon\Carbon::parse($item->tanggal_daftar)->format('Y-m-d') : '-',
                'Pembayaran Selanjutnya' => $item->pembayaran_selanjutnya ? \Carbon\Carbon::parse($item->pembayaran_selanjutnya)->format('Y-m-d') : '-',
                'Status Pembayaran' => $item->status_pembayaran,
                'Metode Pembayaran' => $item->metode_pembayaran,

            ];
        });
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
            'Status Pembayaran',
            'Metode Pembayaran'

        ];
    }
    
}
