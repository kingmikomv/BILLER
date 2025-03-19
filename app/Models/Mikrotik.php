<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mikrotik extends Model
{
    use HasFactory;
    protected $table = 'mikrotik';
    protected $primaryKey = 'id';
    protected $guarded = [];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function paketPppoe()
    {
        return $this->hasMany(PaketPppoe::class, 'mikrotik_id', 'id');
    }

    function pelanggan()
    {
        return $this->hasMany(Pelanggan::class, 'mikrotik_id');
    }
    public function olt()
    {
        return $this->hasMany(OLT::class, 'mikrotik_id', 'id');
    }
    public function undian()
    {
        return $this->hasMany(Undian::class, 'mikrotik_id');
    }
}
