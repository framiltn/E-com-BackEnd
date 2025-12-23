<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class ApiInfoController extends Controller
{
    public function index()
    {
        return response()->json([
            'name' => config('app.name'),
            'version' => '1.0.0',
            'environment' => config('app.env'),
            'endpoints' => [
                'health' => '/api/health',
                'health_detailed' => '/api/health/detailed',
                'documentation' => '/api/documentation',
            ],
            'authentication' => 'Bearer Token (Sanctum)',
            'rate_limits' => [
                'default' => '60 requests per minute',
                'authenticated' => '120 requests per minute',
            ],
        ]);
    }
}
