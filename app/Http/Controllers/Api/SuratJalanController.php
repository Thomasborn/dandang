<?php

namespace App\Http\Controllers\Api;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\AllResource;
use App\Models\barang;
use App\Models\barang_kemasan;
use App\Models\barang_sales;
use App\Models\barang_surat_jalan;
use Illuminate\Http\Request;
use App\Models\surat_jalan;
//import Facade "Validator"
use Illuminate\Support\Facades\Validator;

class SuratJalanController extends Controller
{
    public function index()
    {
        //get all suratJalan
        $suratJalan = Surat_jalan::with(['sales','barangSuratJalan.barang.kemasan'])->get();


        //return collection of suratJalan as a resource
        return new AllResource(true, 'List Data suratJalan', $suratJalan);
    }
     /**
     * store
     *
     * @param  mixed $request
     * @return void
     */
    public function store(Request $request)
    {
                    // Validate the request for surat_jalan
            $validatorSuratJalan = Validator::make($request->all(), [
                'sales_id' => 'required|exists:sales,id',
                'barang_detail' => 'required|array', // Ensure that barang_details is an array
                'barang_detail.*.barang_id' => 'required|exists:barang_kemasan,id',
                'barang_detail.*.jumlah_barang' => 'required|integer|min:0',
            ]);

            // Check if validation fails for surat_jalan
            if ($validatorSuratJalan->fails()) {
                return response()->json($validatorSuratJalan->errors(), 422);
            }
            // $barangData = 
            //             return response()->json($barangData, 200);

            try {
                // Start a database transaction
                 // Start a database transaction
                DB::beginTransaction();

                // Create a new surat_jalan
                $suratJalan = surat_jalan::create([
                    'sales_id' => $request->sales_id,
                    'tanggal' => date('Y-m-d')
                ]);

                $barangSuratJalanArray = [];

                // Loop through each barang detail and create or update barang_surat_jalan entries
                foreach ($request->barang_detail as $barangDetail) {
                    $barangSuratJalanItem = barang_surat_jalan::updateOrCreate(
                        [
                            'surat_jalan_id' => $suratJalan->id,
                            'barang_id' => $barangDetail['barang_id'],
                        ],
                        [
                            'jumlah_barang' => $barangDetail['jumlah_barang'],
                        ]
                    );
                    // Increment the jumlah_barang field in barang_sales
                    $barang_sales=barang_sales::updateOrCreate(
                        [
                            'sales_id' => $request->sales_id,
                            'barang_id' => $barangDetail['barang_id'],
                        ],
                        [
                            'jumlah_barang' => DB::raw('jumlah_barang + ' . $barangDetail['jumlah_barang']),
                        ]
                    );
                    
                    $barangData = barang_kemasan::find($barangSuratJalanItem->barang_id);

                    // // Check if the data is found
                    // if ($barangData) {
                    //     // Return the JSON response
                    //     return response()->json($barangData, 200);
                    // } else {
                    //     // Return an error JSON response
                    //     return response()->json(['message' => 'Data not found'], 404);
                    // }
                    

                    if ($barangData) {
                        $barangData->update([
                            'stok' => $barangData->stok - $barangDetail['jumlah_barang']
                        ]);
                    } else {
                        // Roll back the transaction in case of an error
                        DB::rollBack();

                        // Handle the case where the barang data is not found
                        return response()->json(['message' => 'Barang is not found'], 404);
                }

                    // Append the $barangSuratJalanItem to the array
                    $barangSuratJalanArray[] = $barangSuratJalanItem;
                }

                // Commit the transaction if everything is successful
                DB::commit();

                // Return a success response
                return new AllResource(true, 'Data suratJalan Berhasil Diubah!', [
                    'suratJalan' => $suratJalan,
                    'barang_surat_jalan' => $barangSuratJalanArray,
                    // 'barang_sales' => $barang_sales,
                    //opsional total barang yang ditambahkan diperlihatkan dengan jumlah bartang yang telah dibawea oleh sales
                ]);

            } catch (\Exception $e) {
                // Roll back the transaction in case of any exception
                DB::rollBack();

                // Handle the exception (log, report, etc.)
                             return response()->json(['message' => $e], 404);

                // return new AllResource(false, ', null, 500);
            }
        }
                    
     public function show($id)
                {
                    //find suratJalan by ID
                    $suratJalan = Surat_jalan::with(['barangSuratJalan.barang.barang.tipeBarang', 'barangSuratJalan.barang.kemasan'])
                    ->where('sales_id', $id)
                    ->get();
                    $result = [];

foreach ($suratJalan as $surat) {
    $transformedData = [
        'id' => $surat->barangSuratJalan[0]->id,
        // Assuming the name, type, description, etc., are properties of the 'tipeBarang' relationship
        'name' => $surat->barangSuratJalan[0]->barang->barang->nama,
        'type' => $surat->barangSuratJalan[0]->barang->barang->tipeBarang->nama,
        // ... other properties from 'tipeBarang'
        'description' => $surat->barangSuratJalan[0]->barang->barang->deskripsi,
        'image' => $surat->barangSuratJalan[0]->barang->barang->gambar,
        // Assuming 'kemasan' is a property of the 'barang' relationship
        'size' => $surat->barangSuratJalan[0]->barang->kemasan->ukuran,
        'uom' => $surat->barangSuratJalan[0]->barang->kemasan->uom,

        // Properties directly from 'barang' relationship
        'price' => $surat->barangSuratJalan[0]->barang->harga,
        'stock' => $surat->barangSuratJalan[0]->barang->stok,
    ];

    $result[] = $transformedData;
}

// Now $result contains the transformed data in the desired format

                    return new AllResource(true, 'Detail Data suratJalan!', $result);
                
                
        //             // Check if the surat_jalan record is not found
        //             if (!$suratJalan) {
        //                 return response()->json(['error' => 'Surat Jalan not found'], 404);
        //             }
                    
        //             // Check if the relationship is loaded and has data
        //             // if (!$suratJalan->relationLoaded('barangSuratJalan') || $suratJalan->barangSuratJalan->isEmpty()) {
        //             //     return response()->json(['error' => 'No related Barang Surat Jalan found'], 404);
        //             // }
        //             $transformedData = [
        //                 "id" => $suratJalan->id,
        //                 "name" => $suratJalan->barangSuratJalan[0]->barang->barang->nama,
        //                 "type" => $suratJalan->barangSuratJalan[0]->barang->barang->tipeBarang->nama,
        //                 "description" => $suratJalan->barangSuratJalan[0]->barang->barang->deskripsi,
        //                 "image" => $suratJalan->barangSuratJalan[0]->barang->barang->gambar,
        //                 "size" => $suratJalan->barangSuratJalan[0]->barang->barang->size, // Adjust the attribute name as needed
        //                 "uom" => $suratJalan->barangSuratJalan[0]->barang->barang->uom, // Adjust the attribute name as needed
        //                 "price" => $suratJalan->barangSuratJalan[0]->barang->harga,
        //                 "stock" => $suratJalan->barangSuratJalan[0]->barang->stok,
        //             ];
        //             // return new AllResource(true, 'Detail Data suratJalan!', $suratJalan->barangSuratJalan[0]);
                   
                    
        //             // // Extract the specific columns from the related data
        //             // $show = $suratJalan->barangSuratJalan->map(function ($item) {
        //             //     return [
        //             //         'id' => $item['id'],
        //             //         'surat_jalan_id' => $item['surat_jalan_id'],
        //             //         'barang_id' => $item['barang_id'],
        //             //         'jumlah_barang' => $item['jumlah_barang'],
        //             //         'harga' => $item['barang']['harga'],
        //             //         'uom' => $item['barang']['uom'],
        //             //         'nama' => $item['barang']['nama'],
        //             //         'created_at' => $item['created_at'],
        //             //         'updated_at' => $item['updated_at'],
        //             //     ];
        //             // });
                    
        //             // return response()->json(['data' => $show]);
                    
                            
        //                     // $transformedSuratJalan = [
        //                     //     'id' => $suratJalan->id,
        //                     //     'sales_id' => $suratJalan->sales_id,
        //                     //     'tanggal' => $suratJalan->tanggal,
        //                     //     'created_at' => $suratJalan->created_at,
        //                     //     'updated_at' => $suratJalan->updated_at,
        //                     //     'barangSuratJalan' => $suratJalan->barangSuratJalan->map(function ($item) {
        //                     //         return [
        //                     //             'id' => $item['id'],
        //                     //             'surat_jalan_id' => $item['surat_jalan_id'],
        //                     //             'barang_id' => $item['barang_id'],
        //                     //             'jumlah_barang' => $item['jumlah_barang'],
        //                     //             'harga' => $item['barang']['harga'],
        //                     //             'uom' => $item['barang']['uom'],
        //                     //             'nama' => $item['barang']['nama'],
        //                     //             'created_at' => $item['created_at'],
        //                     //             'updated_at' => $item['updated_at'],
        //                     //         ];
        //                     //     }),
        //                     // ];
                            
        //                     // return response()->json(['data' => $transformedSuratJalan]);
        // //return single suratJalan as a resource
        // return new AllResource(true, 'Detail Data suratJalan!', $transformedData);
    }

    /**
     * update
     *
     * @param  mixed $request
     * @param  mixed $suratJalan
     * @return void
     */
    public function update(Request $request, $id)
    {
     // Validate the request for surat_jalan
$validatorSuratJalan = Validator::make($request->all(), [
    'sales_id' => 'required|exists:sales,id',
    'tanggal' => 'required|date',
    'barang_detail' => 'required|array', // Ensure that barang_details is an array
    'barang_detail.*.barang_id' => 'required|exists:barang,id',
    'barang_detail.*.jumlah_barang' => 'required|integer|min:0',
]);

// Check if validation fails for surat_jalan
if ($validatorSuratJalan->fails()) {
    return response()->json($validatorSuratJalan->errors(), 422);
}

// Create a new surat_jalan
$suratJalan = surat_jalan::create([
    'sales_id' => $request->sales_id,
    'tanggal' => $request->tanggal,
]);

// Loop through each barang detail and create barang_surat_jalan entries
foreach ($request->barang_detail as $barangDetail) {
    barang_surat_jalan::create([
        'surat_jalan_id' => $suratJalan->id,
        'barang_id' => $barangDetail['barang_id'],
        'jumlah_barang' => $barangDetail['jumlah_barang'],
    ]);
}


        //return response
        return new AllResource(true, 'Data suratJalan Berhasil Diubah!', [
            'suratJalan' => $suratJalan,
            'barang_surat_jalan' => $suratJalan->barangSuratJalan,
        ]);
    }

    /**
     * destroy
     *
     * @param  mixed $suratJalan
     * @return void
     */
    public function destroy($id)
    {

        //find suratJalan by ID
        $suratJalan = surat_jalan::find($id);

      
        //delete suratJalan
        $suratJalan->delete();

        //return response
        return new AllResource(true, 'Data suratJalan Berhasil Dihapus!', null);
    }
    //
}
