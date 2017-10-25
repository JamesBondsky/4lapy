<?php

namespace FourPaws\External\Manzana\Client;

use FourPaws\External\Manzana\Exception\AuthenticationException;
use FourPaws\External\Manzana\Exception\ExecuteException;
use FourPaws\Health\HealthService;
use FourPaws\Helpers\Exception\HealthException;
use Meng\AsyncSoap\SoapClientInterface;

/**
 * Class SoapClient
 *
 * @package FourPaws\External\Manzana\Client
 */
class SoapClient
{
    const METHOD_AUTHENTICATE = 'Authenticate';
    
    const METHOD_EXECUTE      = 'Execute';
    
    protected $_client;
    
    protected $_healthService;
    
    protected $_mlLogin;
    
    protected $_mlPassword;
    
    protected $_mlIp;
    
    public function __construct(SoapClientInterface $client, HealthService $healthService)
    {
        $this->_client        = $client;
        $this->_healthService = $healthService;
        /**
         * @todo set it from parameters
         */
        $this->_mlLogin    = '';
        $this->_mlPassword = '';
        $this->_mlIp       = '';
    }
    
    /**
     * @param string $login
     *
     * @return string
     *
     * @throws \FourPaws\External\Manzana\Exception\AuthenticationException
     */
    protected function _authenticate(string $login = '') : string
    {
        $arguments = [
            'login'    => $this->_mlLogin,
            'password' => $this->_mlPassword,
            'ip'       => $this->_mlIp,
        ];
        
        if ($login) {
            $arguments['innerLogin'] = $login;
        }
        
        try {
            $sessionId = $this->_client->call(self::METHOD_AUTHENTICATE, $arguments)->AuthenticateResult->SessionId;
            
            try {
                $this->_healthService->setStatus($this->_healthService::SERVICE_MANZANA,
                                                 $this->_healthService::STATUS_AVAILABLE);
            } catch (HealthException $e) {
            }
        } catch (\Exception $e) {
            try {
                $this->_healthService->setStatus($this->_healthService::SERVICE_MANZANA,
                                                 $this->_healthService::STATUS_UNAVAILABLE);
            } catch (HealthException $e) {
            }
            
            throw new AuthenticationException(sprintf('Auth error: %s', $e->getMessage()), $e->getCode(), $e);
        }
        
        return $sessionId;
    }
    
    /**
     * @param string $contract
     * @param array  $parameters
     * @param string $login
     *
     * @return \SimpleXMLElement
     *
     * @throws \FourPaws\External\Manzana\Exception\AuthenticationException
     * @throws \FourPaws\External\Manzana\Exception\ExecuteException
     */
    public function execute(string $contract, array $parameters = [], string $login = '') : \SimpleXMLElement
    {
        $sessionId = $this->_authenticate($login);
        
        try {
            $arguments = [
                'sessionId'    => $sessionId,
                'contractName' => $contract,
                'parameters'   => $parameters,
            ];
            
            $result =
                simplexml_load_string($this->_client->call(self::METHOD_EXECUTE, $arguments)->ExecuteResult->Value);
        } catch (\Exception $e) {
            throw new ExecuteException(sprintf('Execute error: %s', $e->getMessage()), $e->getCode(), $e);
        }
        
        return $result;
    }
}
