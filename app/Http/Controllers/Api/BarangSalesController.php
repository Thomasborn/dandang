<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AllResource;
use App\Models\Barang;
use Illuminate\Http\Request;
use App\Models\Barang_sales; // Adjust the namespace accordingly
use App\Models\Sales;

class BarangSalesController extends Controller
{
    public function index()
    {
        // Retrieve all barang_sales entries
        $barangSales = barang_sales::with('barangKemasan.barang', 'barangKemasan.kemasan')->get();

        $result = [];
        
        foreach ($barangSales as $item) {
            $id = $item->barangKemasan->barang->id;
        
            // If the id already exists in the result array, update the values
            if (array_key_exists($id, $result)) {
                $result[$id]['stock_total'] += $item->jumlah_barang;
                $result[$id]['packaging'][] = [
                    "size" => (float)$item->barangKemasan->kemasan->ukuran,
                    "uom" => $item->barangKemasan->kemasan->uom,
                    "price" => (float)$item->barangKemasan->harga,
                    "stock" => $item->jumlah_barang,
                ];
            } else {
                // If the id doesn't exist, create a new entry
                $result[$id] = [
                    "id" => $id,
                    "name" => $item->barangKemasan->barang->nama,
                    "stock_total" => (float)$item->jumlah_barang,
                    "image" => $item->barangKemasan->barang->gambar,
                    "description" => $item->barangKemasan->barang->deskripsi,
                    "packaging" => [
                        [
                            "size" => (float)$item->barangKemasan->kemasan->ukuran,
                            "uom" => $item->barangKemasan->kemasan->uom,
                            "price" => (float)$item->barangKemasan->harga,
                            "stock" => (float)$item->jumlah_barang,
                        ],
                    ],
                ];
            }
        }
        
        $output = array_values($result); // Convert associative array to indexed array
        
   
        return new AllResource(200, 'List Data daftar_barang', $output);
    }

    public function store(Request $request)
    {
        // Validate the request data as needed

        // Create a new barang_sales entry
        $barangSales = barang_sales::create([
            'sales_id' => $request->sales_id,
            'barang_id' => $request->barang_id,
            'jumlah_barang' => $request->jumlah_barang,
            // Add other fields as needed
        ]);

        // Return a JSON response or use a resource if needed
        return new AllResource(200, 'List Data daftar_barang', $barangSales);
    }

    public function show($id)
{
    $sales = Sales::find($id);

    if ($sales && strpos(strtolower($sales->tipe), 'sales to') !== false) {
        $barangSales = Barang::with('barangKemasans.kemasan', 'tipe')->get();
        // return new AllResource(200, 'Data daftar_barang', $barangSales);
        $output = $this->process($barangSales);

        return new AllResource(200, 'Data daftar_barang', $output);
    
    } else {
        $barangSales = barang_sales::with('barangKemasan.barang', 'barangKemasan.kemasan')
            ->where('sales_id', $id)
            ->get();

        if ($barangSales->isEmpty()) {
            return new AllResource(404, 'Barang sales not found for the given sales_id', []);
        }
    }

    $output = $this->processBarangSales($barangSales);

    return new AllResource(200, 'Data daftar_barang', $output);
}

private function processBarangSales($barangSales)
{
    // return new AllResource(200, 'Data daftar_barang', $barangSales);
    // $id = $barangSales->barangKemasan == null ? $barangSales->barangKemasans[0]->barang_id : $barangSales->barangKemasan->barang->id;

    // if ($id == $barangSales->barangKemasans[0]->barang_id) {
    //     return array_values($this->process($barangSales)); // Replace this line with your actual processing logic
    // }

    $result = [];

    foreach ($barangSales as $item) {
     $id =   $item->barangKemasan->barang->id;
        if (array_key_exists($id, $result)) {
            $result[$id]['stock_total'] += $item->jumlah_barang;
            $result[$id]['packaging'][] = [
                "size" => $item->barangKemasan->ukuran,
                "uom" => $item->barangKemasan->kemasan->uom,
                "price" => $item->barangKemasan->harga,
                "stock" => (float)$item->jumlah_barang,
            ];
        } else {
            $result[$id] = [
                "id" => $id,
                "name" => $item->barangKemasan->barang->nama,
                "stock_total" => (float)$item->jumlah_barang,
                "description" => $item->barangKemasan->barang->deskripsi,
                "image" => $item->barangKemasan->barang->gambar,
                "packaging" => [
                    [
                        "size" => (float)$item->barangKemasan->kemasan->ukuran,
                        "uom" => $item->barangKemasan->kemasan->uom,
                        "price" => (float)$item->barangKemasan->harga,
                        "stock" => (float)$item->jumlah_barang,
                    ],
                ],
            ];
        }
    
    
    }

    return array_values($result);
}
private function process($barangData)
{
    $initialArray = $barangData->toArray();
    $transformedData = [];
        
    foreach ($initialArray as $barang) {
        $totalStok = 0; 
    
        foreach ($barang['barang_kemasans'] as $barangKemasan) {
            $totalStok += $barangKemasan['stok']; 
        }
    
        $transformedData[] = [
            'id' => $barang['id'],
            'name' => $barang['nama'],
            'description' => $barang['deskripsi'],
            'image' => $barang['gambar'],
            'total_stok' => $totalStok,
            'packaging' => [],
        ];
    
        foreach ($barang['barang_kemasans'] as $barangKemasan) {
            $transformedData[count($transformedData) - 1]['packaging'][] = [
                'size' => (float) $barangKemasan['kemasan']['ukuran'],
                'uom' => $barangKemasan['kemasan']['uom'],
                'price' => (float) $barangKemasan['harga'],
                'stok' => (float) $barangKemasan['stok'],
            ];
        }
    }
    
   
    if (count($transformedData) === 1) {
        $transformedData = $transformedData[0];
    }
    return $transformedData;
}



}
