<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Traits\HasRoles;

class CheckRole
{
    /**
     * The authenticated user.
     *
     * @var \App\Models\User
     */
    private $user;

    /**
     * Create a new middleware instance.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function __construct(Request $request)
    {
        $this->user = Auth::user();
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles
     * @return mixed
     */
    public function handle($request, Closure $next, ...$roles)
    {
        if (!$this->user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $userRoles = $this->user->roles->pluck('name')->toArray();

        foreach ($roles as $role) {
            // Split the roles using the '|' symbol
            $roleNames = explode('|', $role);

            // Check if the user has at least one of the specified roles
            if (count(array_intersect($userRoles, $roleNames)) > 0) {
                return $next($request);
            }
        }

        return response()->json(['error' => 'Unauthorized for resource'], 403);
    }
    
}
