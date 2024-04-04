<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Master;
use App\Models\Saler;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function updateProfile(Request $request, $id)
    {
        try {
        // Validate the incoming request data
        $request->validate([
            'name' => 'string',
            'email' => 'email|unique:users,email,' . $id,
            'address' => 'string',
            'contact' => 'int',
        ]);

            // Find the user by ID
            $user = User::findOrFail($id);
           
            // Update user information
            $user->update([
                'contact' => $request->input('contact'),
                'email' => $request->input('email'),
            ]);
            
            if($user->role_id==1001){
            // as Master/ Super Admin
                $role= Master::where('user_id',$id)->first();
                $role->update([
                    'nama' => $request->input('name'),
                    'alamat' => $request->input('address'),
                ]);
                
            }
            if($user->role_id==7){
                //Driver
                $role= Driver::where('user_id',$id)->first();
                $role->update([
                    'name' => $request->input('name'),
                    'address' => $request->input('address'),
                ]);
            }else{
                $role= Saler::where('user_id',$id)->first();
                $role->update([
                    'Nama' => $request->input('name'),
                    'alamat' => $request->input('address'),
                ]);
            }
            // Update roles information

            return response()->json([
                'success' => true,
                'message' => 'User profile updated successfully.',
                // 'data' => $role,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating user profile.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function updateAccount(Request $request,$id){
        try{
            $request->validate([
            'name' => 'string',
            // 'email' => 'email|unique:users,email,' . $id,
            // 'password' => 'string',
            // 'contact' => 'int',
        ]);

            // Find the user by ID
            $user = User::findOrFail($id);
           
            // Update user information
            if ($request->filled('name')) {
                $user->name = $request->input('name');
            }
            
            // Check if the request contains a non-null 'password' value
            if ($request->filled('password')) {
                $user->password = Hash::make($request->input('password'));
            }
            
            // Update user information only if changes were made
            if ($user->isDirty()) {
                $user->update();
            }
            return response()->json([
                'success' => true,
                'message' => 'User account updated successfully.',
                // 'data' => $role,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating user account.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
}
