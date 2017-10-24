<?php

namespace FourPaws\External;

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
     */
    public function __construct()
    {
        $client = new Client();
        
        /**
         * @todo move into parameters
         */
        $this->client = new ExpertSender('endpointUrl', 'apiKey', $client);
    }
    
    public function addUserToList(string $email, array $parameters) {
    
    }
}
