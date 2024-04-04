<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class users extends Model
{
    use HasFactory;
    protected $fillable = [
        'username',
        'password',
        'email',  
        // 'role',
        'nomor_telepon',
       ];
       public function hasRole($role)
       {
           return $this->role === $role;
       }
}
