<?php
namespace App\Helpers;

use SimpleXMLElement;
use Exception;

class WsdlHelper
{
    private $user;
    private $password;
    private $logger;
    private $timeout;
    private $debug;
    private $lastRequest;
    private $lastResponse;

    public function __construct($user, $password, $timeout = 30, $debug = false)
    {
        $this->user = $user;
        $this->password = $password;
        $this->timeout = $timeout;
        $this->debug = $debug;
        $this->lastRequest = null;
        $this->lastResponse = null;
    }

    public function callService($url, $serviceName, $parameters)
    {
        if ($this->debug) {
            \Log::info("WsdlHelper: Iniciando chamada SOAP", [
                'url' => $url,
                'service' => $serviceName,
                'parameters' => $parameters
            ]);
        }

        $soapRequest = $this->buildSoapRequest($serviceName, $parameters);
        $this->lastRequest = $soapRequest;

        $headers = [
            'Content-Type: text/xml; charset=utf-8',
            'Content-Length: ' . strlen($soapRequest),
            'SOAPAction: "urn:' . $serviceName . '"'
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $soapRequest,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);

        $response = curl_exec($ch);
        $this->lastResponse = $response;
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new Exception("Erro CURL: " . $curlError);
        }

        if ($statusCode !== 200) {
            throw new Exception("HTTP Error {$statusCode}: " . $response);
        }

        if ($this->debug) {
            \Log::info("WsdlHelper: Resposta recebida", [
                'status_code' => $statusCode,
                'response' => $response
            ]);
        }

        return $this->handleResponse($response, $serviceName);
    }

    public function buildSoapRequest($serviceName, $parameters)
    {
        $xml = new SimpleXMLElement(
            '<?xml version="1.0" encoding="UTF-8"?>' .
            '<soapenv:Envelope ' .
            'xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" ' .
            'xmlns:ser="http://services.senior.com.br">' .
            '</soapenv:Envelope>'
        );
        
        $xml->addChild('soapenv:Header', '', 'http://schemas.xmlsoap.org/soap/envelope/');

        $body = $xml->addChild('soapenv:Body', '', 'http://schemas.xmlsoap.org/soap/envelope/');
        $serviceElem = $body->addChild('ser:' . $serviceName, '', 'http://services.senior.com.br');
        
        $serviceElem->addChild('user', $this->user, '');
        $serviceElem->addChild('password', $this->password, '');
        $serviceElem->addChild('encryption', '0', '');
        
        $parametersElem = $serviceElem->addChild('parameters', '', '');
        $parametersElem->addChild('FlowName', '', '');
        $parametersElem->addChild('FlowInstanceID', '', '');
        
        $this->addParameters($parametersElem, $parameters);
        
        $request = $xml->asXML();
        if ($this->debug) {
            \Log::info("WsdlHelper: XML Request gerado", ['xml' => $request]);
        }
        return $request;
    }

    public function addParameters($parentElem, $parameters)
    {
        if (is_array($parameters)) {
            foreach ($parameters as $key => $value) {
                if (is_array($value) && $this->isAssociativeArray($value)) {
                    $subElem = $parentElem->addChild($key);
                    $this->addParameters($subElem, $value);
                } elseif (is_array($value) && !$this->isAssociativeArray($value)) {
                    $subElem = $parentElem->addChild($key);
                    foreach ($value as $item) {
                        $itemElem = $subElem->addChild('dados');
                        $this->addParameters($itemElem, $item);
                    }
                } else {
                    $parentElem->addChild($key, htmlspecialchars((string)$value));
                }
            }
        } elseif (is_array($parameters) && !$this->isAssociativeArray($parameters)) {
            foreach ($parameters as $item) {
                $itemElem = $parentElem->addChild('dados');
                $this->addParameters($itemElem, $item);
            }
        }
    }

    private function isAssociativeArray($array)
    {
        if (!is_array($array)) return false;
        return array_keys($array) !== range(0, count($array) - 1);
    }

    private function parseWsdlResponse($response, $serviceName = null)
    {
        try {
            $xml = new SimpleXMLElement($response);
            
            // Registrar namespaces
            $xml->registerXPathNamespace('soap', 'http://schemas.xmlsoap.org/soap/envelope/');
            $xml->registerXPathNamespace('ns2', 'http://services.senior.com.br');
            
            // Encontrar o elemento result
            $resultElements = $xml->xpath('//result');
            
            if (empty($resultElements)) {
                $message = "Nenhum elemento 'result' encontrado no XML";
                if ($serviceName) {
                    $message .= " para {$serviceName}";
                }
                $this->logger->warning($message);
                return null;
            }
            
            $resultElem = $resultElements[0];
            $parsedResult = $this->parseXmlElement($resultElem);
            
            return $parsedResult;
        } catch (Exception $e) {
            $message = "Erro ao parsear o XML";
            if ($serviceName) {
                $message .= " do serviço {$serviceName}";
            }
            $message .= ": " . $e->getMessage();
            $this->logger->error($message);
            return null;
        }
    }

    private function parseXmlElement($element)
    {
        // Se não tem filhos, retorne como string
        if ($element->count() == 0) {
            return (string)$element;
        }
        
        $childTags = [];
        foreach ($element->children() as $child) {
            $childTags[] = $child->getName();
        }
        
        if (count(array_unique($childTags)) == 1) {
            $result = [];
            foreach ($element->children() as $child) {
                $result[] = $this->parseXmlElement($child);
            }
            return $result;
        }
        
        $result = [];
        foreach ($element->children() as $child) {
            $tag = $child->getName();
            $value = $this->parseXmlElement($child);
            
            if (isset($result[$tag])) {
                if (!is_array($result[$tag]) || !isset($result[$tag][0])) {
                    $result[$tag] = [$result[$tag]];
                }
                $result[$tag][] = $value;
            } else {
                $result[$tag] = $value;
            }
        }
        
        return $result;
    }

    private function handleResponse($response, $serviceName = null)
    {
        try {
            if ($this->debug) {
                \Log::info("WsdlHelper: Parseando resposta", [
                    'response' => $response,
                    'service' => $serviceName
                ]);
            }

            // Procurar por Fault primeiro
            if (strpos($response, '<soap:Fault>') !== false || strpos($response, '<soapenv:Fault>') !== false) {
                $xml = new SimpleXMLElement($response);
                $fault = $xml->xpath('//faultstring');
                if (!empty($fault)) {
                    throw new Exception("SOAP Fault: " . (string)$fault[0]);
                }
            }

            $parsedResponse = $this->parseWsdlResponse($response, $serviceName);
        
            if (empty($parsedResponse)) {
                throw new Exception("Resposta vazia ou inválida do servidor");
            }

            if (isset($parsedResponse['erroExecucao']) && !empty($parsedResponse['erroExecucao'])) {
                throw new Exception($parsedResponse['erroExecucao']);
            }
        
            return $parsedResponse;
        } catch (Exception $e) {
            if ($this->debug) {
                \Log::error("WsdlHelper: Erro ao processar resposta", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'lastRequest' => $this->lastRequest,
                    'lastResponse' => $this->lastResponse
                ]);
            }
            throw $e;
        }
    }
}