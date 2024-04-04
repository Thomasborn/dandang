<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AllResource;
use App\Models\Barang_kemasan;
use App\Models\Kemasan;
use Illuminate\Http\Request;
//import Facade "Validator"
use Illuminate\Support\Facades\Validator;

class BarangKemasanController extends Controller
{
    public function index()
    {
        //get all BarangKemasan
        $barangKemasan = Barang_kemasan::with(['barang', 'kemasan'])->get();
        $transformedData = $barangKemasan->map(function ($barangKemasan) {
            return [
                'id' => $barangKemasan->id,
                'name' => $barangKemasan->barang->nama,
                'description' => $barangKemasan->barang->deskripsi,
                'size' => $barangKemasan->kemasan->ukuran,
                'uom' => $barangKemasan->kemasan->uom,
                'price' => $barangKemasan->harga,
                'stock' => $barangKemasan->stok,
            ];
        });
        
        // Return the transformed data
        return new AllResource(true, 'List Data BarangKemasan', $transformedData);
        
        //return collection of BarangKemasan as a resource
        // return new AllResource(true, 'List Data BarangKemasan', $barangKemasan);
    }
     /**
     * store
     *
     * @param  mixed $request
     * @return void
     */
    public function store(Request $request)
    {
        //define validation rules
        $rules = [
            'harga' => 'required|numeric|min:0|max:100000000',
            'stok' => 'required|numeric|min:1',
            'kemasan_id' => 'exists:kemasan,id',
            'barang_id' => 'required|exists:barang,id',
        ];
        
        // Check if there is no kemasan_id in the request
        if (!$request->has('kemasan_id')) {
            // If kemasan_id is not present, make ukuran and uom required
            $rules['ukuran'] = 'required|numeric|max:255';
            $rules['uom'] = 'required|string|max:255';
        }
        
        $validator = Validator::make($request->all(), $rules);
        
        // Check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        
        // If kemasan_id is not present, check if the combination exists in the table
        if (!$request->has('kemasan_id')) {
            $existingKemasan = Kemasan::where('ukuran', $request->ukuran)
                                       ->where('uom', $request->uom)
                                       ->first();
        
            // If the combination exists, use the existing kemasan_id
            if ($existingKemasan) {
                $kemasanId = $existingKemasan->id;
            } else {
                // If the combination doesn't exist, create a new Kemasan
                $newKemasan = Kemasan::create([
                    'ukuran' => $request->ukuran,
                    'uom' => $request->uom,
                ]);
        
                // Use the ID of the newly created kemasan
                $kemasanId = $newKemasan->id;
            }
        } else {
            // If kemasan_id is present, use the provided kemasan_id
            $kemasanId = $request->kemasan_id;
        }
        
                $existingBarangKemasan = barang_kemasan::where('barang_id', $request->barang_id)
            ->where('kemasan_id', $kemasanId)
            ->first();

        if ($existingBarangKemasan) {
            // If the combination exists, return a response with an error message
            return response()->json(['error' => 'Barang sudah memiliki kemasan tersebut.'], 422);
        }

        // If the combination doesn't exist, create a new BarangKemasan
        $barang_kemasan = barang_kemasan::create([
            'barang_id' => $request->barang_id,
            'kemasan_id' => $kemasanId,
            'stok' => $request->stok,
            'harga' => $request->harga,
        ]);
      
        // Return a response or perform additional actions as needed
        
      
        //return response
        return new AllResource(true, 'Data BarangKemasan Berhasil Ditambahkan!', $barang_kemasan);
    }
    public function show($id)
    {
        //find BarangKemasan by ID
        $BarangKemasan = Barang_kemasan::find($id);

        //return single BarangKemasan as a resource
        return new AllResource(true, 'Detail Data BarangKemasan!', $BarangKemasan);
    }

    /**
     * update
     *
     * @param  mixed $request
     * @param  mixed $BarangKemasan
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

        //find BarangKemasan by ID
        $BarangKemasan = Barang_kemasan::find($id);

        //check if image is not empty
      
            //update BarangKemasan without image
            $BarangKemasan->update([
                'company_name'     => $request->company_name,
                'address'     => $request->address,
                'phone'   => $request->phone,
                'email'   => $request->email,
                'website'   => $request->website,
            ]);
        

        //return response
        return new AllResource(true, 'Data BarangKemasan Berhasil Diubah!', $BarangKemasan);
    }

    /**
     * destroy
     *
     * @param  mixed $BarangKemasan
     * @return void
     */
    public function destroy($id)
    {

        //find BarangKemasan by ID
        $BarangKemasan = Barang_kemasan::find($id);

      
        //delete BarangKemasan
        $BarangKemasan->delete();

        //return response
        return new AllResource(true, 'Data BarangKemasan Berhasil Dihapus!', null);
    }
    //
}
