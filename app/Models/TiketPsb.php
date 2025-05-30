<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TiketPsb extends Model
{
    use HasFactory;
    protected $table = 'tiketpsb';
    protected $guarded = [];


    public function pelanggan()
{
    return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
}

public function mikrotik()
{
    return $this->belongsTo(Mikrotik::class, 'mikrotik_id');
}

}
