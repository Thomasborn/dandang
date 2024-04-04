<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang_sales extends Model
{
    use HasFactory;protected $fillable = ['sales_id', 'barang_id', 'jumlah_barang'];

    public function sales()
    {
        return $this->belongsTo(Sales::class);
    }

    public function barangKemasan()
    {
        return $this->belongsTo(Barang_kemasan::class, 'barang_id');
    }
}
