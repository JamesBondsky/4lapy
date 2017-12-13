<?php

namespace FourPaws\External\Manzana\Client;

use FourPaws\App\Application;

/**
 * Class SoapClient
 *
 * @package FourPaws\External\Manzana\Client
 */
class SoapClient
{
    
    
    protected $client;
    
    protected $mlLogin;
    
    protected $mlPassword;
    
    protected $mlIp;
    
    public function __construct()
    {
        $container  = Application::getInstance()->getContainer();
        $parameters = $container->getParameter('manzana');
    
        $this->client = $client;
        
        $this->mlLogin    = $parameters['login'];
        $this->mlPassword = $parameters['password'];
    }
}
