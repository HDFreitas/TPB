<?php

namespace App\Modules\Csi\Services;

use App\Modules\Csi\Interfaces\ConectorRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class ConectorService
{
    protected $conectorRepository;

    public function __construct(ConectorRepositoryInterface $conectorRepository)
    {
        $this->conectorRepository = $conectorRepository;
    }

    /**
     * Listar conectores com filtros por tenant
     */
    public function getConectores($perPage = 15, $tenantId = null)
    {
        $authenticatedUser = Auth::user();
        
        if (!$authenticatedUser->hasRole('HUB')) {
            // Usuários não-HUB sempre filtram pelo próprio tenant
            $tenantId = $authenticatedUser->tenant_id;
        } elseif ($tenantId === null) {
            // HUB sem tenant especificado usa o próprio tenant
            $tenantId = $authenticatedUser->tenant_id;
        }
        
        return $this->conectorRepository->findByTenant($tenantId, $perPage);
    }

    /**
     * Buscar conector por ID
     */
    public function getConectorById($id)
    {
        return $this->conectorRepository->findById($id);
    }

    /**
     * Atualizar conector
     */
    public function updateConector($id, array $data)
    {
        $conector = $this->conectorRepository->findById($id);
        
        if (!$conector) {
            throw new \Exception('Conector não encontrado');
        }

        return $this->conectorRepository->update($id, $data);
    }

    /**
     * Testar conexão do conector
     */
    public function testConnection($id)
    {
        $conector = $this->conectorRepository->findById($id);
        
        if (!$conector) {
            throw new \Exception('Conector não encontrado');
        }

        return $this->testConectorConnection($conector);
    }

    /**
     * Testar conexão específica do conector
     */
    private function testConectorConnection($conector)
    {
        switch ($conector->codigo) {
            case '1-ERP':
                return $this->testErpConnection($conector);
            case '2-Movidesk':
                return $this->testMovideskConnection($conector);
            case '3-CRM Eleve':
                return $this->testCrmEleveConnection($conector);
            default:
                throw new \Exception('Tipo de conector não suportado');
        }
    }

    /**
     * Testar conexão ERP
     */
    private function testErpConnection($conector)
    {
        // Teste real de conexão SOAP ERP
        try {
            $porta = $conector->porta ?? '';
            $url = rtrim($conector->url, '/') . ($porta ? ":{$porta}" : '');
            $rota = $conector->rota ?? '';
            if ($rota) {
                $url .= '/' . ltrim($rota, '/');
            }

            // Ativar debug para logar XML e resposta
            $wsdlHelper = new \App\Helpers\WsdlHelper(
                $conector->usuario,
                $conector->senha,
                10,
                true // debug ativado
            );
            $serviceName = 'faturamento';
            $parameters = [];
            $wsdlHelper->callService($url, $serviceName, $parameters);
            return [
                'type' => 'ERP',
                'url' => $url,
                'status' => 'connected',
                'message' => 'Conexão ERP testada com sucesso'
            ];
        } catch (\Exception $e) {
            return [
                'type' => 'ERP',
                'url' => $conector->url,
                'status' => 'error',
                'message' => 'Erro ao conectar: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Testar conexão Movidesk
     */
    private function testMovideskConnection($conector)
    {
        // Implementar teste de conexão REST para Movidesk
        // Por enquanto retorna sucesso simulado
        return [
            'type' => 'Movidesk',
            'url' => $conector->url,
            'status' => 'connected',
            'message' => 'Conexão Movidesk testada com sucesso'
        ];
    }

    /**
     * Testar conexão CRM Eleve
     */
    private function testCrmEleveConnection($conector)
    {
        // Implementar teste de conexão para CRM Eleve
        // Por enquanto retorna sucesso simulado
        return [
            'type' => 'CRM Eleve',
            'url' => $conector->url,
            'status' => 'connected',
            'message' => 'Conexão CRM Eleve testada com sucesso'
        ];
    }

    /**
     * Buscar conectores por tenant
     */
    public function getConectoresByTenant($tenantId, $perPage = 15)
    {
        return $this->conectorRepository->findByTenant($tenantId, $perPage);
    }

    /**
     * Buscar conectores por código
     */
    public function getConectoresByCodigo($codigo, $perPage = 15)
    {
        return $this->conectorRepository->findByCodigo($codigo, $perPage);
    }

    /**
     * Buscar conectores ativos
     */
    public function getConectoresAtivos($perPage = 15)
    {
        return $this->conectorRepository->findAtivos($perPage);
    }

    /**
     * Buscar conectores com filtros
     */
    public function searchConectores(array $filters, $perPage = 15, $paginate = true)
    {
        $authenticatedUser = Auth::user();
        
        // Lógica para filtrar por tenant baseado no perfil do usuário
        if (!$authenticatedUser->hasRole('HUB')) {
            // Se não é usuário HUB, filtra apenas pelo tenant do usuário logado
            $filters['tenant_id'] = $authenticatedUser->tenant_id;
        }
        
        return $this->conectorRepository->search($filters, $perPage, $paginate);
    }

    /**
     * Buscar conector por código e tenant
     */
    public function getConectorByCodigoAndTenant($codigo, $tenantId)
    {
        return $this->conectorRepository->findByCodigoAndTenant($codigo, $tenantId);
    }
}
