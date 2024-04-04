<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\AllResource;
use App\Models\Depo;
use App\Models\Driver;
use App\Models\Master;
use App\Models\Saler;
use Illuminate\Http\Request;

class MasterController extends Controller
{
    public function index()
    {
        $masters = Master::all();
        return new AllResource(true, 'List Data Masters', $masters);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'code' => 'required',
            'name' => 'required',
        ]);
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
        
        // Check if the user_id exists in any of the specified tables
        if (userHasRelations($user_id, $tablesToCheck)) {
            return response()->json(['error' => 'User has existing relations in other tables. Operation cancelled.'], 400);
        }
        $master = Master::create([
            'kode' => $request->code,
            'nama' => $request->name,
            
        ]);
        // $master = Master::create($request->all());
        return new AllResource(true, 'Data Master created successfully', $master);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $master = Master::find($id);

        if (!$master) {
            return new AllResource(false, 'Master not found', null);
        }

        return new AllResource(true, 'Data Master retrieved successfully', $master);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $master = Master::find($id);

        if (!$master) {
            return new AllResource(false, 'Master not found', null);
        }

        $master->update($request->all());
        return new AllResource(true, 'Data Master updated successfully', $master);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $master = Master::find($id);

        if (!$master) {
            return new AllResource(false, 'Master not found', null);
        }

        $master->delete();
        return new AllResource(true, 'Master deleted successfully', null);
    }
}
