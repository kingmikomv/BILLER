<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OLT extends Model
{
    use HasFactory;
    protected $table = 'olt';
    protected $guarded = [];

    public function mikrotik()
    {
        return $this->belongsTo(Mikrotik::class, 'mikrotik_id', 'id');
    }
}
