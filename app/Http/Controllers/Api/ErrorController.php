<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ErrorController extends Controller
{
    //
    public function notFound()
    {
        return response()->json([
            'success' => false,
            'message' => 'The requested resource was not found.'
        ], 404);
    }
}
