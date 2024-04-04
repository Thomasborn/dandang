<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang_kemasan extends Model
{
    protected $table = 'barang_kemasan';
    use HasFactory;
    protected $fillable = [
        'barang_id',
        'kemasan_id',
        'stok',
        'harga',
       ]; public function barang()
       {
           return $this->belongsTo(Barang::class, 'barang_id');
       }
   
       public function kemasan()
       {
           return $this->belongsTo(Kemasan::class, 'kemasan_id');
       }
       public function transaksiDetail()
       {
           return $this->hasMany(Transaksi_detail::class, 'barang_id');
       }
}
