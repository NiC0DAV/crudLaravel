<?php

namespace App\Http\Middleware;

use App\Helpers\JwtAuth;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next){
        $tokenAuth = $request->header('Authorization');
        $jwtValidator = new JwtAuth();
        $checkToken = $jwtValidator->checkToken($tokenAuth);
        if ($checkToken === true) {
            return $next($request);
        } else {
            $data = array(
                'code' => "403",
                'message' => $checkToken
            );
            return response()->json($data, $data['code']);
        }
    }

}
