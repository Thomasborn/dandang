<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Saler extends Model
{
    protected $table='saler';
    use HasFactory;


    protected $fillable = [
     'Kode',
     'Nama', 
     'alamat', 
     'user_id'];

     public function user()
     {
         return $this->belongsTo(User::class)->with('userWithRole');
     }
}
