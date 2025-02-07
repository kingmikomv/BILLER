<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    use HasFactory;
    protected $table = 'pelanggan';
    protected $guarded = [];

    public function paket()
    {
        return $this->belongsTo(PaketPppoe::class, 'kode_paket', 'kode_paket');
    }
}
