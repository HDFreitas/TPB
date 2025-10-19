<?php

namespace App\Modules\Csi\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Csi\Interfaces\InteracaoRepositoryInterface;
use App\Services\LogService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Modules\Csi\Requests\InteracaoRequest;

class InteracoesController extends Controller
{
    protected $interacaoRepository;
    protected $logService;

    public function __construct(
        InteracaoRepositoryInterface $interacaoRepository,
        LogService $logService
    )
    {
        $this->interacaoRepository = $interacaoRepository;
        $this->logService = $logService;

        // Permissões por ação
        $this->middleware('permission:interacoes.visualizar')->only(['index', 'show', 'search', 'byCliente', 'byTenant']);
        $this->middleware('permission:interacoes.criar')->only(['store']);
        $this->middleware('permission:interacoes.editar')->only(['update']);
        $this->middleware('permission:interacoes.excluir')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $user = auth()->user();
        if ($user && $user->tenant_id) {
            $interacoes = $this->interacaoRepository->findByTenant($user->tenant_id, $perPage);
        } else {
            $interacoes = $this->interacaoRepository->getAll($perPage, true);
        }
        return response()->json($interacoes);
    }

    public function store(InteracaoRequest $request)
    {
        $validated = $request->validated();

        // Forçar tenant e usuário do contexto autenticado
        $user = auth()->user();
        if ($user) {
            $validated['tenant_id'] = $user->tenant_id ?? $validated['tenant_id'] ?? null;
            $validated['user_id'] = $validated['user_id'] ?? $user->id;
        }

        try {
            $interacao = $this->interacaoRepository->create($validated);

            // Log de sucesso (campos não sensíveis à LGPD)
            try {
                $this->logService->logCrud('create', 'interacao', $interacao->id, [
                    'tipo' => $interacao->tipo,
                    'origem' => $interacao->origem,
                    'data_interacao' => optional($interacao->data_interacao)->toDateTimeString(),
                    'chave' => $interacao->chave,
                    'valor' => $interacao->valor,
                    'cliente_id' => $interacao->cliente_id,
                    'tenant_id' => $interacao->tenant_id,
                ]);
            } catch (\Exception $e) {
                \Log::error('Erro ao criar log: ' . $e->getMessage());
            }

            return response()->json($interacao, Response::HTTP_CREATED);
            
        } catch (\Exception $e) {
            // Log de erro
            try {
                $this->logService->logError('create_interacao',
                    'Falha ao criar interação: ' . $e->getMessage(),
                    ['dados' => $validated, 'erro' => $e->getMessage()]
                );
            } catch (\Exception $logError) {
                \Log::error('Erro ao criar log de erro: ' . $logError->getMessage());
            }
            
            return response()->json([
                'error' => 'Erro ao criar interação',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show($id)
    {
        $interacao = $this->interacaoRepository->findById($id);
        return response()->json($interacao);
    }

    public function update(InteracaoRequest $request, $id)
    {
        $validated = $request->validated();

        // Forçar tenant do contexto (não permitir troca) e manter user_id se não enviado
        $user = auth()->user();
        if ($user) {
            $validated['tenant_id'] = $user->tenant_id ?? null;
            if (!isset($validated['user_id'])) {
                $validated['user_id'] = $user->id;
            }
        }

        try {
            $interacao = $this->interacaoRepository->update($id, $validated);

            // Log de sucesso (campos não sensíveis à LGPD)
            try {
                $sanitized = $validated;
                unset($sanitized['descricao'], $sanitized['titulo']);
                $this->logService->logCrud('update', 'interacao', $interacao->id, [
                    'campos_alterados' => array_keys($sanitized),
                    'valores' => $sanitized,
                    'tipo' => $interacao->tipo,
                    'origem' => $interacao->origem,
                    'data_interacao' => optional($interacao->data_interacao)->toDateTimeString(),
                    'chave' => $interacao->chave,
                    'valor' => $interacao->valor,
                    'cliente_id' => $interacao->cliente_id,
                ]);
            } catch (\Exception $e) {
                \Log::error('Erro ao criar log: ' . $e->getMessage());
            }

            return response()->json($interacao);
            
        } catch (\Exception $e) {
            // Log de erro
            try {
                $this->logService->logError('update_interacao',
                    'Falha ao atualizar interação: ' . $e->getMessage(),
                    ['interacao_id' => $id, 'dados' => $validated, 'erro' => $e->getMessage()]
                );
            } catch (\Exception $logError) {
                \Log::error('Erro ao criar log de erro: ' . $logError->getMessage());
            }
            
            return response()->json([
                'error' => 'Erro ao atualizar interação',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy($id)
    {
        try {
            $interacao = $this->interacaoRepository->findById($id);
            
            if (!$interacao) {
                return response()->json(['error' => 'Interação não encontrada'], Response::HTTP_NOT_FOUND);
            }
            
            $interacaoData = [
                'tipo' => $interacao->tipo,
                'origem' => $interacao->origem,
                'data_interacao' => optional($interacao->data_interacao)->toDateTimeString(),
                'chave' => $interacao->chave,
                'valor' => $interacao->valor,
                'cliente_id' => $interacao->cliente_id
            ];
            
            $this->interacaoRepository->delete($id);
            
            // Log de sucesso
            try {
                $this->logService->logCrud('delete', 'interacao', $id, $interacaoData);
            } catch (\Exception $e) {
                \Log::error('Erro ao criar log: ' . $e->getMessage());
            }
            
            return response()->json(null, Response::HTTP_NO_CONTENT);
            
        } catch (\Exception $e) {
            // Log de erro
            try {
                $this->logService->logError('delete_interacao',
                    'Falha ao excluir interação: ' . $e->getMessage(),
                    ['interacao_id' => $id, 'erro' => $e->getMessage()]
                );
            } catch (\Exception $logError) {
                \Log::error('Erro ao criar log de erro: ' . $logError->getMessage());
            }
            
            return response()->json([
                'error' => 'Erro ao excluir interação',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function search(Request $request)
    {
        $where = $request->only([
            'tenant_id', 'cliente_id', 'user_id', 'tipo_interacao_id', 'tipo_interacao_nome',
            'origem', 'data_interacao_from', 'data_interacao_to', 'descricao', 'titulo', 'chave',
            'valor_from', 'valor_to', 'cliente_nome'
        ]);

        // Forçar escopo por tenant autenticado quando não informado
        $user = auth()->user();
        if ($user && $user->tenant_id && empty($where['tenant_id'])) {
            $where['tenant_id'] = $user->tenant_id;
        }
        
        $take = $request->input('per_page', 15);
        $paginate = $request->input('paginate', true);
        $interacoes = $this->interacaoRepository->search($where, $take, $paginate);
        
        return response()->json($interacoes);
    }

    public function byCliente(Request $request, $clienteId)
    {
        $perPage = $request->input('per_page', 15);
        $interacoes = $this->interacaoRepository->findByCliente($clienteId, $perPage);
        return response()->json($interacoes);
    }

    public function byTenant(Request $request, $tenantId)
    {
        $perPage = $request->input('per_page', 15);
        $interacoes = $this->interacaoRepository->findByTenant($tenantId, $perPage);
        return response()->json($interacoes);
    }
}
