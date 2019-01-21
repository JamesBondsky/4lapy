<?php

namespace FourPaws\External\Dostavista;

use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Exception\RequestException;

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
    public const DEFAULT_API_URL = 'https://robot.dostavista.ru/bapi';
    /**
     * API Url test
     */
    public const DEFAULT_API_URL_TEST = 'https://robotapitest.dostavista.ru/bapi';

    /**
     * API url add order
     */
    public const URL_ADD_ORDER = 'https://robotapitest.dostavista.ru/bapi/order';

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

    /**
     * current url
     * @var string
     */
    protected $url;


    /**
     * Client constructor.
     * @param $testMode
     * @param $clientId
     * @param $token
     */
    public function __construct($testMode, $clientId, $token)
    {
        $this->testMode = $testMode;
        $this->clientId = $clientId;
        $this->token = $token;
        if ($this->testMode) {
            $this->url = self::DEFAULT_API_URL_TEST;
        } else {
            $this->url = self::DEFAULT_API_URL;
        }
    }

    /**
     * @param string $method
     * @param array $options
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function send(string $method, array $options): array
    {
        $client = new GuzzleHttpClient([
            'defaults' => [
                'config' => [
                    'curl' => [
                        CURLOPT_RETURNTRANSFER => true
                    ]
                ],
                'timeout' => 1000
            ]
        ]);

        try {
            $response = $client->request($method, $this->url, $options);
            $body = json_decode($response->getBody()->getContents(), true);
            return $body;
        } catch (RequestException $e) {
            return [
                'result' => 0,
                'error_code' => [
                    $e->getCode()
                ],
                'error_message' => [
                    $e->getMessage()
                ]
            ];
        }
    }

    /**
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function checkConnection(): array
    {
        $options['query'] = [
            'token' => $this->token,
            'client_id' => $this->clientId
        ];

        $result = $this->send('GET', $options);
        return $this->parseSendingResult($result);
    }

    /**
     * @param $data
     * @return array|bool
     */
    public function checkOrderFields($data)
    {
        if (!in_array('matter', $data)) {
            return [
                'result' => 0,
                'error_code' => [
                    '404'
                ],
                'error_message' => [
                    'Поле "matter" не заполнено'
                ]
            ];
        }
        if (!in_array('point', $data) || !is_array($data['point']) || count($data['point']) != 2) {
            return [
                'result' => 0,
                'error_code' => [
                    '404'
                ],
                'error_message' => [
                    'Поле "point" не заполнено или некорректно заполнено'
                ]
            ];
        }
        if (!is_array($data['point'][0]) || !isset($data['point'][0]['address']) || !isset($data['point'][0]['required_time_start']) || !isset($data['point'][0]['required_time']) || !isset($data['point'][0]['phone'])) {
            return [
                'result' => 0,
                'error_code' => [
                    '404'
                ],
                'error_message' => [
                    'Параметры адреса/времени/телефона забора не верные'
                ]
            ];
        }

        if (!is_array($data['point'][1]) || !isset($data['point'][1]['address']) || !isset($data['point'][1]['required_time_start']) || !isset($data['point'][1]['required_time']) || !isset($data['point'][1]['phone'])) {
            return [
                'result' => 0,
                'error_code' => [
                    '404'
                ],
                'error_message' => [
                    'Параметры адреса/времени/телефона доставки не верные'
                ]
            ];
        }
        return true;
    }

    public function sendOrder($data): array
    {
        //проверка заполненности обязательных полей
        if ($resCheck = $this->checkOrderFields($data) !== true) {
            return $resCheck;
        }

        $this->url = self::URL_ADD_ORDER;

        $options['query'] = array_merge(
            [
                'token' => $this->token,
                'client_id' => $this->clientId
            ],
            $data
        );

        $result = $this->send('POST', $options);
        return $this->parseSendingResult($result);
    }

    /**
     * @param $res
     * @return array
     */
    protected function parseSendingResult($res): array
    {
        if ($res['result'] == 1) {
            $result = [
                'success' => true
            ];
        } else {
            $result = ['success' => false];
            $result['message'] .= 'Errors = [' . implode(', ', $res['error_code']) . '], ';
            $result['message'] .= 'Messages = (' . implode('. ', $res['error_message']) . ')';
        }
        return $result;
    }
}
