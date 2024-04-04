<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Surat_jalan extends Model
{
    protected $table='surat_jalan';
    use HasFactory;
    protected $fillable = [
        'sales_id',
        'tanggal'
       ];
       public function sales()
       {
           return $this->belongsTo(Sales::class);
       }
       public function barangSuratJalan()
       {
           return $this->hasMany(Barang_surat_jalan::class);
       }
}
