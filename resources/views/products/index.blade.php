<!-- resources/views/products/index.blade.php -->

@extends('layouts.app')

@section('title', 'Product List')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <h1 class="display-4">Daftar Barang</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 mb-4">
            <!-- Add a link or button to navigate to the create view -->
            <a href="{{ route('products.create') }}" class="btn btn-primary">Tambah Produk</a>
        </div>
    </div>

    <div class="row">
        @foreach ($products as $product)
            <div class="col-md-4 mb-4">
                <div class="card h-100 custom-card"> {{-- Add the custom-card class --}}
                    {{-- Fix the image source syntax --}}
                    <img src="{{ 'https://omahit.my.id/' . $product['image'] }}" class="card-img-top" alt="{{ $product['name'] }}">
                    <div class="card-body">
                        <h5 class="card-title custom-card-title">{{ ucwords($product['name']) }}</h5> {{-- Add the custom-card-title class --}}
                        <p class="card-text">{{ $product['description'] }}</p>
                        <p>Total Stock: {{ $product['total_stok'] }}</p>

                        <h6>Packaging:</h6>
                        @foreach ($product['packaging'] as $package)
                            <p>Size: {{ $package['size'] }} {{ $package['uom'] }}, Price: {{ $package['price'] }}, Stock: {{ $package['stok'] }}</p>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection

<style>
    .custom-card {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 5px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .custom-card-title {
        color: #333;
        font-size: 18px;
        font-weight: bold;
    }
</style>
