<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bonus_transaksi extends Model
{
    protected $table = 'bonus_transaksi';
    use HasFactory;
    protected $fillable = [
        'transaksi_id',
        'barang_bonus_id',
        'jumlah_barang_bonus',
        
       ];
}
