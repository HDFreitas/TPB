<?php

namespace App\Modules\Plataforma\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Modules\Plataforma\Interfaces\UserRepositoryInterface;
use App\Modules\Plataforma\Requests\RegisterUserRequest;
use App\Modules\Plataforma\Requests\LoginUserRequest;
use App\Http\Resources\V1\UserResource;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

/**
 * @OA\Tag(
 *     name="Auth",
 *     description="API Endpoints de autenticação"
 * )
 */
class AuthController extends Controller
{
    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/register",
     *     summary="Registrar novo usuário",
     *     tags={"Auth"},
     *     security={{"cookieAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/RegisterUserRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Usuário registrado com sucesso",
     *         @OA\Header(
     *             header="Set-Cookie",
     *             description="Define o cookie 'access_token' HttpOnly para a sessão.",
     *             @OA\Schema(type="string", example="access_token=eyJ...; expires=...; path=/; HttpOnly; SameSite=lax")
     *         ),
     *         @OA\JsonContent(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/SuccessResponse"),
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="data",
     *                         type="object",
     *                         @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
     *                         @OA\Property(property="user", ref="#/components/schemas/User")
     *                     )
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autorizado - Token inválido ou ausente",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function register(RegisterUserRequest $request)
    {
        $validatedData = $request->validated();
        
        // Pega o tenant_id do usuário autenticado
        $authenticatedUser = Auth::user();
        
        $user = $this->userRepository->create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => bcrypt($validatedData['password']),
            'tenant_id' => $authenticatedUser->tenant_id,
            'usuario' => $validatedData['usuario'],
            'dominio' => $validatedData['dominio']
        ]);

        try {
            $token = JWTAuth::fromUser($user);
        } catch (JWTException $e) {
            return $this->errorResponse(
                'Could not create token',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return $this->successResponse(
            'Usuário registrado com sucesso',
            Response::HTTP_CREATED,
            [
                'user' => new UserResource($user),
            ]
        )->withCookie($this->createTokenCookie($token));
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/login",
     *     summary="Realizar login",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/LoginUserRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login realizado com sucesso",
     *         @OA\Header(
     *             header="Set-Cookie",
     *             description="Define o cookie 'access_token' HttpOnly para a sessão.",
     *             @OA\Schema(type="string")
     *         ),
     *         @OA\JsonContent(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/SuccessResponse"),
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="data",
     *                         type="object",
     *                         @OA\Property(property="user", ref="#/components/schemas/User")
     *                     )
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Credenciais inválidas",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function login(LoginUserRequest $request)
    {
        $validatedData = $request->validated();
        
        // Extrair usuário e domínio do login (formato: usuario@dominio.com.br)
        $loginParts = explode('@', $validatedData['login']);
        if (count($loginParts) !== 2) {
            return $this->errorResponse(
                'Formato de login inválido. Use: usuario@dominio.com.br',
                Response::HTTP_BAD_REQUEST
            );
        }
        
        $usuario = $loginParts[0];
        $dominioComBr = $loginParts[1]; // "dominio.com.br"
        
        // Remover ".com.br" para buscar no banco
        $dominio = str_replace('.com.br', '', $dominioComBr);
        
        $credentials = [
            'password' => $validatedData['password'],
        ];

        // Primeiro verificar se o usuário existe e está ativo
        $user = $this->userRepository->findByUsuarioAndDominio($usuario, $dominio);
        
        if (!$user) {
            return $this->errorResponse(
                'Credenciais inválidas. Verifique os dados informados.',
                Response::HTTP_UNAUTHORIZED
            );
        }
        
        // Verificar se o usuário está ativo
        if (!$user->status) {
            return $this->errorResponse(
                'Usuário inativo. Entre em contato com o administrador.',
                Response::HTTP_FORBIDDEN
            );
        }
        
        // Verificar se o tenant do usuário está ativo
        if (!$user->tenant || !$user->tenant->status) {
            return $this->errorResponse(
                'Tenant inativo. Entre em contato com o administrador.',
                Response::HTTP_FORBIDDEN
            );
        }
        
        try {
            // Verificar senha manualmente já que temos o usuário
            if (!Hash::check($credentials['password'], $user->password)) {
                return $this->errorResponse(
                    'Credenciais inválidas. Verifique os dados informados.',
                    Response::HTTP_UNAUTHORIZED
                );
            }
            
            // Gerar token JWT para o usuário autenticado
            $token = JWTAuth::fromUser($user);
        } catch (JWTException $e) {
            return $this->errorResponse(
                'Erro interno do servidor. Tente novamente.',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        // Definir o contexto de team (tenant) para o Spatie Permission
        setPermissionsTeamId($user->tenant_id);
        
        // Recarregar o usuário com roles e permissões no contexto correto
        $user = $user->fresh(['roles', 'permissions']);
        
        return $this->successResponse(
            'Login realizado com sucesso',
            Response::HTTP_OK,
            [ 
                'user' => new UserResource($user),
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => config('jwt.ttl') * 60
            ]
        )->withCookie($this->createTokenCookie($token));
    }
    
    /**
     * @OA\Post(
     *     path="/api/v1/auth/logout",
     *     summary="Realizar logout",
     *     tags={"Auth"},
     *     security={{"cookieAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logout realizado com sucesso",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autorizado",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function logout(Request $request)
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());

            return $this->successResponse(
                'Successfully logged out',
                Response::HTTP_OK
            )->withCookie(cookie()->forget('access_token'));
        } catch (JWTException $e) {
            return $this->errorResponse(
                'Could not log out',
                Response::HTTP_INTERNAL_SERVER_ERROR
            )->withCookie(cookie()->forget('access_token'));
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/refresh",
     *     summary="Renovar token",
     *     tags={"Auth"},
     *     security={{"cookieAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Token renovado com sucesso",
     *         @OA\JsonContent(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/SuccessResponse"),
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="data",
     *                         type="object",
     *                         @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...")
     *                     )
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autorizado",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function refresh(Request $request)
    {
        try {
            $token = JWTAuth::refresh(JWTAuth::getToken());
            return $this->successResponse(
                'Token refreshed successfully',
                Response::HTTP_OK,
                ['token' => $token]
            );
        } catch (JWTException $e) {
            return $this->errorResponse(
                'Could not refresh token',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/auth/me",
     *     summary="Validar sessão do usuário",
     *     tags={"Auth"},
     *     security={{"cookieAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Cliente autenticado com sucesso!",
     *         @OA\JsonContent(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/SuccessResponse"),
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autorizado",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function me()
    {
        $user = Auth::user();
        
        // Definir o contexto de team (tenant) para o Spatie Permission
        setPermissionsTeamId($user->tenant_id);
        
        // Recarregar o usuário com roles e permissões no contexto correto
        $user = $user->fresh(['roles', 'permissions']);
        
        return $this->successResponse(
            'Cliente autenticado com sucesso!',
            Response::HTTP_OK,
            [ 
                'user' => [
                    'id' => $user->id,
                    'tenant_id' => $user->tenant_id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'usuario' => $user->usuario,
                    'dominio' => $user->dominio,
                    'roles' => $user->getRoleNames()->toArray(),
                    'permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ]
            ],
        );
    }

    /**
     * Helper para criar o cookie do access token.
     * @param string $token
     * @return \Symfony\Component\HttpFoundation\Cookie
     */
    protected function createTokenCookie(string $token)
    {
        return cookie(
            'access_token',
            $token,
            config('jwt.ttl'),
            '/',
            config('session.domain'),
            config('session.secure', false),
            true, // httpOnly
            false,
            config('session.same_site', 'lax')
        );
    }
}