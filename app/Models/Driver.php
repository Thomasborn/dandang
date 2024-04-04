<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    protected $table='driver';
    use HasFactory;
    protected $fillable = [
        'code',
        'name',
        'address',
        'user_id',
       ];

}
