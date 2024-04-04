<!-- resources/views/products/create.blade.php -->

@extends('layouts.app')

@section('title', 'Tambah Produk')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <h1 class="display-4">Tambah Produk</h1>
           
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
        @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
            <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label">Nama Produk</label>
                    <input type="text" class="form-control" id="name" name="nama" value="{{ old('nama') }}" required>
                    @if($errors->has('nama'))
                        <div class="text-danger">{{ $errors->first('nama') }}</div>
                    @endif
                </div>

                <div class="mb-3">
                    <label for="harga" class="form-label">Harga</label>
                    <input type="number" class="form-control" id="harga" name="harga" value="{{ old('harga') }}" required>
                    @if($errors->has('harga'))
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="stok" class="form-label">Stok</label>
                    <input type="number" class="form-control" id="stok" name="stok" value="{{ old('stok') }}" required>
                    @error('stok')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="uom" class="form-label">Unit of Measure (UOM)</label>
                    <input type="text" class="form-control" id="uom" name="uom" value="{{ old('uom') }}" required>
                    @error('uom')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Add ukuran field -->
                <div class="mb-3">
                    <label for="ukuran" class="form-label">Ukuran</label>
                    <input type="text" class="form-control" id="ukuran" name="ukuran" value="{{ old('ukuran') }}" required>
                    @error('ukuran')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="tipe" class="form-label">Tipe Produk</label>
                    <select class="form-select" id="tipe" name="tipe" required>
                        <option value="" selected disabled>Pilih Tipe Produk</option>
                        @foreach($tipe as $t)
                            <option value="{{ $t->id }}" {{ old('tipe') == $t->id ? 'selected' : '' }}>{{ $t->nama }}</option>
                        @endforeach
                    </select>
                    @error('tipe')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="gambar" class="form-label">Gambar Produk</label>
                    <input type="file" class="form-control" id="gambar" name="gambar" onchange="previewImage()">
                    <img id="imagePreview" src="#" alt="Preview" style="max-width: 100%; display: none; margin-top: 10px;">
                    @error('gambar')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="deskripsi" class="form-label">Deskripsi</label>
                    <textarea class="form-control" id="deskripsi" name="deskripsi" required>{{ old('deskripsi') }}</textarea>
                    @error('deskripsi')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Tambah Produk</button>
            </form>
        </div>
    </div>

    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 20px;
        }

        .row {
            margin-bottom: 20px;
        }

        form {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
    </style>

    <script>
        function previewImage() {
            var input = document.getElementById('gambar');
            var preview = document.getElementById('imagePreview');
            var file = input.files[0];
            var reader = new FileReader();

            reader.onloadend = function () {
                preview.src = reader.result;
                preview.style.display = 'block';
            };

            if (file) {
                reader.readAsDataURL(file);
            } else {
                preview.src = '';
                preview.style.display = 'none';
            }
        }
    </script>
@endsection
