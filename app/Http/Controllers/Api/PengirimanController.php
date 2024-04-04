<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AllResource;
use Illuminate\Http\Request;
use App\Models\pengiriman;
//import Facade "Validator"
use Illuminate\Support\Facades\Validator;

class PengirimanController extends Controller
{
    public function index()
    {
        //get all pengiriman
        $pengiriman = pengiriman::all();

        //return collection of pengiriman as a resource
        return new AllResource(true, 'List Data pengiriman', $pengiriman);
    }
     /**
     * store
     *
     * @param  mixed $request
     * @return void
     */
    public function store(Request $request)
    {
                // Validate the request
        $validator = Validator::make($request->all(), [
            'transaksi_id' => 'required|exists:transaksi,id',
            'driver_id' => 'required|exists:driver,id',
            'gudang_asal_id' => 'required|exists:gudang,id',
            'tanggal_pengiriman' => 'required|date',
            'status_pengiriman' => 'required|string|max:50',
            'keterangan' => 'nullable|string|max:255',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Create a new pengiriman
        $pengiriman = Pengiriman::create($request->all());
                

        //return response
        return new AllResource(true, 'Data pengiriman Berhasil Ditambahkan!', $pengiriman);
    }
    public function show($id)
    {
        //find pengiriman by ID
        $pengiriman = pengiriman::find($id);

        //return single pengiriman as a resource
        return new AllResource(true, 'Detail Data pengiriman!', $pengiriman);
    }

    /**
     * update
     *
     * @param  mixed $request
     * @param  mixed $pengiriman
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

        //find pengiriman by ID
        $pengiriman = pengiriman::find($id);

        //check if image is not empty
      
            //update pengiriman without image
            $pengiriman->update([
                'company_name'     => $request->company_name,
                'address'     => $request->address,
                'phone'   => $request->phone,
                'email'   => $request->email,
                'website'   => $request->website,
            ]);
        

        //return response
        return new AllResource(true, 'Data pengiriman Berhasil Diubah!', $pengiriman);
    }

    /**
     * destroy
     *
     * @param  mixed $pengiriman
     * @return void
     */
    public function destroy($id)
    {

        //find pengiriman by ID
        $pengiriman = pengiriman::find($id);

      
        //delete pengiriman
        $pengiriman->delete();

        //return response
        return new AllResource(true, 'Data pengiriman Berhasil Dihapus!', null);
    }
    //
}
