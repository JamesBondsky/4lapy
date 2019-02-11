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
    protected const DEFAULT_API_URL = 'https://robot.dostavista.ru/api/business/1.0';
    /**
     * API Url test
     */
    protected const DEFAULT_API_URL_TEST = 'https://robotapitest.dostavista.ru/api/business/1.0';

    /**
     * API url add order
     */
    protected const URL_ADD_ORDER = 'https://robot.dostavista.ru/api/business/1.0/create-order';

    /**
     * API url add order test
     */
    protected const URL_ADD_ORDER_TEST = 'https://robotapitest.dostavista.ru/api/business/1.0/create-order';

    /**
     * API url cancel order
     */
    protected const URL_CANCEL_ORDER = 'https://robot.dostavista.ru/api/business/1.0/cancel-order';

    /**
     * API url cancel order test
     */
    protected const URL_CANCEL_ORDER_TEST = 'https://robotapitest.dostavista.ru/api/business/1.0/cancel-order';

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
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_HTTPHEADER => [
                            'X-DV-Auth-Token' => $this->token
                        ]
                    ]
                ],
                'timeout' => 3000
            ],
            'headers' => [
                'X-DV-Auth-Token' => $this->token
            ]
        ]);

        try {
            $response = $client->request($method, $this->url, $options);
            $body = json_decode($response->getBody()->getContents(), true);
            return $body;
        } catch (RequestException $e) {
            return [
                'is_successful' => false,
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
        if ($this->testMode) {
            $this->url = self::DEFAULT_API_URL_TEST;
        } else {
            $this->url = self::DEFAULT_API_URL;
        }
        $result = $this->send('GET', []);
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
                'is_successful' => false,
                'error_code' => [
                    '404'
                ],
                'error_message' => [
                    'Поле "matter" не заполнено'
                ]
            ];
        }
        if (!in_array('points', $data) || !is_array($data['points']) || count($data['points']) != 2) {
            return [
                'is_successful' => false,
                'error_code' => [
                    '404'
                ],
                'error_message' => [
                    'Поле "point" не заполнено или некорректно заполнено'
                ]
            ];
        }
        if (!is_array($data['points'][0]) || !isset($data['points'][0]['address']) || !isset($data['points'][0]['required_start_datetime']) || !isset($data['points'][0]['required_finish_datetime']) || !isset($data['points'][0]['contact_person']['phone'])) {
            return [
                'is_successful' => false,
                'error_code' => [
                    '404'
                ],
                'error_message' => [
                    'Параметры адреса/времени/телефона забора не верные'
                ]
            ];
        }

        if (!is_array($data['points'][1]) || !isset($data['points'][1]['address']) || !isset($data['points'][1]['required_start_datetime']) || !isset($data['points'][1]['required_finish_datetime']) || !isset($data['points'][1]['contact_person']['phone'])) {
            return [
                'is_successful' => false,
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
        //фикс времени начала доставки для доставкисты
        $curDate = (new \DateTime)->modify('+1 minutes');
        $requireTimeStart = $curDate->format('c');
        $data['points'][0]['required_start_datetime'] = $requireTimeStart;
        $data['points'][1]['required_start_datetime'] = $requireTimeStart;

        //проверка заполненности обязательных полей
        if (count($resCheck = $this->checkOrderFields($data)) > 0) {
            return $resCheck;
        }

        if ($this->testMode) {
            $this->url = self::URL_ADD_ORDER_TEST;
        } else {
            $this->url = self::URL_ADD_ORDER;
        }

        $options['body'] = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $result = $this->send('POST', $options);
        return $this->parseSendingResult($result);
    }

    /**
     * @param $orderId
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function cancelOrder($orderId): array
    {
        if ($this->testMode) {
            $this->url = self::URL_CANCEL_ORDER_TEST;
        } else {
            $this->url = self::URL_CANCEL_ORDER;
        }

        $options['body'] = json_encode(['order_id' => (int)$orderId], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $result = $this->send('POST', $options);
        return $this->parseSendingResult($result);
    }

    /**
     * @param $res
     * @return array
     */
    protected function parseSendingResult($res): array
    {
        if ($res['is_successful'] == true) {
            $result = [
                'success' => true,
                'order_id' => $res['order']['order_id']
            ];
        } else {
            $result = ['success' => false];
            $result['message'] .= 'Errors = [' . implode(', ', $res['error_code']) . '], ';
            $result['message'] .= 'Messages = (' . implode('. ', $res['error_message']) . ')';
        }
        return $result;
    }
}
