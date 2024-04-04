<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengiriman extends Model
{
    protected $table ='pengiriman';
    use HasFactory; 
    protected $fillable=[
        'transaksi_id',
        'driver_id',
        'gudang_asal_id',
        'tanggal_pengiriman',
        'status_pengiriman',
        'keterangan',
    ];
}
