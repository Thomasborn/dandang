<?php

namespace App\Http\Controllers\Api;
use Illuminate\Support\Facades\Storage;

use App\Http\Controllers\Controller;
use App\Http\Resources\AllResource;
use Illuminate\Http\Request;
use App\Models\Barang;
use App\Models\Barang_kemasan;
use App\Models\Kemasan;
use Illuminate\Support\Facades\DB;
//import Facade "Validator"
use Illuminate\Support\Facades\Validator;

class BarangController extends Controller
{
    public function index()
    {
  
        $barangKemasan = Barang_kemasan::with(['barang.tipeBarang', 'kemasan'])->get();
        $transformedData = $barangKemasan->map(function ($barangKemasan) {
            return [
                'id' => $barangKemasan->id,
                'name' => $barangKemasan->barang->nama,
                'type' => $barangKemasan->barang->tipeBarang->nama,
                'description' => $barangKemasan->barang->deskripsi,
                'image' => $barangKemasan->barang->gambar,
                'size' => $barangKemasan->kemasan->ukuran,
                'uom' => $barangKemasan->kemasan->uom,
                'price' => $barangKemasan->harga,
                'stock' => $barangKemasan->stok,
            ];
        });
        
        // Return the transformed data
        return new AllResource(true, 'List Data BarangKemasan', $transformedData);
        // $barangData = Barang::with('barangKemasans.kemasan','barangKemasans.transaksiDetail', 'tipe')->get();

        // $initialArray = $barangData->toArray();
     
        // $transformedData = [];
        
        // foreach ($initialArray as $barang) {
        //     $totalStok = 0; 
        
        //     foreach ($barang['barang_kemasans'] as $barangKemasan) {
        //         $totalStok += $barangKemasan['stok']; 
        //     }
        
        //     $transformedData[] = [
        //         'id' => $barang['id'],
        //         'name' => $barang['nama'],
        //         'description' => $barang['deskripsi'],
        //         'image' => $barang['gambar'],
        //         'total_stok' => $totalStok,
        //         'packaging' => [],
        //     ];
        
        //     foreach ($barang['barang_kemasans'] as $barangKemasan) {
        //         $transformedData[count($transformedData) - 1]['packaging'][] = [
        //             'size' => (float) $barangKemasan['kemasan']['ukuran'],
        //             'uom' => $barangKemasan['kemasan']['uom'],
        //             'price' => (float) $barangKemasan['harga'],
        //             'stok' => (float) $barangKemasan['stok'],
        //         ];
        //     }
        // }
        
       
        // if (count($transformedData) === 1) {
        //     $transformedData = $transformedData[0];
        // }
        

        // return new AllResource(200, 'List Data daftar_barang', $barangData);
        
        
    }
     /**
     * store
     *
     * @param  mixed $request
     * @return void
     */
    public function store(Request $request)
    {
        // $validator = Validator::make($request->all(), [
        //     'nama'  => 'required',
        //     'harga' => 'required|numeric|min:0',
        //     'stok'  => 'required|integer|min:0',
        //     'uom'   => 'required',
        //     'tipe'  => 'required',
        //     'gambar' => 'image|mimes:jpeg,png,jpg,gif|max:2048', // Assuming you allow only image gambars (jpeg, png, jpg, gif) with a maximum size of 2048 KB.
        // ]);
        
        // if ($validator->fails()) {
        //     return response()->json($validator->errors(), 422);
        // }
        
        // // Assuming you have an 'upload' folder in your public directory for storing images.
        // $fileName = uniqid() . '_' . $request->gambar->getClientOriginalName();
        // $gambarPath =$request->gambar->storeAs('public/barang', $fileName);
          
        // $daftar_barang = Barang::create([
        //     'nama'   => $request->nama,
        //     'harga'  => $request->harga,
        //     'stok'   => $request->stok,
        //     'uom'    => $request->uom,
        //     'tipe'   => $request->tipe,
        //     'gambar' => $gambarPath, // Store the image path in the 'gambar' column.
        // ]);
        // $request->validate([
        //     'gambar' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        // ]);
    
        // if ($request->hasFile('gambar')) {
        //     $gambar = $request->file('gambar');
        //     $gambarName = time() . '.' . $gambar->getClientOriginalExtension();
        //     $path = 'barangs/' . $gambarName;
    
        //     try {
        //         // Move the uploaded file to the desired location
        //         $gambar->move(public_path('storage/barang'), $gambarName);
    
        //         return response()->json(['message' => 'Gambar uploaded successfully', 'path' => $path]);
        //     } catch (\Exception $e) {
        //         return response()->json(['message' => 'Gambar upload failed', 'error' => $e->getMessage()], 500);
        //     }
        // } else {
        //     return response()->json(['message' => 'No gambar provided'], 400);
        // }
        $rules = [
            'nama'      => 'required|unique_nama_tipe_combination',
            'harga'     => 'required|numeric|min:0',
            'stok'      => 'required|integer|min:0',
            'tipe'      => 'required|exists:tipe,id',
            'gambar'    => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'deskripsi' => 'required',
        ];
        
        // Check if there is a kemasan_id in the request
        if ($request->has('kemasan_id')) {
            // If kemasan_id is present, uom and ukuran are not required
            $rules['kemasan_id'] = 'integer|min:0';
        } else {
            // If kemasan_id is not present, require uom and ukuran
            $rules['kemasan_id'] = 'integer|min:0';
            $rules['uom'] = 'required';
            $rules['ukuran'] = 'required|numeric|min:0';
        }
        
        $customMessages = [
            'unique_nama_tipe_combination' => 'Nama dan tipe tersebut sudah ada.',
        ];
        
        $validator = Validator::make($request->all(), $rules, $customMessages);
        
        
    
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        // $stok = $request->stok;
        // return response()->json($stok, 422);
        //Mengatur pengelolaan gambar
        if ($request->file('gambar')->isValid()) {
            // File is valid, proceed with saving
        } else {
            // File upload failed
            return response()->json(['error' => 'File upload failed'], 400);
        }
        try {
            $fileName = uniqid() . '_' . $request->gambar->getClientOriginalName();
            $request->gambar->move(public_path('storage/barang'), $fileName);

            DB::beginTransaction();
        
            // Create barang
            $barang = Barang::create([
                'deskripsi' => $request->deskripsi,
                'nama' => $request->nama,
                'tipe' => $request->tipe,
                'gambar' => 'storage/barang/' . $fileName
            ]);
            $kemasan = null; // Initialize $kemasan variable

    if ($request->has('kemasan_id')) {
        // Use the provided kemasan_id
        $kemasanId = $request->kemasan_id;
    } else {
        // Check if there is a matching kemasan based on uom and ukuran
        $matchingKemasan = Kemasan::where('uom', $request->uom)
                                    ->where('ukuran', $request->ukuran)
                                    ->first();

        if ($matchingKemasan) {
            // Use the ID of the matching kemasan
            $kemasanId = $matchingKemasan->id;
            $kemasan = $matchingKemasan; // Set $kemasan variable
        } else {
            // Create a new kemasan if no matching record is found
            $newKemasan = Kemasan::create([
                'ukuran' => $request->ukuran,
                'uom' => $request->uom,
            ]);

            // Use the ID of the newly created kemasan
            $kemasanId = $newKemasan->id;
            $kemasan = $newKemasan; // Set $kemasan variable
        }
    }

    // Create barang_kemasan
    $barang_kemasan = Barang_Kemasan::create([
        'barang_id' => $barang->id,
        'kemasan_id' => $kemasanId,
        'stok' => $request->stok,
        'harga' => $request->harga,
    ]);

    // Commit the transaction
    DB::commit();

    $daftar_barang = [
        'barang' => $barang,
        'kemasan' => $kemasan, // Include $kemasan in the response
        'barang_kemasan' => $barang_kemasan,
    ];
        
                return new AllResource(true, 'Data barang Berhasil Ditambahkan!', $daftar_barang);
        
        } catch (\Exception $e) {
            // An error occurred, rollback the transaction
            DB::rollBack();
        
            // Log the exception or handle it accordingly
            return response()->json(['error' => $e], 500);
        }
   
         }
    public function show($id)
    {
        //find daftar_barang by ID
        $daftar_barang = Barang::find($id);

        //return single barang as a resource
        return new AllResource(true, 'Detail Data daftar_barang!', $daftar_barang);
    }

    /**
     * update
     *
     * @param  mixed $request
     * @param  mixed $daftar_barang
     * @return void
     */
    public function update(Request $request, $id)
    {
        //define validation rules
        $validator = Validator::make($request->all(), [
            'company_name'     => 'required',
            'address'     => 'required',
            'phone'   => 'required',
            'email'   => 'required',
            'website'   => 'required',
           
        ]);

        //check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //find daftar_barang by ID
        $daftar_barang = Barang::find($id);

        //check if image is not empty
      
            //update daftar_barang without image
            $daftar_barang->update([
                'company_name'     => $request->company_name,
                'address'     => $request->address,
                'phone'   => $request->phone,
                'email'   => $request->email,
                'website'   => $request->website,
            ]);
        

        //return response
        return new AllResource(true, 'Data daftar_barang Berhasil Diubah!', $daftar_barang);
    }

    /**
     * destroy
     *
     * @param  mixed $daftar_barang
     * @return void
     */
    public function destroy($id)
    {

        //find daftar_barang by ID
        $daftar_barang = Barang::find($id);

      
        //delete daftar_barang
        $daftar_barang->delete();

        //return response
        return new AllResource(true, 'Data daftar_barang Berhasil Dihapus!', null);
    }
    //
}
