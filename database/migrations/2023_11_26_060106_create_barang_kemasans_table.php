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
        Schema::create('barang_kemasan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('barang_id')->index();
            $table->unsignedBigInteger('kemasan_id')->index();
            $table->integer('stok');
            $table->foreign('barang_id')->references('id')->on('barang');
            $table->foreign('kemasan_id')->references('id')->on('kemasan');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('barang_kemasan');
    }
};
