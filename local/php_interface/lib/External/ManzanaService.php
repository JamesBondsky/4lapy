<?php

namespace FourPaws\External;

use FourPaws\External\Manzana\Client\SoapClient;
use GuzzleHttp\Client;
use Meng\AsyncSoap\Guzzle\Factory;

/**
 * Class ManzanaService
 *
 * @package FourPaws\External
 */
class ManzanaService
{
    const CONTRACT_CARD_VALIDATE = 'card_validate';
    
    protected $client;
    
    /**
     * SmsService constructor.
     */
    public function __construct()
    {
        /**
         * @todo move into parameters
         */
        $this->client = new SoapClient((new Factory())->create(new Client(), ''));
    }
    
    /**
     * @param string $cardNumber
     *
     * @return bool
     */
    public function isCardValidByCardNumber(string $cardNumber) : bool
    {
        $parameters = [
            [
                'Name'  => 'cardnumber',
                'Value' => $cardNumber,
            ],
        ];
        
        $result = $this->client->execute(self::CONTRACT_CARD_VALIDATE, $parameters);
        
        return (bool)$result->cardvalidateresult->isvalid->textContent();
    }
}
