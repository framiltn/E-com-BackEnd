<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SanitizeInput
{
    public function handle(Request $request, Closure $next)
    {
        $input = $request->all();
        
        array_walk_recursive($input, function (&$value) {
            if (is_string($value)) {
                // Remove potential XSS
                $value = strip_tags($value);
                // Trim whitespace
                $value = trim($value);
            }
        });
        
        $request->merge($input);
        
        return $next($request);
    }
}
