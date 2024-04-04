<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Contracts\Role;
use Spatie\Permission\Traits\HasRoles;
class User extends Authenticatable

{
    use HasApiTokens, HasFactory, Notifiable,HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    use HasApiTokens, Notifiable;
    protected $fillable = [
       
            'name',
            'is_active',
            'password',
            'email',  
            'role_id',  
            'remember_token',
            'contact',
            // 'depo_code',
            // 'saler_code',
            // 'driver_id',
            // 'master_id',
            'role_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        // 'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
    public function hasRole($role)
{
    return $this->roles()->where('name', $role)->exists();
}public function depo()
{
    return $this->hasOne(Depo::class);
}

public function sales()
{
    return $this->hasOne(Saler::class);
}

public function driver()
{
    return $this->hasOne(Driver::class);
}

public function superAdmin()
{
    return $this->hasOne(Master::class);
}

public function role()
{
    return $this->hasOne(Role::class);
}
public function userWithRole()
{
    return $this->belongsTo(Roles::class, 'role_id');
}
public function customersWithTransactions()
{
    return $this->hasManyThrough(Customer::class, Transaksi::class, 'sales_id', 'id', 'id', 'customer_id');
}
}
