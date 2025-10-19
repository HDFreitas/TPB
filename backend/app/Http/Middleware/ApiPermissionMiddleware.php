<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Spatie\Permission\Traits\HasRoles;
use App\Modules\Plataforma\Models\User;

class ApiPermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        /** @var User|null $user */
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'error' => 'Usuário não autenticado.'
            ], 401);
        }
        
        // Garantir que o contexto de team está definido
        if ($user->tenant_id) {
            setPermissionsTeamId($user->tenant_id);
        }
        
        // Verificar permissão usando o método do Spatie Permission
        if (!$user->can($permission)) {
            return response()->json([
                'error' => 'Acesso negado. Você não tem permissão para realizar esta ação.',
                'required_permission' => $permission
            ], 403);
        }
        
        return $next($request);
    }
}
