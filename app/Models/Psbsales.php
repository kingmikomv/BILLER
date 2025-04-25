<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Psbsales extends Model
{
    use HasFactory;
    protected $table = 'psbsales';
    protected $fillable = [
        'sales',
        'nama_psb',
        'alamat_psb',
        'foto_lokasi_psb',
        'paket_psb',
        'tanggal_ingin_pasang',
        'telepon',
        'status'
    ];
    
}
