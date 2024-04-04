<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_saler', function (Blueprint $table) {
            $table->id();
            $table->string('saler_code', 50)->collation('utf8mb4_general_ci');
            $table->unsignedBigInteger('product_id');
            $table->integer('product_quantity');
            $table->timestamps();

            // Foreign keys
            $table->foreign('saler_code')->references('Kode')->on('saler')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_saler');
    }
};
