<?php

namespace FourPaws\External;

use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\External\Exception\ExpertsenderServiceException;
use GuzzleHttp\Client;
use LinguaLeo\ExpertSender\ExpertSender;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

/**
 * Class ExpertsenderService
 *
 * @package FourPaws\External
 */
class ExpertsenderService
{
    protected $client;
    
    /**
     * ExpertsenderService constructor.
     *
     * @throws ApplicationCreateException
     * @throws InvalidArgumentException
     */
    public function __construct()
    {
        $client = new Client();
        
        list($url, $key) = Application::getInstance()->getContainer()->getParameter('expertsender');
        $this->client = new ExpertSender($url, $key, $client);
    }
    
    /**
     * @param string $email
     * @param array  $parameters
     *
     * @throws ExpertsenderServiceException
     */
    public function addUserToList(string $email, array $parameters)
    {
        throw new ExpertsenderServiceException('Service error');
    }
    
    public function simpleSubscribe(string $email)
    {
        /**
         * @todo implement parameters
         */
        $parameters = [];
        
        $this->addUserToList($email, $parameters);
    }
    
    public function simpleUnsubscribe(string $email)
    {
    
    }
}
