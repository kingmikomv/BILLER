<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappSession extends Model
{
    use HasFactory;
    protected $table = 'whatsapp_sessions';
        protected $fillable = ['session_id', 'admin_number'];

}
