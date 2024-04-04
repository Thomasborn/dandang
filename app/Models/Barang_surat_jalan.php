<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang_surat_jalan extends Model
{
    protected $table='barang_surat_jalan';
    use HasFactory;
    protected $fillable = ['surat_jalan_id', 'barang_id', 'jumlah_barang'];

    public function suratJalan()
    {
        return $this->belongsTo(Surat_jalan::class);
    }

    public function barang()
    {
        return $this->belongsTo(Barang_kemasan::class);
    }
}
