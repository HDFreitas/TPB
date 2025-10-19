<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\JWTException;

class JwtAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            if (!$token = $request->cookie('access_token')) {
                throw new JWTException('Token absent');
            }
 
            $user = JWTAuth::setToken($token)->toUser();
            if($user == null){
                throw new Exception('Authorization error');
            }
            
            // Registrar usuário no guard 'api' para integração com Laravel Guards
            Auth::guard('api')->setUser($user);
            
            // Definir o contexto de team (tenant) para o Spatie Permission
            if ($user && $user->tenant_id) {
                setPermissionsTeamId($user->tenant_id);
            }
        } catch (Exception $e) {
            if ($e instanceof TokenInvalidException) {
                return response()->json(['status' => 'error', 'message' => 'Token is invalid'], 401);
            } elseif ($e instanceof TokenExpiredException) {
                return response()->json(['status' => 'error', 'message' => 'Token has expired'], 401);
            } elseif ($e instanceof JWTException) {
                return response()->json(['status' => 'error', 'message' => 'Token absent'], 401);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Authorization error'], 500);
            }
        }

        return $next($request);
    }
}