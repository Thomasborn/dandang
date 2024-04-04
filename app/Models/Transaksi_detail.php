<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi_detail extends Model
{
    protected $table ='transaksi_detail';
    use HasFactory;
    protected $fillable = ['transaksi_id', 'barang_id', 'jumlah_barang', 'harga_barang'];

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class);
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }
}
