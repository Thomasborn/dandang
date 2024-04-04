<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'category_id',
        'product_name',
        'product_code',
        'product_barcode_symbology',
        'product_quantity',
        'product_cost',
        'product_price',
        'product_unit',
        'product_size',
        'image',
        'product_stock_alert',
        'product_order_tax',
        'product_tax_type',
        'product_note',
    ];

    // You may also define relationships, such as a belongsTo for the category:
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function saleDetails()
    {
        return $this->hasMany(SalesDetail::class, 'product_id');
    }
    public function productShippingDoc()
    {
        return $this->hasMany(ProductShippingDoc::class, 'product_id');
    }
}
