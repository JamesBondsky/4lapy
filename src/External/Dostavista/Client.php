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
    protected const DEFAULT_API_URL = 'https://robot.dostavista.ru/bapi';
    /**
     * API Url test
     */
    protected const DEFAULT_API_URL_TEST = 'https://robotapitest.dostavista.ru/bapi';

    /**
     * API url add order
     */
    protected const URL_ADD_ORDER = 'https://robot.dostavista.ru/bapi/order';

    /**
     * API url add order test
     */
    protected const URL_ADD_ORDER_TEST = 'https://robotapitest.dostavista.ru/bapi/order';

    /**
     * API url edit order
     */
    protected const URL_EDIT_ORDER = 'https://robot.dostavista.ru/api/business/1.0/edit-order';

    /**
     * API url edit order test
     */
    protected const URL_EDIT_ORDER_TEST = 'https://robotapitest.dostavista.ru/api/business/1.0/edit-order';

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
     * array of dostavista statuses
     * @var array
     */
    protected $statuses = [
        0 => 'new',
        1 => 'available',
        2 => 'active',
        3 => 'completed',
        4 => 'reactivated',
        5 => 'draft',
        6 => 'canceled',
        7 => 'delayed'
    ];


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
                'timeout' => 3000
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
     * @return array
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
        return [];
    }

    /**
     * @param $data
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function addOrder($data): array
    {
        //проверка заполненности обязательных полей
        if (count($resCheck = $this->checkOrderFields($data)) > 0) {
            return $resCheck;
        }

        if ($this->testMode) {
            $this->url = self::URL_ADD_ORDER_TEST;
        } else {
            $this->url = self::URL_ADD_ORDER;
        }

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
     * @param $orderId
     * @param $data
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function editOrder($orderId, $data): array
    {
        if ($this->testMode) {
            $this->url = self::URL_EDIT_ORDER_TEST;
        } else {
            $this->url = self::URL_EDIT_ORDER;
        }

        $options['query'] = array_merge(
            [
                'token' => $this->token,
                'client_id' => $this->clientId,
                'order_id' => (int) $orderId
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
                'success' => true,
                'order_id' => $res['order_id']
            ];
        } else {
            $result = ['success' => false];
            $result['message'] .= 'Errors = [' . implode(', ', $res['error_code']) . '], ';
            $result['message'] .= 'Messages = (' . implode('. ', $res['error_message']) . ')';
        }
        return $result;
    }
}
