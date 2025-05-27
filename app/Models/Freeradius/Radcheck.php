<?php

namespace App\Models\Freeradius;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Radcheck extends Model
{
    use HasFactory;
    protected $connection = 'freeradius'; // sesuaikan di config/database.php
    protected $table = 'radcheck';
    public $timestamps = false;

    protected $fillable = ['username', 'attribute', 'op', 'value'];
}
