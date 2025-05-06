<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\PaketPppoe;
use App\Models\ProfilPerusahaan;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;


class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'unique_member',
        'unique_id_pekerja',
        'username',
        'name',
        'email',
        'password',
        'phone',
        'role',
        'coin',


    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function hasRole($role)
    {
        return $this->role === $role;
    }


    public function findForPassport($identifier)
    {
        return $this->where('email', $identifier)
                    ->orWhere('username', $identifier)
                    ->first();
    }
    public function profilPerusahaan() {
        return $this->hasOne(ProfilPerusahaan::class, 'user_id');
    }

    public function usaha()
    {
        return $this->hasOne(Usaha::class);
    }
    public function mikrotik()
    {
        return $this->hasMany(Mikrotik::class, 'user_id', 'id');
    }
    public function pelanggan()
    {
    return $this->hasManyThrough(Pelanggan::class, Mikrotik::class, 'user_id', 'mikrotik_id');
    }
    public function messageTemplates()
    {
    return $this->hasMany(MessageTemplate::class);
    }


}
