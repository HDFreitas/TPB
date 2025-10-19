<?php

namespace App\Modules\Csi\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Csi\Services\TipoInteracaoService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class TiposInteracaoController extends Controller
{
    protected TipoInteracaoService $tipoInteracaoService;

    public function __construct(TipoInteracaoService $tipoInteracaoService)
    {
        $this->tipoInteracaoService = $tipoInteracaoService;
        
        // Aplicar middleware para definir contexto do tenant antes das verificações de permissão
        $this->middleware('set.permission.team');
        
        // Aplicar middlewares de permissão específicas para cada método
        $this->middleware('permission:tipos_interacao.visualizar')->only(['index', 'show', 'byTenant', 'byConector', 'ativos']);
        $this->middleware('permission:tipos_interacao.criar')->only(['store']);
        $this->middleware('permission:tipos_interacao.editar')->only(['update']);
        $this->middleware('permission:tipos_interacao.excluir')->only(['destroy']);
        $this->middleware('permission:tipos_interacao.importar')->only(['importFromConector']);
    }

    /**
     * Listar todos os tipos de interação
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 15);
            $tiposInteracao = $this->tipoInteracaoService->index($perPage);
            
            return response()->json($tiposInteracao);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao buscar tipos de interação',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Criar novo tipo de interação
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'tenant_id' => 'required|integer|exists:tenants,id',
                'nome' => 'required|string|max:255',
                'conector_id' => 'nullable|integer|exists:conectores,id',
                'porta' => 'nullable|string|max:32',
                'rota' => 'nullable|string|max:255',
                'status' => 'boolean',
                'observacoes' => 'nullable|string'
            ]);

            $tipoInteracao = $this->tipoInteracaoService->store($validated);
            
            return response()->json([
                'message' => 'Tipo de interação criado com sucesso',
                'data' => $tipoInteracao->load(['tenant', 'conector'])
            ], Response::HTTP_CREATED);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao criar tipo de interação',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Exibir tipo de interação específico
     */
    public function show(int $id)
    {
        try {
            $tipoInteracao = $this->tipoInteracaoService->show($id);
            
            if (!$tipoInteracao) {
                return response()->json([
                    'message' => 'Tipo de interação não encontrado'
                ], Response::HTTP_NOT_FOUND);
            }
            
            return response()->json([
                'data' => $tipoInteracao
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao buscar tipo de interação',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Atualizar tipo de interação
     */
    public function update(Request $request, int $id)
    {
        try {
            $validated = $request->validate([
                'tenant_id' => 'sometimes|integer|exists:tenants,id',
                'nome' => 'sometimes|string|max:255',
                'conector_id' => 'nullable|integer|exists:conectores,id',
                'porta' => 'nullable|string|max:32',
                'rota' => 'nullable|string|max:255',
                'status' => 'boolean',
                'observacoes' => 'nullable|string'
            ]);

            $tipoInteracao = $this->tipoInteracaoService->update($id, $validated);
            
            if (!$tipoInteracao) {
                return response()->json([
                    'message' => 'Tipo de interação não encontrado'
                ], Response::HTTP_NOT_FOUND);
            }
            
            return response()->json([
                'message' => 'Tipo de interação atualizado com sucesso',
                'data' => $tipoInteracao
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao atualizar tipo de interação',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Deletar tipo de interação
     */
    public function destroy(int $id)
    {
        try {
            $deleted = $this->tipoInteracaoService->destroy($id);
            
            if (!$deleted) {
                return response()->json([
                    'message' => 'Tipo de interação não encontrado'
                ], Response::HTTP_NOT_FOUND);
            }
            
            return response()->json([
                'message' => 'Tipo de interação deletado com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao deletar tipo de interação',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Buscar tipos de interação por tenant
     */
    public function byTenant(Request $request, int $tenantId)
    {
        try {
            $perPage = $request->input('per_page', 15);
            $tiposInteracao = $this->tipoInteracaoService->byTenant($tenantId, $perPage);
            
            return response()->json($tiposInteracao);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao buscar tipos de interação por tenant',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Buscar tipos de interação ativos
     */
    public function ativos(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 15);
            $tenantId = $request->input('tenant_id');
            $tiposInteracao = $this->tipoInteracaoService->ativos($tenantId, $perPage);
            
            return response()->json($tiposInteracao);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao buscar tipos de interação ativos',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Buscar tipos de interação com filtros
     */
    public function search(Request $request)
    {
        try {
            $filters = $request->only(['tenant_id', 'nome', 'conector_id', 'status']);
            $perPage = $request->input('per_page', 15);
            
            $tiposInteracao = $this->tipoInteracaoService->search($filters, $perPage);
            
            return response()->json($tiposInteracao);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao buscar tipos de interação',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Buscar tipos de interação por conector
     */
    public function byConector(int $conectorId)
    {
        try {
            $tiposInteracao = $this->tipoInteracaoService->byConector($conectorId);
            
            return response()->json([
                'data' => $tiposInteracao
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao buscar tipos de interação por conector',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Importar dados do conector
     */
    public function importFromConector(int $id)
    {
        try {
            $result = $this->tipoInteracaoService->importFromConector($id);
            
            return response()->json([
                'message' => 'Importação realizada com sucesso',
                'data' => $result
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erro de validação',
                'errors' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao importar dados do conector',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
