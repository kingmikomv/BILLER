<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageTemplate extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'name', 'content'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
