# Dandang App

Dandang App adalah aplikasi berbasis Laravel versi 10 yang dirancang untuk mempermudah pengelolaan izin dan otentikasi. Dengan menggunakan beberapa paket eksternal, aplikasi ini menyediakan solusi yang handal untuk manajemen izin pengguna.

## Persyaratan Sistem
Pastikan sistem Anda memenuhi persyaratan berikut sebelum menginstal projek:

- PHP versi 8 atau lebih baru
- Composer (https://getcomposer.org/)

## Langkah Instalasi

1. **Clone Repositori**

    ```bash
    git clone https://github.com/Thomasborn/dandang-app.git
    ```

2. **Pindah ke Direktori Projek**

    ```bash
    cd dandang-app
    ```

3. **Install Dependensi PHP**

    ```bash
    composer install
    ```

4. **Konfigurasi .env**

    Duplikat file `.env.example` dan simpan sebagai `.env`. Sesuaikan konfigurasi database dan pengaturan lainnya sesuai kebutuhan Anda.

5. **Generate App Key**

    ```bash
    php artisan key:generate
    ```

6. **Migrasi Database**

    ```bash
    php artisan migrate
    ```

7. **Install Dependensi Laravel**

    ```bash
    composer require laravel/sanctum "^3.3" laravel/tinker "^2.8" spatie/laravel-permission "^6.1"
    ```

8. **Publish Konfigurasi dan Migrasi Paket Eksternal**

    ```bash
    php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
    php artisan migrate
    ```

9. **Jalankan Aplikasi**

    ```bash
    php artisan serve
    ```

    Akses aplikasi melalui browser pada [http://localhost:8000](http://localhost:8000).

## Penjelasan Stack
- Laravel v10: Versi terbaru dari framework PHP Laravel.
- Laravel Sanctum (^3.3): Paket untuk manajemen otentikasi API menggunakan token.
- Laravel Tinker (^2.8): Console REPL yang kuat untuk Laravel.
- Spatie Laravel Permission (^6.1): Paket untuk manajemen izin dan peran pengguna.

## Catatan Tambahan

- Pastikan Anda telah mengatur koneksi database pada file `.env`.
- Pastikan direktori `storage` dan `bootstrap/cache` dapat ditulis oleh server web.
- Lihat dokumentasi Laravel resmi (https://laravel.com/docs) untuk informasi lebih lanjut.

Terima kasih telah menggunakan Dandang App!
