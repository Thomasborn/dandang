<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\AllResource;
use App\Http\Resources\PostResource;
use App\Models\Depo;
use App\Models\Driver;
use App\Models\Master;
use App\Models\Saler;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class SalerController extends Controller
{private $salesData;

    public function __construct()
    {
        $this->salesData = [];
    }

    public function getSalesData()
    {
        return $this->salesData;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('perPage', 10);
            $search = $request->input('search');
        
            $query = Saler::with('user.userWithRole'); // Load the role relationship
        
            if ($search) {
                $query->where('Kode', 'LIKE', '%' . $search . '%')
                      ->orWhere('Nama', 'LIKE', '%' . $search . '%')
                      ->orWhere('alamat', 'LIKE', '%' . $search . '%')
                      ->orWhereHas('user.userWithRole', function ($roleQuery) use ($search) {
                          $roleQuery->where('name', 'LIKE', '%' . $search . '%');
                      });
            }
        
            $sales = $query->paginate($perPage);
            $transformedData = collect($sales->items())->map(function ($saler) {
                return [
                    'id' => strval($saler->id),
                    'name' => $saler->Nama,
                    'code' => $saler->Kode,
                    'role_id' => optional($saler->user)->role_id,
                ];
            });
        
            // Recreate a paginator with the transformed data
            $transformedSalesData = new LengthAwarePaginator(
                $transformedData->all(),
                $sales->total(),
                $sales->perPage(),
                $sales->currentPage(),
                ['path' => url('/api/sales')]
            );
        
            // Return the resource
            return new AllResource(true, 'List Data sellers', $transformedSalesData);
        
        } catch (\Exception $e) {
            // Handle exceptions if any
            return new AllResource(false, 'Error fetching sellers data', $e->getMessage());
        }
        
        
    }public function searchSeller()
    {
        $query = Saler::all(); // Load all sellers
    
        $transformedData = $query->map(function ($seller) {
            return [
                'id' => $seller->id,
                'name' => $seller->Nama, // Check if it's 'Nama' or 'name'
                'code' => $seller->Kode, // Check if it's 'Kode' or 'code'
                'role_id' => optional($seller->user)->role_id, // Assuming there's a 'user' relationship
            ];
        });
    
        // Return the resource
        return new AllResource(true, 'List Data sellers', $transformedData);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Validate the request data
            $this->validate($request, [
                'code' => 'required',
                'name' => 'required',
                'user_id' => 'required',
            ]);
        
            // Extract user_id from the request
            $user_id = $request->user_id;
        
            // Specify the tables to check for relations
            $tablesToCheck = [Depo::class, Saler::class, Driver::class, Master::class];
        
            // Check if the user_id exists in any of the specified tables
            if (userHasRelations($user_id, $tablesToCheck)) {
                return response()->json(['error' => 'User has existing relations in other tables. Operation cancelled.'], 400);
            }
        
            // Create a new Saler
            $saler = Saler::create([
                'Kode' => $request->code,
                'Nama' => $request->name,
                'alamat' => $request->address,
                'user_id' => $user_id,
            ]);
        
            // Return success response
            return new PostResource(true, 'Data saler created successfully', $saler);
        
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors
            return response()->json(['error' => $e->validator->errors()], 422);
        } catch (\Exception $e) {
            // Handle other exceptions
            return response()->json(['error' => $e->getMessage()], 500);
        }
        

    }
    public function searchSellers(Request $request){
        $perPage = $request->input('perPage', 10);
        $search = $request->input('search');
    
        $query = Saler::with('user.role'); // Load the role relationship
    
        if ($search) {
            $query->where('Kode', 'LIKE', '%' . $search . '%')
                  ->orWhere('Nama', 'LIKE', '%' . $search . '%')
                  ->orWhere('alamat', 'LIKE', '%' . $search . '%')
                  ->orWhereHas('user.role', function ($roleQuery) use ($search) {
                      $roleQuery->where('name', 'LIKE', '%' . $search . '%');
                  });
        }
    
        $sales = $query->paginate($perPage);
    
        return response()->json([
            'success' => true,
            'message' => 'Saler data retrieved successfully.',
            'data' => $sales,
        ]);
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            // Find the Saler by ID
            $saler = Saler::find($id);
        
            // Check if Saler is not found
            if (!$saler) {
                return response()->json([
                    'success' => false,
                    'message' => 'Saler not found',
                ], 404);
            }
        
            // Transform the data
            $transformedData = [
                'id' => $saler->id,
                'name' => $saler->Nama, // Replace with the actual attribute name for 'name'
                'code' => $saler->Kode, // Replace with the actual attribute name for 'code'
                'role_id' => optional($saler->user)->role_id, // Use optional to handle null
            ];
        
            // Return the transformed data
            return new PostResource(true, 'Data sale retrieved successfully', $transformedData);
        
        } catch (\Exception $e) {
            // Handle other exceptions if any
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
        

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        try {
            $saler = [1];
        
         
            return new AllResource(true, 'Data saler and associated user updated successfully', $saler);
        
        } catch (\Exception $e) {
            // Handle exceptions here
            return response()->json(['message' => 'An error occurred while updating data.', 'error' => $e->getMessage()], 500);
        }
        
        
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $saler = Saler::find($id);

        if (!$saler) {
            return response()->json(['message' => 'Saler not found'], 404);
        }

        $saler->delete();
        return new AllResource(true, 'Saler deleted successfully', $saler);

    
    }
}
