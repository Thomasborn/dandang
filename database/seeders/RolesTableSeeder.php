<?php
// database/seeders/RolesTableSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesTableSeeder extends Seeder
{
    public function run()
    {
        // Create roles
        Role::create(['name' => 'depo']);
        Role::create(['name' => 'sales TO']);
        Role::create(['name' => 'sales mobilris']);
        Role::create(['name' => 'sales motoris']);
        Role::create(['name' => 'driver']);
        // Add more roles as needed
    }
}

// }
