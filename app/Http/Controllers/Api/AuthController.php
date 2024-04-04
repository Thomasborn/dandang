<?php

namespace App\Http\Controllers\Api;

use App\Enums\TokenAbility;
use App\Http\Controllers\API\MasterController;
use App\Http\Controllers\API\SalerController;
use App\Http\Controllers\Controller;
use App\Http\Resources\AllResource;
use App\Http\Resources\PostResource;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;
use PhpParser\Node\Expr\Cast\Object_;
// use Spatie\Permission\Contracts\Role;
use Spatie\Permission\Models\Role;
use stdClass;

class AuthController extends Controller
{
    use HasApiTokens;
    public function register(Request $request)
    {

        try {
     
            // Validate the incoming request data
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                // 'depo_id' => 'required_unless:role,depo|exists:depo,id',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6',
                'code' => 'required|string|min:6',
                'address' => 'required|string|min:6',
                'role' => 'required|string', // Adjust validation rules based on your needs
                'contact' => 'required|numeric', // Adjust validation rules based on your needs
            ]);
    
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }
            // return response()->json(['error' => $request->role], 422);
            
                DB::beginTransaction();
            
                $role = Role::where('name', $request->role)->first();
            
                if (!$role) {
                    throw new \Exception('Invalid role specified');
                }
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'contact' => $request->contact,
                    'role_id' => ($role->id),
                    'is_active' => 1,
                ]);
            
                if (!$user) {
                    throw new \Exception('Failed to create user');
                }
            
            
                $user->assignRole($role);
               
                $request->merge(['fromAuthController' => true]);
                $request->merge(['user_id' => $user->id]);
                
                if (Str::contains(strtolower($role->name), 'sales')) {
                    $request->merge(['tipe' => $role->name]);
                    $salesController = new SalerController();
                    $userRole = $salesController->store($request);
                } elseif (Str::contains(strtolower($role->name), 'depo')) {
                    $depoController = new DepoController();
                    $userRole = $depoController->store($request);
                } elseif (Str::contains(strtolower($role->name), 'driver')) {
                    $driverController = new DriverController();
                    $userRole = $driverController->store($request);
                } elseif (Str::contains(strtolower($role->name), 'super admin')) {
                    $masterController = new MasterController();
                    $userRole = $masterController->store($request);
                } else {
                    throw new \Exception('Unsupported role: ' . $role->name);
                }
            
                DB::commit();
            
                return response()->json([
                    'data' => $user,
                    'role' => $role->name,
                    'detail_role' => $userRole,
                ]);
            } catch (\Exception $e) {
                // Handle the exception (e.g., log, rollback, or report the error)
                // Undo store operations or take appropriate action based on your requirements
            
                DB::rollBack();
            
                if (isset($user) && $user->exists) {
                    $user->delete(); // Rollback the user creation
                }
            
                // Optionally rethrow the exception if needed
                return response()->json(['error' => $e->getMessage()], 500);
            }
    }
    public function unauthorized()
    {
        return response()->json(['success' =>'false','message' => 'Unauthorized'], 401);
    }
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required_without:username|string|email',
            'username' => 'required_without:email|string',
            'password' => 'required|string',
        ]);
        
        // Check if validation fails
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }
        
        // Attempt authentication
       
        if (!Auth::attempt($request->only('email', 'password'))) {
            $user = User::where('email', $request->input('email'))->first();
        
            if (!$user) {
                return response()->json(['error' => 'Email not found. Please check your email and try again.'], 401);
            }
        
            return response()->json(['error' => 'Incorrect password. Please check your password and try again.'], 401);
        }
        
      
        // return response()->json(['error' => 'Email not found or Incorrect password. Please check your email and try again.'], 401);
       
    
        
        $user = User::where('email', $request->email)
        ->orWhere('name', $request->username)
        ->first();

        // return new AllResource(false, 'Invalid credentials',  $user->id);
        
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        // $userRoles = Auth::user()->roles->pluck('name')->toArray();
        
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            //check collection in model user
            $allRoles = collect(['depo', 'sales', 'driver','superAdmin'])
            ->map(function ($role) use ($user) {
                return $user->$role;
            })
            ->filter()
            ->values()
            ->first();
           
                if ($user) {
                    $roles = $user->roles; 
                    foreach ($roles as $role) {
                        $roleName = $role->name;
                        $role_id = $role->id;
                    }
                } else {
                    return response()->json(['error' => 'Role not found'], 404);

                }
                $loginTime = Carbon::now(); // Get the current time using Carbon
                $expirationTime = $loginTime->copy()->addHours(2); // Set the expiration time to be 2 hours after login time

                $loginTimeMilliseconds = $loginTime->valueOf(); // Get the login time in milliseconds
                $expirationMilliseconds = $expirationTime->valueOf();
                $accessToken = $user->createToken('access_token', [TokenAbility::ACCESS_API->value], Carbon::now()->addMinutes(config('sanctum.expiration')))->plainTextToken;
                $refreshToken = $user->createToken('refresh_token', [TokenAbility::ISSUE_ACCESS_TOKEN->value], Carbon::now()->addMinutes(config('sanctum.rt_expiration')))->plainTextToken;
                $token = $user->createToken('auth_token', ['expires_in' => 2 * 60 * 60])->plainTextToken;
                $user->remember_token = $token;
                $user->save();

                
                // return new AllResource(true, 'Success', $user->remember_token);
                function getUserAddress($allRoles)
                {
                    return isset($allRoles->alamat) ? $allRoles->alamat : $allRoles->address;
                }
                
                function getRoleInformation($roleName, $allRoles, $role_id)
                {
                    $roleInformation = [
                        'role_id' => $role_id,
                        'role' => $roleName,
                        'permission' => [],
                    ];
                
                    if ($roleName == "depo" || $roleName == "master" || $roleName == "sales" || $roleName == "driver_id") {
                        $roleInformation[strtolower($roleName) . '_id'] = $allRoles->id;
                    }
                
                    // Add more conditions for additional role properties if needed
                
                    return $roleInformation;
                }
                
                $login = [
                    'login_time' => $loginTimeMilliseconds,
                    'exp' => $expirationMilliseconds,
                    'api_token' => $token,
                    'refresh_token' => $refreshToken,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'address' => getUserAddress($allRoles),
                        'contact' => $user->contact,
                    ],
                    'role' => getRoleInformation($roleName, $allRoles, $role_id),
                ];
                
                    
                   return $response = new AllResource(true, 'Success', $login);

                    // Add the access token as a cookie
                    // $cookie = cookie('api_token', $accessToken, config('sanctum.expiration'));

                    // Return the response with the cookie
                    // return $response->response()->withCookie($cookie);      
                 } else {
                            return new AllResource(false, 'Invalid credentials', null);
                        }
                        
        
    }
    public function getTokenFromRequest(Request $request)
    {
        $token = $request->bearerToken(); // Retrieve token from Authorization header
    
        return $token;
    }
    public function logout(Request $request)
    {
       
try {
    // Attempt to delete tokens for the user
    $token = $this->getTokenFromRequest($request);

    // Check if the token exists
    if (!$token) {
        return new AllResource(false, 'Token not found', 404);
    }
    
    // Log the deleted token before actually deleting it
    try {
        DB::beginTransaction();
    
        // Log the token in the 'deleted_tokens' table
        DB::table('deleted_tokens')->insert([
            'user_id' => $request->user()->id,
            'token' => $token, // Adjust this based on your token structure
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    
        // Delete the token
        // $token->delete();
        $request->user()->tokens()->delete();

        // $user = Auth::user();
        // $user->tokens()->where('token', hash('sha256', $token))->delete();
        DB::commit();
    
        return new PostResource(true, 'Token revoked', $token);
    } catch (\Exception $e) {
        // If an error occurs, rollback the transaction
        DB::rollBack();
    
        // Handle the exception
        return response()->json(['error' => 'Token revocation failed', 'message' => $e->getMessage()], 500);
    }
    // If successful, return a success response
    // return new AllResource(true, 'Tokens revoked', $user);
} catch (\Exception $e) {
    // Log the exception for debugging purposes
    logger()->error('Token revocation failed: ' . $e->getMessage());

    // Determine the appropriate response based on the exception type
    if ($e instanceof \Illuminate\Database\QueryException) {
        // Handle database-related errors
        return response()->json(['error' => 'Database error', 'message' => $e->getMessage()], 500);
    } elseif ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
        // Handle authorization errors
        return response()->json(['error' => 'Authorization error', 'message' => $e->getMessage()], 403);
    } else {
        // Handle other unexpected errors
        return response()->json(['error' => 'Token revocation failed', 'message' => $e->getMessage()], 500);
    }
}
         

        // return response()->json(['message' => 'Tokens revoked']);
    }
  
public function refreshToken(Request $request)
{
    try {
       
    $token = $this->getTokenFromRequest($request);
    
    
    if (!$token) {
        return new AllResource(false, 'Invalid credentials', null);
    }

    $loginTime = Carbon::now(); // Get the current time using Carbon
    $expirationTime = $loginTime->copy()->addHours(2); // Set the expiration time to be 2 hours after login time

    $expirationMilliseconds = $expirationTime->valueOf();
    // return new AllResource(false, 'Invalid credentials', $expirationMilliseconds);

    // Get the current user's access token

    // If the user has an existing access token, create a new one
    if ($token) {
       
        $currentToken= $request->user()->currentAccessToken();

        //testing
        // return new AllResource(false, 'Invalid credentials', $currentToken);

         // Convert milliseconds to seconds (1 second = 1000 milliseconds)
    $expiresInMilliseconds = 7200000; // 2 hours in milliseconds
    $expiresInSeconds = $expiresInMilliseconds / 1000;

    // Calculate the new expires_at based on the provided duration
    $newExpiresAt = now()->addSeconds($expiresInSeconds);
    
        $currentToken->update([
            'expires_at' =>$newExpiresAt,
        ]);
        return new PostResource(true, 'Token refreshed successfully', ['api_token' => $currentToken]);
    } else {
        // If the user doesn't have an existing access token, handle accordingly
        return new AllResource(false, 'Invalid credentials', null);
    }


}catch (\Exception $e) {
    return new AllResource(false, 'Invalid credentials', $e);

}
}}
  
// public function refreshToken(Request $request)
// {
//     try {
       
//     // Assuming you already have a logged-in user
//     $token = $this->getTokenFromRequest($request);
//     // $user = User::where('remember_token', $request->api_token)->first();
//     // $user = User::where('remember_token', $token)->first();
//     return new AllResource(false, 'Invalid credentials', $token);
    
//     if (!$token) {
//         return new AllResource(false, 'Invalid credentials', null);
//     }

//     // Set the login time in the Jakarta time zone
//     $loginTime = now()->setTimezone('Asia/Jakarta');

//     // Set the expiration time to be 2 hours after login time
//     $expirationTime = $loginTime->addHours(2);

//     // Calculate the new expiration time in seconds
//     $expiresIn = $expirationTime->diffInSeconds($loginTime);

//     // Get the current user's access token
//     // $currentAccessToken = $user->remember_token;
//     // return new AllResource(false, 'Invalid credentials', null);

   
//     // Get the current user's access token

//     // If the user has an existing access token, create a new one
//     if ($token) {
//         // Revoke the current access token
//         DB::beginTransaction();
    
//         // Log the token in the 'deleted_tokens' table
//         DB::table('deleted_tokens')->insert([
//             'user_id' => $request->user()->id,
//             'token' => $token, // Adjust this based on your token structure
//             'created_at' => now(),
//             'updated_at' => now(),
//         ]);
//         DB::commit();
//         $request->user()->tokens()->delete();
//         // $user->tokens()->where('token', hash('sha256', $token))->delete();
//         // Create a new access token
//         $newAccessToken = $user->createToken('auth_token', ['expires_in' => $expiresIn])->plainTextToken;
//         // $user->update([
//         //     'remember_token' =>  $newAccessToken
//         //     // 'remember_token' => hash('sha256', $newAccessToken)
//         //     // 'remember_token_expires_at' => $expiresAt,
//         // ]);
//         return new AllResource(true, 'Token refreshed successfully', ['api_token' => $token]);
//     } else {
//         // If the user doesn't have an existing access token, handle accordingly
//         return new AllResource(false, 'Invalid credentials', null);
//     }


// }catch (\Exception $e) {
//     return new AllResource(false, 'Invalid credentials', $e);

// }
// }}