<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AllResource;
use Illuminate\Http\Request;
use App\Models\gudang;
//import Facade "Validator"
use Illuminate\Support\Facades\Validator;

class GudangController extends Controller
{
    public function index()
    {
        //get all gudang
        $gudang = gudang::all();

        //return collection of gudang as a resource
        return new AllResource(true, 'List Data gudang', $gudang);
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
            'nama' => 'required|string|max:255',
            'alamat' => 'required|string|max:255',
        ]);
        
        // Check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        
        // Create a new gudang
        $gudang = Gudang::create([
            'nama' => $request->nama,
            'alamat' => $request->alamat,
        ]);
        
        

        //return response
        return new AllResource(true, 'Data gudang Berhasil Ditambahkan!', $gudang);
    }
    public function show($id)
    {
        //find gudang by ID
        $gudang = gudang::find($id);

        //return single gudang as a resource
        return new AllResource(true, 'Detail Data gudang!', $gudang);
    }

    /**
     * update
     *
     * @param  mixed $request
     * @param  mixed $gudang
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

        //find gudang by ID
        $gudang = gudang::find($id);

        //check if image is not empty
      
            //update gudang without image
            $gudang->update([
                'company_name'     => $request->company_name,
                'address'     => $request->address,
                'phone'   => $request->phone,
                'email'   => $request->email,
                'website'   => $request->website,
            ]);
        

        //return response
        return new AllResource(true, 'Data gudang Berhasil Diubah!', $gudang);
    }

    /**
     * destroy
     *
     * @param  mixed $gudang
     * @return void
     */
    public function destroy($id)
    {

        //find gudang by ID
        $gudang = gudang::find($id);

      
        //delete gudang
        $gudang->delete();

        //return response
        return new AllResource(true, 'Data gudang Berhasil Dihapus!', null);
    }
    //
}
