<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AllResource;
use App\Http\Resources\PostResource;
use Illuminate\Http\Request;
use App\Models\Depo;
use App\Models\Driver;
use App\Models\Master;
use App\Models\Saler;
use Illuminate\Pagination\LengthAwarePaginator;
//import Facade "Validator"
use Illuminate\Support\Facades\Validator;

class DepoController extends Controller
{
  public function index(Request $request)
{
    // Get the search query parameters for Kode and alamat
    $search = $request->input('search');
    $kode = $request->input('Kode');
    $alamat = $request->input('alamat');

    // Query the Depo model based on the search parameters
    $query = Depo::query();

    // Apply search filter if search term is provided
    if ($search) {
        $query->where('Kode', 'LIKE', '%' . $search . '%')
              ->orWhere('alamat', 'LIKE', '%' . $search . '%');
    }

    // Filter by Kode if it's provided
    if ($kode !== null) {
        $query->where('Kode', $kode);
    }

    // Filter by alamat if it's provided
    if ($alamat !== null) {
        $query->where('alamat', 'like', '%' . $alamat . '%');
    }

    // Get the filtered Depo collection with pagination
    $perPage = $request->input('perPage', 10);
    $depo = $query->paginate($perPage);

    // Transform the filtered Depo collection into the desired format
    $formattedDepo = $depo->map(function ($item) {
        return [
            'id' => strval($item->id),
            'code' => $item->Kode,
            'address' => $item->alamat
        ];
    });

    // Recreate a paginator with the transformed data
    $transformedDepoData = new LengthAwarePaginator(
        $formattedDepo,
        $depo->total(),
        $depo->perPage(),
        $depo->currentPage(),
        ['path' => $request->url(), 'query' => $request->query()]
    );

    // Return the recreated paginator as a resource
    return new AllResource(true, 'List Data depo', $transformedDepoData);
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
            // 'name' => 'required|string|max:255',
            // 'code' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'user_id' => 'required|string|max:255',
            // 'user_id' => 'required|exists:users,id',
        ]);
        
        // Check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        function userHasRelations($user_id, $tables)
        {
            foreach ($tables as $table) {
                if ($table::where('user_id', $user_id)->exists()) {
                    return true;
                }
            }
        
            return false;
        }
        
        $user_id = $request->user_id;
        
        // Specify the tables to check for relations
        $tablesToCheck = [Depo::class, Saler::class, Driver::class, Master::class];
        
        $province = $request->province;
        $city = $request->city;
        
        // Getting the first two characters of province and city
        $provincePrefix = substr($province, 0, 2);
        $cityPrefix = substr($city, 0, 2);
        
        // Concatenating the prefixes with D and 0.1
        $code = "D" . $provincePrefix . $cityPrefix . "0.1";
        // Check if the user_id exists in any of the specified tables
        if (userHasRelations($user_id, $tablesToCheck)) {
            return response()->json(['error' => 'User has existing relations in other tables. Operation cancelled.'], 400);
        }
        // Create a new depo
        $depo = Depo::create([
            
            'Kode' => $code,
            'alamat' => $request->address,
            'kota' => $request->city,
            'provinsi' => $request->province,
            // 'user_id' => $request->user_id,
            // 'user_id' => $request->user_id,
        ]);
        
        if (isset($request->fromAuthController) && $request->fromAuthController) {
            return $depo;
        }
        //return response
        return new PostResource(true, 'Data depo Berhasil Ditambahkan!', $depo);
    }
    public function show($id)
{
    // Find depo by ID
    $depo = Depo::find($id);

    // Check if the depo exists
    if (!$depo) {
        return new PostResource(false, 'Depo not found!', null);
    }

    // Format the depo data
    $formattedDepo = [
        'id' => $depo->id,
        'code' => $depo->Kode,
        'address' => $depo->alamat
    ];

    // Return single depo as a resource
    return new PostResource(true, 'Detail Data depo!', $formattedDepo);
}


    /**
     * update
     *
     * @param  mixed $request
     * @param  mixed $depo
     * @return void
     */
    public function update(Request $request, $id)
{
    // Find the depo by ID
    $depo = Depo::find($id);

    // Check if the depo exists
    if (!$depo) {
        return response()->json(['error' => 'Depo not found!'], 404);
    }

    // Define validation rules for the update
    $validator = Validator::make($request->all(), [
        'code' => 'required|string|max:255',
        'address' => 'required|string|max:255',
        'user_id' => 'required|string|max:255',
    ]);

    // Check if validation fails
    if ($validator->fails()) {
        return response()->json($validator->errors(), 422);
    }

    // Update the depo attributes
    $depo->Kode = $request->code;
    $depo->alamat = $request->address;
    $depo->user_id = $request->user_id;
    $depo->save();

    // Return the updated depo as a resource
    return new PostResource(true, 'Data depo berhasil diperbarui!', $depo);
}


    /**
     * destroy
     *
     * @param  mixed $depo
     * @return void
     */
    public function destroy($id)
    {

        //find depo by ID
        $depo = depo::find($id);

      
        //delete depo
        $depo->delete();

        //return response
        return new AllResource(true, 'Data depo Berhasil Dihapus!', null);
    }
    //
}
