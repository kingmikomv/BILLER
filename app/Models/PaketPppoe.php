<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaketPppoe extends Model
{
    use HasFactory;
    protected $table = 'paketpppoe';
    protected $primaryKey = 'id';

    protected $guarded = [];
    // Di model Pelanggan
 // Relasi ke Mikrotik
 public function mikrotik()
{
    return $this->belongsTo(Mikrotik::class, 'mikrotik_id');
}
public function pelanggan()
{
    return $this->hasMany(Pelanggan::class, 'paket_id');
}
}
