<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfilPerusahaan extends Model {
    use HasFactory;

    protected $table = 'profil_perusahaan';

    protected $fillable = [
        'user_id', 'nama_perusahaan', 'brand', 'nomor_izin_isp', 'nomor_izin_jartaplok',
        'npwp', 'alamat', 'website', 'email_perusahaan', 'nomor_telepon', 'nomor_whatsapp',
        'nama_owner', 'nama_finance', 'nomor_telepon_finance'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
