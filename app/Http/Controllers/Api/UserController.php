<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AllResource;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\CreateUserRequest;
//import Facade "Validator"
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index()
    {
        //get all users
        $users = User::all();

        $usersData = $users->map(function ($user) {
            // Get roles for the current user
            $roles = $user->roles->pluck('name')->toArray();
        
            return [
                'id' => $user->id,
                'username' => $user->username,
                'nomor_telepon' => $user->nomor_telepon,
                'email' => $user->email,
                'roles' => $roles, // Include roles in the returned data
            ];
        });
        //return collection of users as a resource
        return new AllResource(true, 'List Data users', $usersData);
    }
     /**
     * store
     *
     * @param  mixed $request
     * @return void
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|unique:users',
            'password' => 'required',
            'nomor_telepon' => 'required',
            'email' => 'required|email|unique:users',
            'role' => 'required',
        ]);
        
        // Check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        
        // Create a new user
        $users = User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'nomor_telepon' => $request->nomor_telepon,
            'email' => $request->email,
            'role' => $request->role,
        ]);

        //return response
        return new AllResource(true, 'Data user Berhasil Ditambahkan!', $users);
    }
    public function show($id)
    {
        //find users by ID
        $users = user::find($id);

        //return single user as a resource
        return new AllResource(true, 'Detail Data users!', $users);
    }

    /**
     * update
     *
     * @param  mixed $request
     * @param  mixed $users
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

        //find users by ID
        $users = user::find($id);

        //check if image is not empty
      
            //update users without image
            $users->update([
                'company_name'     => $request->company_name,
                'address'     => $request->address,
                'phone'   => $request->phone,
                'email'   => $request->email,
                'website'   => $request->website,
            ]);
        

        //return response
        return new AllResource(true, 'Data users Berhasil Diubah!', $users);
    }

    /**
     * destroy
     *
     * @param  mixed $users
     * @return void
     */
    public function destroy($id)
    {

        //find users by ID
        $users = user::find($id);

      
        //delete users
        $users->delete();

        //return response
        return new AllResource(true, 'Data users Berhasil Dihapus!', null);
    }
    //
}
