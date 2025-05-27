<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
   
     use HasFactory;

    protected $fillable = [
        'hotspot_profile_id',
        'username',
        'password',
        'price',
        'uptime',
        'status',
        'expired_at',
    ];

    protected $dates = ['expired_at'];

    // Relasi ke HotspotProfile
    public function hotspotProfile()
    {
        return $this->belongsTo(HotspotProfile::class);
    }
}
