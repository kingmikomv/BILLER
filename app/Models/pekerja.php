<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class pekerja extends Model
{
    use HasFactory;
    protected $table = 'pekerja';
    protected $fillable = ['user_id', 'jenis_pekerja', 'jabatan', 'gaji', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
