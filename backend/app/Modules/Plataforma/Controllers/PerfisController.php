<?php
namespace App\Modules\Plataforma\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Plataforma\Interfaces\PerfilRepositoryInterface;
use App\Modules\Plataforma\Models\User;
use App\Services\LogService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class PerfisController extends Controller
{
    protected $perfilRepository;
    protected $logService;

    public function __construct(
        PerfilRepositoryInterface $perfilRepository,
        LogService $logService
    )
    {
        $this->perfilRepository = $perfilRepository;
        $this->logService = $logService;
        
        // Aplicar middleware para definir contexto do tenant antes das verificações de permissão
        $this->middleware('set.permission.team');
        
        // Aplicar middlewares de permissão específicas para cada método
        $this->middleware('permission:perfis.visualizar')->only(['index', 'show', 'getUsers', 'getPermissions']);
        $this->middleware('permission:perfis.criar')->only(['store']);
        $this->middleware('permission:perfis.editar')->only(['update', 'associateUsers', 'removeUser', 'syncPermissions']);
        $this->middleware('permission:perfis.excluir')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        
        // Usar sempre o tenant_id do usuário logado
        /** @var User $authenticatedUser */
        $authenticatedUser = Auth::user();
        $tenantId = $authenticatedUser->tenant_id;
        
        // Verificar se o usuário tem permissão para gerenciar perfis HUB
        $canSeeHubRole = $authenticatedUser->can('perfis.gerenciar_hub');

        // Filtros de pesquisa
        $filters = [
            'search' => $request->input('search'),
            'status' => $request->input('status')
        ];

        $perfis = $this->perfilRepository->getAll($perPage, true, $tenantId, $canSeeHubRole, $filters);

        return response()->json($perfis);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);
        
        // Usar sempre o tenant_id do usuário logado
        /** @var User $authenticatedUser */
        $authenticatedUser = Auth::user();
        $validated['tenant_id'] = $authenticatedUser->tenant_id;
        
        // Verificar se está tentando criar perfil HUB sem permissão
        if (strtoupper($validated['name']) === 'HUB' && !$authenticatedUser->can('perfis.gerenciar_hub')) {
            return response()->json([
                'message' => 'Acesso negado. Apenas usuários com permissão podem criar perfis HUB.'
            ], Response::HTTP_FORBIDDEN);
        }
        
        try {
            $perfil = $this->perfilRepository->create($validated);
            
            // Log de criação com sucesso
            try {
                $this->logService->logCrud('create', 'perfil', $perfil->id, [
                    'name' => $perfil->name,
                    'tenant_id' => $perfil->tenant_id
                ]);
            } catch (\Exception $e) {
                \Log::error('Erro ao criar log: ' . $e->getMessage());
            }
            
            return response()->json($perfil, Response::HTTP_CREATED);
            
        } catch (\Exception $e) {
            // Log de erro
            try {
                $this->logService->logError('create_perfil',
                    'Falha ao criar perfil: ' . $e->getMessage(),
                    ['dados' => $validated, 'erro' => $e->getMessage()]
                );
            } catch (\Exception $logError) {
                \Log::error('Erro ao criar log de erro: ' . $logError->getMessage());
            }
            
            return response()->json([
                'error' => 'Erro ao criar perfil',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show($id)
    {
        $perfil = $this->perfilRepository->findById($id);
        return response()->json($perfil);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name'        => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:255',
            'status'      => 'sometimes|boolean',
        ]);
        
        // Garantir que só pode editar perfis do próprio tenant
        /** @var User $authenticatedUser */
        $authenticatedUser = Auth::user();
        $validated['tenant_id'] = $authenticatedUser->tenant_id;
        
        // Verificar se está tentando editar para perfil HUB sem permissão
        if (isset($validated['name']) && strtoupper($validated['name']) === 'HUB' && !$authenticatedUser->can('perfis.gerenciar_hub')) {
            return response()->json([
                'message' => 'Acesso negado. Apenas usuários com permissão podem editar perfis HUB.'
            ], Response::HTTP_FORBIDDEN);
        }
        
        // Verificar se está tentando editar um perfil HUB existente sem permissão
        $currentPerfil = $this->perfilRepository->findById($id);
        if ($currentPerfil && strtoupper($currentPerfil->name) === 'HUB' && !$authenticatedUser->can('perfis.gerenciar_hub')) {
            return response()->json([
                'message' => 'Acesso negado. Apenas usuários com permissão podem editar perfis HUB.'
            ], Response::HTTP_FORBIDDEN);
        }

        // RF-003: Os perfis HUB e Administrador não devem permitir alterações no que tange a nome e descrição
        if ($currentPerfil && in_array(strtoupper($currentPerfil->name), ['HUB', 'ADMINISTRADOR'])) {
            if (isset($validated['name']) || isset($validated['description'])) {
                return response()->json([
                    'message' => 'Perfis HUB e Administrador não permitem alterações de nome e descrição.'
                ], Response::HTTP_FORBIDDEN);
            }
            // Para perfis especiais, remover name e description do validated
            unset($validated['name']);
            unset($validated['description']);
        }

        // RF-003: O perfil HUB não permite alteração de status (apenas Administrador permite)
        if ($currentPerfil && strtoupper($currentPerfil->name) === 'HUB') {
            if (isset($validated['status'])) {
                return response()->json([
                    'message' => 'O perfil HUB não permite alteração de status. Apenas vinculação de usuários é permitida.'
                ], Response::HTTP_FORBIDDEN);
            }
        }

        // RF-003: O tenant do perfil nunca pode ser alterado
        unset($validated['tenant_id']);
        
        try {
            $perfil = $this->perfilRepository->update($id, $validated);
            
            // Log de atualização com sucesso
            try {
                $this->logService->logCrud('update', 'perfil', $perfil->id, [
                    'campos_alterados' => array_keys($validated),
                    'valores' => $validated
                ]);
            } catch (\Exception $e) {
                \Log::error('Erro ao criar log: ' . $e->getMessage());
            }
            
            return response()->json($perfil);
            
        } catch (\Exception $e) {
            // Log de erro
            try {
                $this->logService->logError('update_perfil',
                    'Falha ao atualizar perfil: ' . $e->getMessage(),
                    ['perfil_id' => $id, 'dados' => $validated, 'erro' => $e->getMessage()]
                );
            } catch (\Exception $logError) {
                \Log::error('Erro ao criar log de erro: ' . $logError->getMessage());
            }
            
            return response()->json([
                'error' => 'Erro ao atualizar perfil',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy($id)
    {
        /** @var User $authenticatedUser */
        $authenticatedUser = Auth::user();
        
        // Verificar se está tentando deletar um perfil HUB sem permissão
        $perfil = $this->perfilRepository->findById($id);
        if ($perfil && strtoupper($perfil->name) === 'HUB' && !$authenticatedUser->can('perfis.gerenciar_hub')) {
            return response()->json([
                'message' => 'Acesso negado. Apenas usuários com permissão podem deletar perfis HUB.'
            ], Response::HTTP_FORBIDDEN);
        }
        
        try {
            if (!$perfil) {
                return response()->json(['error' => 'Perfil não encontrado'], Response::HTTP_NOT_FOUND);
            }
            
            $perfilData = [
                'name' => $perfil->name,
                'tenant_id' => $perfil->tenant_id
            ];
            
            $this->perfilRepository->delete($id);
            
            // Log de exclusão com sucesso
            try {
                $this->logService->logCrud('delete', 'perfil', $id, $perfilData);
            } catch (\Exception $e) {
                \Log::error('Erro ao criar log: ' . $e->getMessage());
            }
            
            return response()->json(null, Response::HTTP_NO_CONTENT);
            
        } catch (\Exception $e) {
            // Log de erro
            try {
                $this->logService->logError('delete_perfil',
                    'Falha ao excluir perfil: ' . $e->getMessage(),
                    ['perfil_id' => $id, 'erro' => $e->getMessage()]
                );
            } catch (\Exception $logError) {
                \Log::error('Erro ao criar log de erro: ' . $logError->getMessage());
            }
            
            return response()->json([
                'error' => 'Erro ao excluir perfil',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Obter usuários associados e disponíveis para um perfil
     */
    public function getUsers($id)
    {
        $authenticatedUser = Auth::user();
        $tenantId = $authenticatedUser->tenant_id;

        try {
            $usersData = $this->perfilRepository->getPerfilUsersData($id, $tenantId);
            return response()->json($usersData);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao buscar usuários do perfil.',
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Associar usuários a um perfil
     */
    public function associateUsers(Request $request, $id)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'integer|exists:users,id'
        ]);

        /** @var User $authenticatedUser */
        $authenticatedUser = Auth::user();
        $tenantId = $authenticatedUser->tenant_id;

        try {
            // Verificar se está tentando modificar perfil HUB sem permissão
            $perfil = $this->perfilRepository->findById($id);
            if ($perfil && strtoupper($perfil->name) === 'HUB' && !$authenticatedUser->can('perfis.gerenciar_hub')) {
                return response()->json([
                    'message' => 'Acesso negado. Apenas usuários com permissão podem gerenciar perfis HUB.'
                ], Response::HTTP_FORBIDDEN);
            }

            $this->perfilRepository->associateUsers($id, $validated['user_ids'], $tenantId);
            
            return response()->json([
                'message' => 'Usuários associados ao perfil com sucesso.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao associar usuários ao perfil.',
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Remover usuário de um perfil
     */
    public function removeUser($id, $userId)
    {
        /** @var User $authenticatedUser */
        $authenticatedUser = Auth::user();
        $tenantId = $authenticatedUser->tenant_id;

        try {
            // Verificar se está tentando modificar perfil HUB sem permissão
            $perfil = $this->perfilRepository->findById($id);
            if ($perfil && strtoupper($perfil->name) === 'HUB' && !$authenticatedUser->can('perfis.gerenciar_hub')) {
                return response()->json([
                    'message' => 'Acesso negado. Apenas usuários com permissão podem gerenciar perfis HUB.'
                ], Response::HTTP_FORBIDDEN);
            }

            $this->perfilRepository->removeUserFromPerfil($id, $userId, $tenantId);
            
            return response()->json([
                'message' => 'Usuário removido do perfil com sucesso.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao remover usuário do perfil.',
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Obter permissões de um perfil
     */
    public function getPermissions($id)
    {
        $authenticatedUser = Auth::user();
        $tenantId = $authenticatedUser->tenant_id;

        try {
            $permissionsData = $this->perfilRepository->getPerfilPermissions($id, $tenantId);
            return response()->json($permissionsData);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao buscar permissões do perfil.',
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Sincronizar permissões de um perfil
     */
    public function syncPermissions(Request $request, $id)
    {
        $validated = $request->validate([
            'permission_ids' => 'required|array',
            'permission_ids.*' => 'integer|exists:permissions,id'
        ]);

        $authenticatedUser = Auth::user();
        $tenantId = $authenticatedUser->tenant_id;

        try {
            // Verificar se está tentando modificar permissões de perfis especiais
            $perfil = $this->perfilRepository->findById($id);
            if ($perfil && in_array(strtoupper($perfil->name), ['HUB', 'ADMINISTRADOR'])) {
                return response()->json([
                    'message' => 'Perfis HUB e Administrador têm permissões fixas e não podem ser alteradas.'
                ], Response::HTTP_FORBIDDEN);
            }

            $this->perfilRepository->syncPerfilPermissions($id, $validated['permission_ids'], $tenantId);
            
            return response()->json([
                'message' => 'Permissões do perfil atualizadas com sucesso.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao atualizar permissões do perfil.',
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}