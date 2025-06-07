<?php

namespace App\Http\Middleware;

use App\Models\AppUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;


class CheckBearerToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
         /***
        // Check if the request has the Authorization header
        if (!$request->hasHeader('Authorization')) {
            return response()->json(['message' => 'Bearer token is missing'], 401);
        }

        // Get the value of the Authorization header
        $token = $request->header('Authorization');

        // Check if the header starts with "Bearer "
        if (!str_starts_with($token, 'Bearer ')) {
            return response()->json(['message' => 'Invalid token format'], 401);
        }

        // Extract the token without "Bearer "
        $token = substr($token, 7);
        **/
        //OR

        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'error' => 'Missing Bearer token in Authorization header',
                'status_code' => 401
            ], 200);
        }

        // Validate the token using Sanctum's authentication
        // if (!auth()->guard('api')->user()) {
        //     return response()->json(['message' => 'Unauthorized, Please provide login to obtain valid access_token. '], 401);
        // }
        // OR
        // Validate the token using the already stored access_token
        $safiriUser = AppUser::where('access_token', $token)->first();

        if (!$safiriUser) {
            return response()->json([
                'error' =>' Bearer token does not match.',
                'message' => 'Unauthorized. Please provide login again.',
                'status_code' => 401
            ], 200);
        }

        return $next($request);
    }
}
