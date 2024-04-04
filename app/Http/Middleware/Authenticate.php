<?php

namespace App\Http\Middleware;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('unauthorized');
    }
    // protected function redirectTo($request): ?string
    // {
    //     if (!$request->expectsJson()) {
    //         throw new AuthenticationException(
    //             'Unauthenticated.',
    //             $this->guards($request), // Guards
    //             $this->redirectTo($request) // Previous URL
    //         );
    //     }

    //     return null;
    // }
    //  /**
    //  * Get the guards from the request.
    //  *
    //  * @param  \Illuminate\Http\Request  $request
    //  * @return array
    //  */
    // protected function guards($request): array
    // {
    //     return array_filter([
    //         'web' => Auth::guard('web')->check(),
    //         'api' => Auth::guard('api')->check(),
    //     ]);
    // }
}
