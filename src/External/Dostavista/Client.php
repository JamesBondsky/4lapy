<?php

namespace FourPaws\External\Dostavista;

/**
 * Class Client
 *
 * @package FourPaws\External\SmsTraffic
 */
class Client
{
    /**
     * API Url
     */
    public const DEFAULT_API_URL = 'https://robot.dostavista.ru/api/business/1.0';
    /**
     * API Url test
     */
    public const DEFAULT_API_URL_TEST = 'https://robotapitest.dostavista.ru/api/business/1.0';

    /**
     * Test mode flag
     *
     * @var bool
     */
    protected $testMode;

    /**
     * Client id
     *
     * @var string
     */
    protected $clientId;

    /**
     * Token
     *
     * @var string
     */
    protected $token;


    public function __construct($testMode, $clientId, $token)
    {
        $this->testMode = $testMode;
        $this->clientId = $clientId;
        $this->token = $token;
    }

    /**
     * @param $data
     * @return array
     */
    public function send($data): array
    {
        return [];
    }

    /**
     * @return bool
     */
    public function checkConnection()
    {
        if(true){
            return true;
        } else {
            return false;
        }
    }

    protected function parseSendingResult($result): array
    {

    }
}
