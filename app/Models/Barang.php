<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    protected $table = 'barang';
    use HasFactory;
    protected $fillable = [
        'nama',
        'harga',
        // 'stok',  
        'deskripsi',
        'tipe',
        'gambar',
       ];  public function barangKemasans()
       {
           return $this->hasMany(Barang_kemasan::class, 'barang_id');
       }
   
       public function tipeBarang()
       {
           return $this->belongsTo(Tipe::class, 'tipe');
       }
     
}
