<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Saler;
use Illuminate\Http\Request;

class SellerControl extends Controller
{
    public function searchSellers(Request $request){
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
    
        return response()->json([
            'success' => true,
            'message' => 'Saler data retrieved successfully.',
            'data' => $sales,
        ]);
    } //
}
