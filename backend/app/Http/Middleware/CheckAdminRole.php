<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verifica se o usuário está autenticado
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado.'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = auth()->user();

        // Definir o contexto de team (tenant) para o Spatie
        setPermissionsTeamId($user->tenant_id);
        
        // Recarregar o usuário com o contexto correto
        $user = $user->fresh(['roles', 'permissions']);

        // Verifica se o usuário tem a role 'Administrador' ou 'HUB'
        if (!$user->hasAnyRole(['Administrador', 'HUB'])) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso negado. Apenas usuários com role Administrador ou HUB podem gerenciar roles de outros usuários.',
                'required_roles' => ['Administrador', 'HUB'],
                'user_roles' => $user->getRoleNames()
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
