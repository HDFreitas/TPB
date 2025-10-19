<?php

namespace App\Modules\Csi\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Csi\Interfaces\ContatoRepositoryInterface;
use App\Services\LogService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class ContatosController extends Controller
{
    protected $contatoRepository;
    protected $logService;

    public function __construct(
        ContatoRepositoryInterface $contatoRepository,
        LogService $logService
    )
    {
        $this->contatoRepository = $contatoRepository;
        $this->logService = $logService;
    }

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $contatos = $this->contatoRepository->getAll($perPage);
        return response()->json($contatos);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'codigo' => 'nullable|string|max:50',
            'nome' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'cargo' => 'nullable|string|max:100',
            'telefone' => 'nullable|string|max:20',
            'tipo_perfil' => 'nullable|in:Relacional,Transacional',
            'promotor' => 'nullable|boolean'
        ]);

        // Lógica para definir tenant baseado no perfil do usuário
        $user = Auth::user();
        if (!$user->hasRole('HUB')) {
            // Se não é usuário HUB, sempre usa o tenant_id do usuário logado
            $validated['tenant_id'] = $user->tenant_id;
        } else {
            // Se é usuário HUB, usa o tenant_id enviado ou o do usuário se não foi enviado
            if (!isset($validated['tenant_id']) || empty($validated['tenant_id'])) {
                $validated['tenant_id'] = $user->tenant_id;
            }
        }

        // Adicionar informações de auditoria
        $validated['created_by'] = $user->id;
        $validated['updated_by'] = $user->id;

        try {
            $contato = $this->contatoRepository->create($validated);

            // Log de sucesso
            try {
                $this->logService->logCrud('create', 'contato', $contato->id, [
                    'nome' => $contato->nome,
                    'codigo' => $contato->codigo,
                    'cliente_id' => $contato->cliente_id
                ]);
            } catch (\Exception $e) {
                \Log::error('Erro ao criar log: ' . $e->getMessage());
            }

            return response()->json($contato, Response::HTTP_CREATED);
            
        } catch (\Exception $e) {
            // Log de erro
            try {
                $this->logService->logError('create_contato',
                    'Falha ao criar contato: ' . $e->getMessage(),
                    ['dados' => $validated, 'erro' => $e->getMessage()]
                );
            } catch (\Exception $logError) {
                \Log::error('Erro ao criar log de erro: ' . $logError->getMessage());
            }
            
            return response()->json([
                'error' => 'Erro ao criar contato',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show($id)
    {
        $contato = $this->contatoRepository->findById($id);
        return response()->json($contato);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'codigo' => 'nullable|string|max:50',
            'nome' => 'sometimes|string|max:255',
            'email' => 'nullable|email|max:255',
            'cargo' => 'nullable|string|max:100',
            'telefone' => 'nullable|string|max:20',
            'tipo_perfil' => 'nullable|in:Relacional,Transacional',
            'promotor' => 'nullable|boolean'
        ]);

        $user = Auth::user();
        $validated['updated_by'] = $user->id;

        try {
            $contato = $this->contatoRepository->update($id, $validated);

            // Log de sucesso (sem dados sensíveis - LGPD)
            try {
                // Remover dados sensíveis antes de registrar no log
                $sanitizedData = $validated;
                unset($sanitizedData['email']); // Remover e-mail (LGPD)
                
                $this->logService->logCrud('update', 'contato', $contato->id, [
                    'campos_alterados' => array_keys($validated),
                    'valores' => $sanitizedData
                ]);
            } catch (\Exception $e) {
                \Log::error('Erro ao criar log: ' . $e->getMessage());
            }

            return response()->json($contato);
            
        } catch (\Exception $e) {
            // Log de erro
            try {
                $this->logService->logError('update_contato',
                    'Falha ao atualizar contato: ' . $e->getMessage(),
                    ['contato_id' => $id, 'dados' => $validated, 'erro' => $e->getMessage()]
                );
            } catch (\Exception $logError) {
                \Log::error('Erro ao criar log de erro: ' . $logError->getMessage());
            }
            
            return response()->json([
                'error' => 'Erro ao atualizar contato',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy($id)
    {
        try {
            $contato = $this->contatoRepository->findById($id);
            
            if (!$contato) {
                return response()->json(['error' => 'Contato não encontrado'], Response::HTTP_NOT_FOUND);
            }
            
            $contatoData = [
                'nome' => $contato->nome,
                'codigo' => $contato->codigo
            ];
            
            $this->contatoRepository->delete($id);
            
            // Log de sucesso
            try {
                $this->logService->logCrud('delete', 'contato', $id, $contatoData);
            } catch (\Exception $e) {
                \Log::error('Erro ao criar log: ' . $e->getMessage());
            }

            return response()->json(null, Response::HTTP_NO_CONTENT);
            
        } catch (\Exception $e) {
            // Log de erro
            try {
                $this->logService->logError('delete_contato',
                    'Falha ao excluir contato: ' . $e->getMessage(),
                    ['contato_id' => $id, 'erro' => $e->getMessage()]
                );
            } catch (\Exception $logError) {
                \Log::error('Erro ao criar log de erro: ' . $logError->getMessage());
            }
            
            return response()->json([
                'error' => 'Erro ao excluir contato',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function search(Request $request)
    {
        $where = $request->only([
            'tenant_id', 'cliente_id', 'nome', 'email', 
            'codigo', 'cargo', 'tipo_perfil', 'promotor'
        ]);
        
        // Validação de segurança: garantir que o usuário só veja contatos do seu tenant
        $user = Auth::user();
        if (isset($where['tenant_id']) && $where['tenant_id'] != $user->tenant_id) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        
        // Se não foi especificado tenant_id, usar o tenant do usuário logado
        if (!isset($where['tenant_id'])) {
            $where['tenant_id'] = $user->tenant_id;
        }
        
        $take = $request->input('per_page', 15);
        $paginate = $request->input('paginate', true);
        $contatos = $this->contatoRepository->search($where, $take, $paginate);
        
        return response()->json($contatos);
    }

    public function byCliente(Request $request, $clienteId)
    {
        $perPage = $request->input('per_page', 15);
        $filters = $request->input('filters', []);
        
        // Validação de segurança: garantir que o usuário só veja contatos do seu tenant
        $user = Auth::user();
        $filters['tenant_id'] = $user->tenant_id;
        
        // Se há filtros, usar o método search com cliente_id e tenant_id
        if (!empty($filters)) {
            $filters['cliente_id'] = $clienteId;
            $contatos = $this->contatoRepository->search($filters, $perPage);
        } else {
            $contatos = $this->contatoRepository->findByCliente($clienteId, $perPage);
        }
        
        return response()->json($contatos);
    }

    public function byTenant(Request $request, $tenantId)
    {
        $perPage = $request->input('per_page', 15);
        $filters = $request->input('filters', []);
        
        // Validação de segurança: garantir que o usuário só veja contatos do seu tenant
        $user = Auth::user();
        if ($tenantId != $user->tenant_id) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        
        // Se há filtros, usar o método search com tenant_id
        if (!empty($filters)) {
            $filters['tenant_id'] = $tenantId;
            $contatos = $this->contatoRepository->search($filters, $perPage);
        } else {
            $contatos = $this->contatoRepository->findByTenant($tenantId, $perPage);
        }
        
        return response()->json($contatos);
    }
}
