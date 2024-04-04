<?php
/// app/Http/Resources/SuratJalanResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SuratJalanResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'sales_id' => $this->sales_id,
            'tanggal' => $this->tanggal,
            'barang_surat_jalan' => $this->transformBarangSuratJalan($this->barang_surat_jalan),
        ];
    }

    protected function transformBarangSuratJalan($barangSuratJalan)
    {
        // Check if $barangSuratJalan is not null before using map
        return $barangSuratJalan ? $barangSuratJalan->map(function ($item) {
            return [
                'id' => $item->id,
                'surat_jalan_id' => $item->surat_jalan_id,
                'barang_id' => $item->barang_id,
                'jumlah_barang' => $item->jumlah_barang,
                'barang' => [
                    'id' => $item->barang->id,
                    'harga' => $item->barang->harga,
                    'uom' => $item->barang->uom,
                    'nama' => $item->barang->nama,
                ],
            ];
        }) : null;
    }
}
