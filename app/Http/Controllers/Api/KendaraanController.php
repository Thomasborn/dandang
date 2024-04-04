<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AllResource;
use Illuminate\Http\Request;
use App\Models\kendaraan;
//import Facade "Validator"
use Illuminate\Support\Facades\Validator;

class KendaraanController extends Controller
{
    public function index()
    {
        //get all kendaraan
        $kendaraan = kendaraan::all();

        //return collection of kendaraan as a resource
        return new AllResource(true, 'List Data kendaraan', $kendaraan);
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
        $validator = Validator::make($request->all(), [
            'depo_id' => 'required|exists:depo,id',
            'jenis' => 'required|string|max:50',
            'nama' => 'required|string|max:50',
            'nomor_polisi' => 'required|string|max:50',
        ]);
        
        // Check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        
        // Create a new kendaraan
        $kendaraan = kendaraan::create([
            'depo_id' => $request->depo_id,
            'jenis' => $request->jenis,
            'nama' => $request->nama,
            'nomor_polisi' => $request->nomor_polisi,
        ]);
        
        

        //return response
        return new AllResource(true, 'Data kendaraan Berhasil Ditambahkan!', $kendaraan);
    }
    public function show($id)
    {
        //find kendaraan by ID
        $kendaraan = kendaraan::find($id);

        //return single kendaraan as a resource
        return new AllResource(true, 'Detail Data kendaraan!', $kendaraan);
    }

    /**
     * update
     *
     * @param  mixed $request
     * @param  mixed $kendaraan
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

        //find kendaraan by ID
        $kendaraan = kendaraan::find($id);

        //check if image is not empty
      
            //update kendaraan without image
            $kendaraan->update([
                'company_name'     => $request->company_name,
                'address'     => $request->address,
                'phone'   => $request->phone,
                'email'   => $request->email,
                'website'   => $request->website,
            ]);
        

        //return response
        return new AllResource(true, 'Data kendaraan Berhasil Diubah!', $kendaraan);
    }

    /**
     * destroy
     *
     * @param  mixed $kendaraan
     * @return void
     */
    public function destroy($id)
    {

        //find kendaraan by ID
        $kendaraan = kendaraan::find($id);

      
        //delete kendaraan
        $kendaraan->delete();

        //return response
        return new AllResource(true, 'Data kendaraan Berhasil Dihapus!', null);
    }
    //
}
