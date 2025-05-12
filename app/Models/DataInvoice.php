<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataInvoice extends Model
{
    use HasFactory;
    protected $table = "data_invoices";
    protected $guarded = [];
    // app/Models/DataInvoice.php

public function pelanggan()
{
    return $this->belongsTo(Pelanggan::class);
}

public function tagihan()
{
    return $this->belongsTo(Tagihan::class);
}


}
