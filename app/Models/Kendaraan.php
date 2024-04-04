<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kendaraan extends Model
{
    protected $table ='kendaraan'; 
    use HasFactory;
    protected $fillable = [
        'depo_id',
        'jenis',
        'nama',
        'nomor_polisi',
       ];
}
