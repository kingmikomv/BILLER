<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NAS extends Model
{
    use HasFactory;
    protected $table = 'nas';
    protected $guarded = [];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function pppoeAccounts()
    {
        return $this->hasMany(PPPoEAccount::class);
    }
    public function vouchers()
    {
        return $this->hasMany(Voucher::class);
    }
}
