<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $table= 'purchases';
    use HasFactory;
    protected $casts = [
        'tax_percentage' => 'float',
        'tax_amount' => 'float',
        'discount_percentage' => 'float',
        'discount_amount' => 'float',
        'shipping_amount' => 'float',
        'total_amount' => 'float',
        'paid_amount' => 'float',
        'due_amount' => 'float',
        'sub_total' => 'float',
        // Add more attributes that should remain numeric
    ];
    protected $fillable = [
        'date',
        'reference',
        'customer_id',
        'customer_name',
        'tax_percentage',
        'kode_depo',
        'kode_salesman',
        'tax_amount',
        'discount_percentage',
        'discount_amount',
        'shipping_amount',
        'total_amount',
        'paid_amount',
        'due_amount',
        'sub_total',
        'status',
        'due',
        'payment_status',
        'payment_method',
        'note',
    ];
     
}
