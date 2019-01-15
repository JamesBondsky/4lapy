<?php

namespace FourPaws\External\ZagruzkaCom;


use FourPaws\External\Exception\SmsQuarantineException;
use FourPaws\Helpers\SmsQuarantineHelper;

class Client
{

    /**
     * API URL
     */
    public const API_URL = 'http://lk.zagruzka.com:9080';

    /**
     * Login and url postfix
     * @var string
     */
    protected $login;

    /**
     * Password
     * @var string
     */
    protected $password;

    /**
     * Message sender
     * @var string
     */
    protected $originator = '4lapy';

    /**
     * Client constructor.
     * @param string $login Sms Traffic login
     * @param string $password Sms Traffic Password
     * @param string|null $originator Sms sender
     */
    public function __construct($login, $password, $originator = '4lapy')
    {
        $this->login = $login;
        $this->password = $password;
        $this->originator = $originator;
    }

    /**
     * @param $sms Sms
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Exception
     */
    public function send($sms) {

        if (!SmsQuarantineHelper::canSend($sms->getPhone())) {
            throw new SmsQuarantineException();
        }

        $query = [
            'serviceId' => $this->login,
            'pass' => $this->password,
            'source' => $this->originator,
            'message' => $sms->getMessage(),
            'clientId' => $sms->getPhone(),
        ];

        if ($sms->getFromHour() && $sms->getToHour()) {
            $query['sending_time'] = implode('_', [$sms->getFromHour(), $sms->getToHour()]);
        }

        $client = new \GuzzleHttp\Client();
        $result = $client->get($this::API_URL.'/'.$this->login, [
            'query' => $query,
        ]);

        return $result;

    }

}