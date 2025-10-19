<?php
namespace App\Services;

use Illuminate\Support\Facades\Config;
use App\Helpers\WsdlHelper;
use Exception;

class SeniorService
{
    private $user;
    private $password;
    private $wsdl;

    public function __construct()
    {
        $this->user = Config::get('senior.webservice_username');
        $this->password = Config::get('senior.webservice_password');
        $this->wsdl = new WsdlHelper($this->user, $this->password);
    }

    /**
     * Atualiza os valores dos contratos no Senior.
     * 
     * @param array $data Dados a serem atualizados
     * @return bool True se o usuário for autenticado, false caso contrário
     * @throws Exception Em caso de erro
     */
    public function autenticarUsuario($data): bool
    {
        try {
            $payload = [
                'pmUserName' => $data['username'],
                'pmUserPassword' => $data['password'],
                'pmEncrypted' => 0,
            ];

            $url = Config::get('senior.webservice_url') . "/sapiens_SyncMCWFUsers?WSDL";
            
            $response = $this->wsdl->callService($url, "AuthenticateJAAS", $payload);

            if ($response['pmLogged'] == 0) {
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            throw $e;
        }
    }
}