<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Helpers\ResponseHelper;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckIfApiRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('api/*')) {
            // If it's an API request, proceed with the request
            return $next($request);
        }

        // If it's not an API request, you can return a custom error response
        // return response()->json(['msg' => 'This is not an API request.'], 400);
        return ResponseHelper::errorResponse(['This is not an API request.',400]);
    }
}
