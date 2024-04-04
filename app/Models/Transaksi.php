<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $table='transaksi';
    use HasFactory;
    protected $fillable = ['driver_id', 'jatuh_tempo','sales_id', 'total_harga', 'tanggal_transaksi', 'metode_pembayaran', 'status_transaksi', 'tipe_transaksi', 'ppn', 'gudang_id', 'customer_id'];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function sales()
    {
        return $this->belongsTo(Sales::class);
    }

    public function gudang()
    {
        return $this->belongsTo(Gudang::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function transaksiDetail()
    {
        return $this->hasMany(Transaksi_detail::class);
    }
    public function bonusTransaksi()
    {
        return $this->hasMany(Bonus_transaksi::class);
    }

    public function diskon()
    {
        return $this->hasOne(Diskon::class);
    }
}
