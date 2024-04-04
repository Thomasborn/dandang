<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table ='customers';
    use HasFactory;
    protected $fillable = [
        'customer_name',
        'customer_email',
        'city',
        'address',
        // 'address',
       ];
       public function transactions()
       {
           return $this->hasMany(Transaksi::class, 'customer_id');
       }
}
