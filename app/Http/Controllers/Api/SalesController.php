<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AllResource;
use Illuminate\Http\Request;
use App\Models\sales;
//import Facade "Validator"
use Illuminate\Support\Facades\Validator;

class SalesController extends Controller
{ private $salesData;

    public function __construct()
    {
        $this->salesData = [];
    }

    public function getSalesData()
    {
        return $this->salesData;
    }

    public function index()
    {
        //get all sales
        $sales = sales::all();

        //return collection of sales as a resource
        return new AllResource(true, 'List Data sales', $sales);
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
            'depo_id' => 'required|exists:depo,id',
            'kendaraan_id' => 'required_if:tipe,mobiliris,motoris|exists:kendaraan,id',
            'tipe' => 'required|string',
            'user_id' => 'required|exists:users,id',
        ]);
        
        // Check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        
        // Create a new sale
        $sales = sales::create([
            'nama' => $request->nama,
            'depo_id' => $request->depo_id,
            'kendaraan_id' => $request->kendaraan_id,
            'tipe' => $request->tipe,
            'user_id' => $request->user_id,
        ]);
        
        
        if (isset($request->fromAuthController) && $request->fromAuthController) {
            return $sales;
        }
        //return response
        return new AllResource(true, 'Data sales Berhasil Ditambahkan!', $sales);
    }
    public function show($id)
    {
        //find sales by ID
        $sales = sales::find($id);

        //return single sales as a resource
        return new AllResource(true, 'Detail Data sales!', $sales);
    }

    /**
     * update
     *
     * @param  mixed $request
     * @param  mixed $sales
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

        //find sales by ID
        $sales = sales::find($id);

        //check if image is not empty
      
            //update sales without image
            $sales->update([
                'company_name'     => $request->company_name,
                'address'     => $request->address,
                'phone'   => $request->phone,
                'email'   => $request->email,
                'website'   => $request->website,
            ]);
        

        //return response
        return new AllResource(true, 'Data sales Berhasil Diubah!', $sales);
    }

    /**
     * destroy
     *
     * @param  mixed $sales
     * @return void
     */
    public function destroy($id)
    {

        //find sales by ID
        $sales = sales::find($id);

      
        //delete sales
        $sales->delete();

        //return response
        return new AllResource(true, 'Data sales Berhasil Dihapus!', null);
    }
    //
}
