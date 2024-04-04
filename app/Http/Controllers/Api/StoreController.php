<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AllResource;
use Illuminate\Http\Request;
use App\Models\customer;
use Illuminate\Support\Facades\Auth;
//import Facade "Validator"
use Illuminate\Support\Facades\Validator;

class StoreController extends Controller
{
    public function index()
    {
        
        $loggedInUserId = request()->header('X-User-Id');

    if (!$loggedInUserId) {
       
        return new AllResource(false, 'User ID not provided', null);
    }

 
    $customersWithTransactions = customer::whereHas('transactions.sales', function ($query) use ($loggedInUserId) {
        $query->where('sales_id', $loggedInUserId);
    })->get();

    return new AllResource(true, 'List Data Customers with Transactions', $customersWithTransactions);
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
            'kode' => 'required|string|max:255',
            'nama' => 'required|string|max:255',
            'alamat' => 'required|string|max:255',
            'nomor_telepon' => 'required|string|max:15',
        ]);
        
        // Check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        
        // Create a new store
        $store = customer::create([
            'kode' => $request->kode,
            'nama' => $request->nama,
            'alamat' => $request->alamat,
            'nomor_telepon' => $request->nomor_telepon,
        ]);
        
        

        //return response
        return new AllResource(true, 'Data store Berhasil Ditambahkan!', $store);
    }
    public function show($id)
    {
        //find store by ID
        $store = customer::find($id);

        //return single store as a resource
        return new AllResource(true, 'Detail Data store!', $store);
    }

    /**
     * update
     *
     * @param  mixed $request
     * @param  mixed $store
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

        //find store by ID
        $store = customer::find($id);

        //check if image is not empty
      
            //update store without image
            $store->update([
                'company_name'     => $request->company_name,
                'address'     => $request->address,
                'phone'   => $request->phone,
                'email'   => $request->email,
                'website'   => $request->website,
            ]);
        

        //return response
        return new AllResource(true, 'Data store Berhasil Diubah!', $store);
    }

    /**
     * destroy
     *
     * @param  mixed $store
     * @return void
     */
    public function destroy($id)
    {

        //find store by ID
        $store = customer::find($id);

      
        //delete store
        $store->delete();

        //return response
        return new AllResource(true, 'Data store Berhasil Dihapus!', null);
    }
    //
}
