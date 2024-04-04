<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductShippingDoc extends Model
{
    use HasFactory;
    protected $fillable = [
        'shipping_doc_id',
        'product_id',
        'quantity',
    ];

    // You may also define relationships if necessary
    public function shipping()
    {
        return $this->belongsTo(ShippingDoc::class, 'shipping_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
