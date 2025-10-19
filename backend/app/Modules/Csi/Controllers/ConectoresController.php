<?php

namespace App\Modules\Csi\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Csi\Services\ConectorService;
use App\Modules\Csi\Requests\UpdateConectorRequest;
use App\Services\LogService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class ConectoresController extends Controller
{
    protected $conectorService;
    protected $logService;

    public function __construct(
        ConectorService $conectorService,
        LogService $logService
    )
    {
        $this->conectorService = $conectorService;
        $this->logService = $logService;
        
        // Aplicar middleware para definir contexto do tenant antes das verificações de permissão
        $this->middleware('set.permission.team');
        
        // Aplicar middlewares de permissão específicas para cada método
        $this->middleware('permission:conectores.visualizar')->only(['index', 'show', 'byTenant', 'byCodigo', 'byCodigoAndTenant', 'ativos']);
        $this->middleware('permission:conectores.editar')->only(['update']);
        $this->middleware('permission:conectores.testar')->only(['testConnection']);
    }

    /**
     * Listar todos os conectores
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $tenantId = $request->input('tenant_id');
        
        $conectores = $this->conectorService->getConectores($perPage, $tenantId);
        
        return response()->json($conectores);
    }

    /**
     * Criar novo conector - DESABILITADO
     */
    public function store(Request $request)
    {
        return response()->json([
            'message' => 'Criação de conectores não é permitida. Conectores são criados automaticamente pelo sistema.'
        ], Response::HTTP_METHOD_NOT_ALLOWED);
    }

    /**
     * Exibir conector específico
     */
    public function show($id)
    {
        try {
            $conector = $this->conectorService->getConectorById($id);
            return response()->json($conector);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    /**
     * Atualizar conector
     */
    public function update(UpdateConectorRequest $request, $id)
    {
        $validated = $request->validated();

        // Lógica para definir tenant baseado no perfil do usuário
        $authenticatedUser = Auth::user();
        if (!$authenticatedUser->hasRole('HUB')) {
            // Se não é usuário HUB, sempre usa o tenant_id do usuário logado
            $validated['tenant_id'] = $authenticatedUser->tenant_id;
        } else {
            // Se é usuário HUB, usa o tenant_id enviado ou o do usuário se não foi enviado
            if (!isset($validated['tenant_id']) || empty($validated['tenant_id'])) {
                $validated['tenant_id'] = $authenticatedUser->tenant_id;
            }
        }
        
        // Verificar se já existe um conector com o mesmo código para este tenant (se código foi alterado)
        if (isset($validated['codigo'])) {
            $existingConector = $this->conectorService->getConectorByCodigoAndTenant($validated['codigo'], $validated['tenant_id']);
            if ($existingConector && $existingConector->id != $id) {
                return response()->json([
                    'message' => 'Já existe um conector com este código para este tenant.',
                    'errors' => ['codigo' => ['Já existe um conector com este código para este tenant.']]
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        try {
            $conector = $this->conectorService->updateConector($id, $validated);
            
            // Log de sucesso
            try {
                $this->logService->logCrud('update', 'conector', $conector->id, [
                    'campos_alterados' => array_keys($validated),
                    'valores' => $validated
                ]);
            } catch (\Exception $e) {
                \Log::error('Erro ao criar log: ' . $e->getMessage());
            }
            
            return response()->json($conector);
            
        } catch (\Exception $e) {
            // Log de erro
            try {
                $this->logService->logError('update_conector',
                    'Falha ao atualizar conector: ' . $e->getMessage(),
                    ['conector_id' => $id, 'dados' => $validated, 'erro' => $e->getMessage()]
                );
            } catch (\Exception $logError) {
                \Log::error('Erro ao criar log de erro: ' . $logError->getMessage());
            }
            
            return response()->json([
                'message' => 'Erro ao atualizar conector: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Excluir conector - DESABILITADO
     */
    public function destroy($id)
    {
        return response()->json([
            'message' => 'Exclusão de conectores não é permitida. Conectores são gerenciados automaticamente pelo sistema.'
        ], Response::HTTP_METHOD_NOT_ALLOWED);
    }

    /**
     * Buscar conectores com filtros
     */
    public function search(Request $request)
    {
        $filters = $request->only([
            'tenant_id', 'nome', 'status'
        ]);
        
        $perPage = $request->input('per_page', 15);
        $paginate = $request->input('paginate', true);
        
        $conectores = $this->conectorService->searchConectores($filters, $perPage, $paginate);
        
        return response()->json($conectores);
    }

    /**
     * Buscar conectores por tenant
     */
    public function byTenant(Request $request, $tenantId)
    {
        $perPage = $request->input('per_page', 15);
        $conectores = $this->conectorService->getConectoresByTenant($tenantId, $perPage);
        return response()->json($conectores);
    }

    /**
     * Buscar conector por código
     */
    public function byCodigo(Request $request, $codigo)
    {
        $perPage = $request->input('per_page', 15);
        $conectores = $this->conectorService->getConectoresByCodigo($codigo, $perPage);
        return response()->json($conectores);
    }

    /**
     * Buscar conector por código e tenant
     */
    public function byCodigoAndTenant(Request $request, $codigo, $tenantId)
    {
        $conector = $this->conectorService->getConectorByCodigoAndTenant($codigo, $tenantId);
        return response()->json($conector);
    }

    /**
     * Buscar apenas conectores ativos
     */
    public function ativos(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $conectores = $this->conectorService->getConectoresAtivos($perPage);
        return response()->json($conectores);
    }

    /**
     * Testar conexão do conector
     */
    public function testConnection($id)
    {
        try {
            $result = $this->conectorService->testConnection($id);
            return response()->json([
                'success' => true,
                'message' => 'Conexão testada com sucesso',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao testar conexão: ' . $e->getMessage()
            ], 500);
        }
    }

}

