<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingDoc extends Model
{
    protected $table='shipping_doc';
    use HasFactory;
    protected $fillable = [
        'saler_code',
        'date'
       ];
       public function saler()
       {
        return $this->belongsTo(Saler::class, 'saler_code', 'Kode');
       }
       public function productShippingDoc()
       {
           return $this->hasMany(ProductShippingDoc::class);
       }
}
