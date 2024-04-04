<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductSaler extends Model
{
    protected $table = 'product_saler';
    use HasFactory; protected $fillable = [
        'saler_code',
        'product_id',
        'product_quantity',
        'first_stock',
        'sold',
    ];
    
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
    public function saler()
    {
        return $this->belongsTo(Saler::class, 'saler_code', 'Kode');
    }
}
