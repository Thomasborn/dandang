<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AllResource;
use App\Http\Resources\PostResource;
use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Pagination\LengthAwarePaginator;
//import Facade "Validator"
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        //get all customer
  //get all customer

        // Transform the customer data
      // Paginate the data
      $perPage = $request->input('perPage', 10);
      $search = $request->input('search');
  
      $query = Customer::query();
  
      if ($search) {
          $query->where('customer_name', 'LIKE', '%' . $search . '%')
                ->orWhere('customer_email', 'LIKE', '%' . $search . '%')
                ->orWhere('city', 'LIKE', '%' . $search . '%')
                ->orWhere('address', 'LIKE', '%' . $search . '%');
      }
  
      $customers = $query->paginate($perPage);
// Transform the paginated data
$transformedData = collect($customers->items())->map(function ($customer) {
    return [
        'id' => strval($customer->id),
        'name' => $customer->customer_name,
        'code' => 'cst' . str_pad($customer->id, 4, '0', STR_PAD_LEFT),
        'address' => $customer->address,
        'contact' => $customer->customer_phone,
        'city' => $customer->city,
    ]; 
        
});

// Recreate a paginator with the transformed data
$transformedCustomersData = new LengthAwarePaginator(
    $transformedData->all(),
    $customers->total(),
    $customers->perPage(),
    $customers->currentPage(),
    ['path' => url('/api/customers')]
);
$transformedCustomersData->appends(['perPage' => $perPage, 'search' => $search]);

// Return the resource
return new AllResource(true, 'List Data customers', $transformedCustomersData);

        
        // Now $transformedData contains the desired structure
        
        // Return the paginated data as a resource
        
        
        // Now $transformedData contains the desired structure
        
        
        // Now $transformedCustomers holds the data in the desired format
        
        //return collection of customer as a resource
    }
     /**
     * store
     *
     * @param  mixed $request
     * @return void
     */
    public function store(Request $request)
    {
        try {
            // Define validation rules
            $validator = Validator::make($request->all(), [
                'kode' => 'required|string|max:255',
                'nama' => 'required|string|max:255',
                'alamat' => 'required|string|max:255',
                'nomor_telepon' => 'required|string|max:15',
            ]);
        
            // Check if validation fails
            if ($validator->fails()) {
                throw new \Exception(json_encode($validator->errors()), 422);
            }
        
            // Create a new customer
            $customer = Customer::create([
                'customer_name' => $request->nama, // Corrected field name
                'customer_address' => $request->alamat, // Assuming 'address' corresponds to 'alamat'
                'customer_phone' => $request->nomor_telepon, // Assuming 'nomor_telepon' corresponds to 'phone'
            ]);
        
            // Return response
            return new AllResource(true, 'Data customer berhasil ditambahkan!', $customer);
        
        } catch (\Exception $e) {
            // Handle exceptions (validation errors)
            return response()->json(['error' => json_decode($e->getMessage())], $e->getCode());
        }
        
    }
    public function show($id)
    {
        $customer = Customer::find($id);

        // Check if the customer is not found
        if (!$customer) {
           return response()->json([
                'success' => false,
           
                    'message' => 'Customer not found!',
                
            ], 404);
            
        }
    
        // Transform the customer data
        $transformedData = [
            'id' => strval($customer->id),
            'name' => $customer->customer_name,
            'code' => 'cst' . str_pad($customer->id, 4, '0', STR_PAD_LEFT),
            'address' => $customer->address,
            'contact' => $customer->customer_phone,
        ];
    
        // Return the transformed data as a resource
        return new PostResource(true, 'Detail Data customer!', $transformedData);
    }

    /**
     * update
     *
     * @param  mixed $request
     * @param  mixed $customer
     * @return void
     */
    public function update(Request $request, $id)
    {
        //define validation rules
        $validator = Validator::make($request->all(), [
            'name'     => 'required',
            'address'     => 'required',
            'phone'   => 'required',
            'email'   => 'required',
            'city'   => 'required',
           
        ]);

        //check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //find customer by ID
        $customer = Customer::find($id);

        //check if image is not empty
      
            //update customer without image
            $customer->update([
                'customer_name' => $request->name,
            'customer_email' => $request->email,
            'address' => $request->address,
            'city' => $request->city,
            ]);
        

        //return response
        return new AllResource(true, 'Data customer Berhasil Diubah!', $customer);
    }

    /**
     * destroy
     *
     * @param  mixed $customer
     * @return void
     */
    public function destroy($id)
    {

        //find customer by ID
        $customer = Customer::find($id);

      
        //delete customer
        $customer->delete();

        //return response
        return new AllResource(true, 'Data customer Berhasil Dihapus!', null);
    }
    //
}
