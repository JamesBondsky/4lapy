<?php

namespace FourPaws\External;

use FourPaws\App\Application;
use GuzzleHttp\Client;
use LinguaLeo\ExpertSender\ExpertSender;

/**
 * Class ExpertsenderService
 *
 * @package FourPaws\External
 */
class ExpertsenderService
{
    protected $client;
    
    /**
     * SmsService constructor.
     *
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     */
    public function __construct()
    {
        $client = new Client();
        
        list($url, $key) = Application::getInstance()->getContainer()->getParameter('expertsender');
        $this->client = new ExpertSender($url, $key, $client);
    }
    
    public function addUserToList(string $email, array $parameters)
    {
    
    }
}
