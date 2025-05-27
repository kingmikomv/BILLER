<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotspotProfile extends Model
{
   use HasFactory;

    protected $fillable = [
    'user_id', 'name', 'price', 'reseller_price',
    'rate_up', 'rate_down', 'uptime', 'validity', 'groupname'
];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function vouchers()
{
    return $this->hasMany(Voucher::class);
}
}
