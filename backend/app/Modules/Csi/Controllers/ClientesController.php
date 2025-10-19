<?php

namespace App\Modules\Csi\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Csi\Interfaces\ClienteRepositoryInterface;
use App\Services\LogService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class ClientesController extends Controller
{
    protected $clienteRepository;
    protected $logService;

    public function __construct(
        ClienteRepositoryInterface $clienteRepository,
        LogService $logService
    )
    {
        $this->clienteRepository = $clienteRepository;
        $this->logService = $logService;
    }

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $clientes = $this->clienteRepository->getAll($perPage, true);
        return response()->json($clientes);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tenant_id' => 'nullable|exists:tenants,id',
            'razao_social' => 'required|string|max:255',
            'nome_fantasia' => 'nullable|string|max:255',
            'cnpj_cpf' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'telefone' => 'nullable|string|max:20',
            'celular' => 'nullable|string|max:20',
            'endereco' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:10',
            'complemento' => 'nullable|string|max:100',
            'bairro' => 'nullable|string|max:100',
            'cidade' => 'nullable|string|max:100',
            'estado' => 'nullable|string|max:2',
            'cep' => 'nullable|string|max:10',
            'status' => 'required|boolean',
            'cliente_referencia' => 'nullable|boolean',
            'tipo_perfil' => 'nullable|in:Relacional,Transacional',
            'classificacao' => 'nullable|in:Promotor,Neutro,Detrator',
            'observacoes' => 'nullable|string'
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
            $cliente = $this->clienteRepository->create($validated);

            // Log de sucesso
            try {
                $this->logService->logCrud('create', 'cliente', $cliente->id, [
                    'razao_social' => $cliente->razao_social,
                    'cnpj_cpf' => $cliente->cnpj_cpf,
                    'tenant_id' => $cliente->tenant_id
                ]);
            } catch (\Exception $e) {
                \Log::error('Erro ao criar log: ' . $e->getMessage());
            }

            return response()->json($cliente, Response::HTTP_CREATED);
            
        } catch (\Exception $e) {
            // Log de erro
            try {
                $this->logService->logError('create_cliente',
                    'Falha ao criar cliente: ' . $e->getMessage(),
                    ['dados' => $validated, 'erro' => $e->getMessage()]
                );
            } catch (\Exception $logError) {
                \Log::error('Erro ao criar log de erro: ' . $logError->getMessage());
            }
            
            return response()->json([
                'error' => 'Erro ao criar cliente',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show($id)
    {
        $cliente = $this->clienteRepository->findById($id);
        return response()->json($cliente);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'razao_social' => 'sometimes|string|max:255',
            'nome_fantasia' => 'nullable|string|max:255',
            'cnpj_cpf' => 'sometimes|string|max:20',
            'email' => 'nullable|email|max:255',
            'telefone' => 'nullable|string|max:20',
            'celular' => 'nullable|string|max:20',
            'endereco' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:10',
            'complemento' => 'nullable|string|max:100',
            'bairro' => 'nullable|string|max:100',
            'cidade' => 'nullable|string|max:100',
            'estado' => 'nullable|string|max:2',
            'cep' => 'nullable|string|max:10',
            'status' => 'sometimes|boolean',
            'cliente_referencia' => 'nullable|boolean',
            'tipo_perfil' => 'nullable|in:Relacional,Transacional',
            'classificacao' => 'nullable|in:Promotor,Neutro,Detrator',
            'observacoes' => 'nullable|string'
        ]);

        $user = Auth::user();
        $validated['updated_by'] = $user->id;

        try {
            $cliente = $this->clienteRepository->update($id, $validated);

            // Log de sucesso (sem dados sensíveis - LGPD)
            try {
                // Remover dados sensíveis antes de registrar no log
                $sanitizedData = $validated;
                unset($sanitizedData['email']); // Remover e-mail se existir (LGPD)
                
                $this->logService->logCrud('update', 'cliente', $cliente->id, [
                    'campos_alterados' => array_keys($validated),
                    'valores' => $sanitizedData
                ]);
            } catch (\Exception $e) {
                \Log::error('Erro ao criar log: ' . $e->getMessage());
            }

            return response()->json($cliente);
            
        } catch (\Exception $e) {
            // Log de erro
            try {
                $this->logService->logError('update_cliente',
                    'Falha ao atualizar cliente: ' . $e->getMessage(),
                    ['cliente_id' => $id, 'dados' => $validated, 'erro' => $e->getMessage()]
                );
            } catch (\Exception $logError) {
                \Log::error('Erro ao criar log de erro: ' . $logError->getMessage());
            }
            
            return response()->json([
                'error' => 'Erro ao atualizar cliente',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy($id)
    {
        try {
            $cliente = $this->clienteRepository->findById($id);
            
            if (!$cliente) {
                return response()->json(['error' => 'Cliente não encontrado'], Response::HTTP_NOT_FOUND);
            }
            
            $clienteData = [
                'razao_social' => $cliente->razao_social,
                'cnpj_cpf' => $cliente->cnpj_cpf
            ];
            
            $this->clienteRepository->delete($id);
            
            // Log de sucesso
            try {
                $this->logService->logCrud('delete', 'cliente', $id, $clienteData);
            } catch (\Exception $e) {
                \Log::error('Erro ao criar log: ' . $e->getMessage());
            }
            
            return response()->json(null, Response::HTTP_NO_CONTENT);
            
        } catch (\Exception $e) {
            // Log de erro
            try {
                $this->logService->logError('delete_cliente',
                    'Falha ao excluir cliente: ' . $e->getMessage(),
                    ['cliente_id' => $id, 'erro' => $e->getMessage()]
                );
            } catch (\Exception $logError) {
                \Log::error('Erro ao criar log de erro: ' . $logError->getMessage());
            }
            
            return response()->json([
                'error' => 'Erro ao excluir cliente',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function search(Request $request)
    {
        $where = $request->only([
            'tenant_id', 'razao_social', 'nome_fantasia', 
            'cnpj_cpf', 'cidade', 'estado', 'status', 'codigo', 'codigo_senior'
        ]);
        
        $take = $request->input('per_page', 15);
        $paginate = $request->input('paginate', true);
        $clientes = $this->clienteRepository->search($where, $take, $paginate);
        
        return response()->json($clientes);
    }

    public function byTenant(Request $request, $tenantId)
    {
        $perPage = $request->input('per_page', 15);
        $clientes = $this->clienteRepository->findByTenant($tenantId, $perPage);
        return response()->json($clientes);
    }

    public function byCnpjCpf(Request $request, $cnpjCpf)
    {
        $cliente = $this->clienteRepository->findByCnpjCpf($cnpjCpf);
        return response()->json($cliente);
    }
}
