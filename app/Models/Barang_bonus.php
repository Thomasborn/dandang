<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang_bonus extends Model
{
    protected $table ='barang_bonus';
    use HasFactory;
    protected $fillable = [
        'barang_bonus_id',
        'jumlah_barang_bonus',
       ];
}
