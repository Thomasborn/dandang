<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AllResource;
use Illuminate\Http\Request;
use App\Models\barang_bonus;
use App\Http\Requests\CreateBarangBonusRequest;
//import Facade "Validator"
use Illuminate\Support\Facades\Validator;

class BarangBonusController extends Controller
{
    public function index()
    {
        //get all barangBonus
        $barangBonus = barang_bonus::all();

        //return collection of barangBonus as a resource
        return new AllResource(true, 'List Data barangBonus', $barangBonus);
    }
     /**
     * store
     *
     * @param  mixed $request
     * @return void
     */
    public function store(CreateBarangBonusRequest $request)
    {
        // Validate the request using the CreateBarangBonusRequest
        $validatedData = $request->validated();

        // Create a new barang_bonus
        $barangBonus = barang_bonus::create($validatedData);


        //return response
        return new AllResource(true, 'Data barangBonus Berhasil Ditambahkan!', $barangBonus);
    }
    public function show($id)
    {
        //find barangBonus by ID
        $barangBonus = barang_bonus::find($id);

        //return single barangBonus as a resource
        return new AllResource(true, 'Detail Data barangBonus!', $barangBonus);
    }

    /**
     * update
     *
     * @param  mixed $request
     * @param  mixed $barangBonus
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

        //find barangBonus by ID
        $barangBonus = barang_bonus::find($id);

        //check if image is not empty
      
            //update barangBonus without image
            $barangBonus->update([
                'company_name'     => $request->company_name,
                'address'     => $request->address,
                'phone'   => $request->phone,
                'email'   => $request->email,
                'website'   => $request->website,
            ]);
        

        //return response
        return new AllResource(true, 'Data barangBonus Berhasil Diubah!', $barangBonus);
    }

    /**
     * destroy
     *
     * @param  mixed $barangBonus
     * @return void
     */
    public function destroy($id)
    {

        //find barangBonus by ID
        $barangBonus = barang_bonus::find($id);

      
        //delete barangBonus
        $barangBonus->delete();

        //return response
        return new AllResource(true, 'Data barangBonus Berhasil Dihapus!', null);
    }
    //
}
