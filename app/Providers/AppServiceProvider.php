<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Validator::extend('unique_nama_tipe_combination', function ($attribute, $value, $parameters, $validator) {
            // Parameters: $attribute, $value, $parameters, $validator

            // Check if the combination of "nama" and "tipe" already exists in the "barang" table
            $exists = \App\Models\Barang::where('nama', $value)
                ->where('tipe', $validator->getData()['tipe'])
                ->exists();

            // Return true if it doesn't exist, meaning the combination is unique
            return !$exists;
            
        });
        
    }
}
