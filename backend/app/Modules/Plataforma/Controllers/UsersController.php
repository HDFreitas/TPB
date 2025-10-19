<?php

namespace App\Modules\Plataforma\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Plataforma\Requests\StoreUserRequest;
use App\Modules\Plataforma\Requests\UpdateUserRequest;
use App\Http\Resources\V1\UserResource;
use App\Modules\Plataforma\Interfaces\UserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\Services\LogService;

class UsersController extends Controller
{
    protected $userRepository;
    protected $logService;

    public function __construct(
        UserRepositoryInterface $userRepository,
        LogService $logService
    ) {
        $this->userRepository = $userRepository;
        $this->logService = $logService;
        
        // Middleware set.permission.team é aplicado globalmente
        
        // Aplicar middlewares de permissão específicas para cada método
        $this->middleware('api.permission:usuarios.visualizar')->only(['index', 'show']);
        $this->middleware('api.permission:usuarios.criar')->only(['store']);
        $this->middleware('api.permission:usuarios.editar')->only(['update']);
        $this->middleware('api.permission:usuarios.excluir')->only(['destroy']);
    }

    /**
     * Display a listing of users.
     *
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        
        // Filtrar usuários apenas do tenant do usuário autenticado
        $authenticatedUser = Auth::user();
        $users = $this->userRepository->getByTenantId($authenticatedUser->tenant_id, $perPage);
        
        return UserResource::collection($users);
    }

    /**
     * Search users with filters
     *
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function search(Request $request)
    {
        $filters = $request->all();
        $perPage = $request->input('per_page', 15);
        
        // Filtrar usuários apenas do tenant do usuário autenticado
        $authenticatedUser = Auth::user();
        $users = $this->userRepository->searchByTenantId(
            $authenticatedUser->tenant_id, 
            $filters,
            $perPage
        );
        
        return UserResource::collection($users);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/users",
     *     summary="Criar novo usuário",
     *     tags={"Users"},
     *     security={{"cookieAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password", "usuario", "dominio"},
     *             @OA\Property(property="name", type="string", example="João Silva", description="Nome completo do usuário"),
     *             @OA\Property(property="email", type="string", format="email", example="joao@exemplo.com", description="Email do usuário"),
     *             @OA\Property(property="password", type="string", format="password", example="senha123", description="Senha do usuário (mínimo 8 caracteres)"),
     *             @OA\Property(property="usuario", type="string", example="joao.silva", description="Nome de usuário único"),
     *             @OA\Property(property="dominio", type="string", example="dominioteste", description="Domínio do usuário")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Usuário criado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/User")
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
     * 
     * Store a newly created user.
     *
     * @param StoreUserRequest $request
     * @return UserResource
     */
    public function store(StoreUserRequest $request)
    {
        $userData = $request->validated();
        
        // Pega o tenant_id e domínio do usuário autenticado
        $authenticatedUser = Auth::user();
        $userData['tenant_id'] = $authenticatedUser->tenant_id;
        $userData['dominio'] = $authenticatedUser->tenant->dominio; // Sempre usar domínio do tenant
        
        // Criptografa a senha
        $userData['password'] = bcrypt($userData['password']);
        
        $user = $this->userRepository->create($userData);
        
        // Log de criação
        try {
            $this->logService->logCrud('create', 'usuario', $user->id, [
                'name' => $user->name,
                'usuario' => $user->usuario
            ]);
        } catch (\Exception $e) {
            \Log::error('Erro ao criar log: ' . $e->getMessage());
        }
        
        return (new UserResource($user))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Display the specified user.
     *
     * @param int $id
     * @return UserResource
     */
    public function show($id)
    {
        $authenticatedUser = Auth::user();
        $user = $this->userRepository->findById($id);
        
        // Verificar se o usuário pertence ao mesmo tenant
        if ($user->tenant_id !== $authenticatedUser->tenant_id) {
            abort(403, 'Você não tem permissão para visualizar este usuário.');
        }
        
        return new UserResource($user);
    }

    /**
     * Update the specified user.
     *
     * @param UpdateUserRequest $request
     * @param int $id
     * @return UserResource
     */
    public function update(UpdateUserRequest $request, $id)
    {
        $authenticatedUser = Auth::user();
        $user = $this->userRepository->findById($id);
        
        // Verificar se o usuário pertence ao mesmo tenant
        if ($user->tenant_id !== $authenticatedUser->tenant_id) {
            abort(403, 'Você não tem permissão para editar este usuário.');
        }
        
        $userData = $request->validated();
        
        // Garantir que o tenant_id e domínio não sejam alterados
        unset($userData['tenant_id']);
        $userData['dominio'] = $authenticatedUser->tenant->dominio; // Sempre usar domínio do tenant
        
        // Criptografar senha se fornecida
        if (isset($userData['password']) && !empty($userData['password'])) {
            $userData['password'] = bcrypt($userData['password']);
        } else {
            // Remove senha se estiver vazia (não alterar)
            unset($userData['password']);
        }
        
        $user = $this->userRepository->update($id, $userData);
        
        // Log de atualização (sem dados sensíveis - LGPD)
        try {
            $this->logService->logCrud('update', 'usuario', $user->id, [
                'name' => $user->name,
                'usuario' => $user->usuario,
                'changes' => array_keys($userData)
                // valores: não incluídos para evitar exposição de dados sensíveis (email, senha, etc)
            ]);
        } catch (\Exception $e) {
            \Log::error('Erro ao criar log: ' . $e->getMessage());
        }
        
        return new UserResource($user);
    }

    /**
     * Remove the specified user.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $authenticatedUser = Auth::user();
        $user = $this->userRepository->findById($id);
        
        // Verificar se o usuário pertence ao mesmo tenant
        if ($user->tenant_id !== $authenticatedUser->tenant_id) {
            abort(403, 'Você não tem permissão para excluir este usuário.');
        }
        
        // Guardar dados antes de deletar
        $userName = $user->name;
        $userUsuario = $user->usuario;
        
        $this->userRepository->delete($id);
        
        // Log de exclusão
        try {
            $this->logService->logCrud('delete', 'usuario', $id, [
                'name' => $userName,
                'usuario' => $userUsuario
            ]);
        } catch (\Exception $e) {
            \Log::error('Erro ao criar log: ' . $e->getMessage());
        }
        
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}