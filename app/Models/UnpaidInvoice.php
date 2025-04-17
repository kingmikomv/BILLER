<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnpaidInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'pelanggan_id',
        'bulan',
        'tahun',
        'jatuh_tempo',
        'sudah_dibayar',
    ];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class);
    }
}
