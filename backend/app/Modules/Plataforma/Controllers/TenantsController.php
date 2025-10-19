<?php

namespace App\Modules\Plataforma\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Plataforma\Interfaces\TenantRepositoryInterface;
use App\Modules\Plataforma\Services\TenantService;
use App\Services\LogService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class TenantsController extends Controller
{
    protected $tenantRepository;
    protected $tenantService;
    protected $logService;

    public function __construct(
        TenantRepositoryInterface $tenantRepository,
        TenantService $tenantService,
        LogService $logService
    )
    {
        $this->tenantRepository = $tenantRepository;
        $this->tenantService = $tenantService;
        $this->logService = $logService;
        
        // Middleware set.permission.team é aplicado globalmente
        
        // Aplicar middlewares de permissão específicas para cada método usando guard api
        $this->middleware('api.permission:tenants.visualizar')->only(['index', 'show']);
        $this->middleware('api.permission:tenants.criar')->only(['store']);
        $this->middleware('api.permission:tenants.editar')->only(['update']);
        $this->middleware('api.permission:tenants.excluir')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $query = \App\Modules\Plataforma\Models\Tenant::query();

        // Filtro por nome
        if ($request->filled('nome')) {
            $query->where('nome', 'ilike', '%' . $request->input('nome') . '%');
        }

        // Filtro por status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $tenants = $query->paginate($perPage);
        return response()->json($tenants);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255|unique:tenants,nome',
            'status' => 'required|boolean',
            'dominio' => 'required|string|max:50|unique:tenants,dominio',
            'descricao' => 'nullable|string',
            // Campos opcionais para criar usuário administrador
            'admin_user.name' => 'nullable|string|max:255',
            'admin_user.email' => 'nullable|email|max:255|unique:users,email',
            'admin_user.password' => 'nullable|string|min:8',
            'admin_user.usuario' => 'nullable|string|max:255|unique:users,usuario',
            'admin_user.dominio' => 'nullable|string|max:255',
        ], [
            'nome.unique' => 'Já existe um tenant com este nome.',
            'dominio.unique' => 'Já existe um tenant com este domínio.',
            'nome.required' => 'O nome é obrigatório.',
            'dominio.required' => 'O domínio é obrigatório.',
            'dominio.regex' => 'O domínio deve conter apenas letras minúsculas.',
        ]);

        // Separar dados do tenant e do usuário admin
        $tenantData = [
            'nome' => $validated['nome'],
            'status' => $validated['status'],
            'dominio' => $validated['dominio'],
            'descricao' => $validated['descricao'] ?? null,
        ];

        $adminUserData = $request->input('admin_user');

        try {
            // Usar o service para criar tenant com perfis e usuário admin
            $tenant = $this->tenantService->createTenantWithDefaults($tenantData, $adminUserData);
            
            // Log de criação com sucesso
            try {
                $this->logService->logCrud('create', 'tenant', $tenant->id, [
                    'nome' => $tenant->nome,
                    'dominio' => $tenant->dominio,
                    'status' => $tenant->status
                ]);
            } catch (\Exception $e) {
                \Log::error('Erro ao criar log: ' . $e->getMessage());
            }
            
            return response()->json($tenant, Response::HTTP_CREATED);
            
        } catch (\Exception $e) {
            // Log de erro
            try {
                $this->logService->logError('create_tenant', 
                    'Falha ao criar tenant: ' . $e->getMessage(),
                    ['dados' => $tenantData, 'erro' => $e->getMessage()]
                );
            } catch (\Exception $logError) {
                \Log::error('Erro ao criar log de erro: ' . $logError->getMessage());
            }
            
            return response()->json([
                'error' => 'Erro ao criar tenant',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show($id)
    {
        $tenant = $this->tenantRepository->findById($id);
        return response()->json($tenant);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nome' => 'sometimes|string|max:255|unique:tenants,nome,' . $id,
            'status' => 'sometimes|boolean',
            'dominio' => 'sometimes|string|max:50|unique:tenants,dominio,' . $id,
            'descricao' => 'nullable|string',
        ], [
            'nome.unique' => 'Já existe um tenant com este nome.',
            'dominio.unique' => 'Já existe um tenant com este domínio.',
            'dominio.regex' => 'O domínio deve conter apenas letras minúsculas.',
        ]);
        
        try {
            // Usar o service para atualizar e disparar eventos se necessário
            $tenant = $this->tenantService->updateTenant($id, $validated);
            
            // Log de atualização com sucesso
            try {
                $this->logService->logCrud('update', 'tenant', $tenant->id, [
                    'campos_alterados' => array_keys($validated),
                    'valores' => $validated
                ]);
            } catch (\Exception $e) {
                \Log::error('Erro ao criar log: ' . $e->getMessage());
            }
            
            return response()->json($tenant);
            
        } catch (\Exception $e) {
            // Log de erro
            try {
                $this->logService->logError('update_tenant',
                    'Falha ao atualizar tenant: ' . $e->getMessage(),
                    ['tenant_id' => $id, 'dados' => $validated, 'erro' => $e->getMessage()]
                );
            } catch (\Exception $logError) {
                \Log::error('Erro ao criar log de erro: ' . $logError->getMessage());
            }
            
            return response()->json([
                'error' => 'Erro ao atualizar tenant',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy($id)
    {
        try {
            // Buscar tenant antes de excluir para o log
            $tenant = $this->tenantRepository->findById($id);
            
            if (!$tenant) {
                return response()->json(['error' => 'Tenant não encontrado'], Response::HTTP_NOT_FOUND);
            }
            
            $tenantData = [
                'nome' => $tenant->nome,
                'dominio' => $tenant->dominio
            ];
            
            $this->tenantRepository->delete($id);
            
            // Log de exclusão com sucesso
            try {
                $this->logService->logCrud('delete', 'tenant', $id, $tenantData);
            } catch (\Exception $e) {
                \Log::error('Erro ao criar log: ' . $e->getMessage());
            }
            
            return response()->json(null, Response::HTTP_NO_CONTENT);
            
        } catch (\Exception $e) {
            // Log de erro
            try {
                $this->logService->logError('delete_tenant',
                    'Falha ao excluir tenant: ' . $e->getMessage(),
                    ['tenant_id' => $id, 'erro' => $e->getMessage()]
                );
            } catch (\Exception $logError) {
                \Log::error('Erro ao criar log de erro: ' . $logError->getMessage());
            }
            
            return response()->json([
                'error' => 'Erro ao excluir tenant',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
} 