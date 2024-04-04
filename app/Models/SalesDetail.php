<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesDetail extends Model
{
    protected $table = "sale_details";
    protected $casts = [
        'quantity' => 'float',
        'price' => 'float',
        'unit_price' => 'float',
        'sub_total' => 'float',
        'product_discount_amount' => 'float',
        'dpp' => 'float',
        'product_tax_amount' => 'float',
        // ... other attributes
    ];
    
    use HasFactory;
    protected $fillable = [
        'sale_id',
        'product_id',
        'product_name',
        'product_code',
        'quantity',
        'price',
        'unit_price',
        'sub_total',
        'product_discount_amount',
        'product_discount_type',
        'dpp',
        'product_tax_amount',
        // ... other fields
    ];

    /**
     * Get the sale that owns the sale detail.
     */
    public function sale()
    {
        return $this->belongsTo(Sales::class, 'sale_id');
    }

    /**
     * Get the product that owns the sale detail.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
