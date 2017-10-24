<?php

namespace FourPaws\External\Manzana\Client;

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
    
    protected $_mlLogin;
    
    protected $_mlPassword;
    
    protected $_mlIp;
    
    public function __construct(SoapClientInterface $client)
    {
        $this->_client = $client;
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
        
        /**
         * @todo обработать исключительные ситуации
         */
        $sessionId = $this->_client->call(self::METHOD_AUTHENTICATE, $arguments)->AuthenticateResult->SessionId;
        
        return $sessionId;
    }
    
    /**
     * @param string $contract
     * @param array  $parameters
     * @param string $login
     *
     * @return \SimpleXMLElement
     */
    public function execute(string $contract, array $parameters = [], string $login = '') : \SimpleXMLElement
    {
        $sessionId = $this->_authenticate($login);
        
        /**
         * @todo обработать исключительные ситуации
         */
        $arguments = [
            'sessionId'    => $sessionId,
            'contractName' => $contract,
            'parameters'   => $parameters,
        ];
        
        return simplexml_load_string($this->_client->call(self::METHOD_EXECUTE, $arguments)->ExecuteResult->Value);
    }
}
