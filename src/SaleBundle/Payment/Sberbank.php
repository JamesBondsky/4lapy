<?php

namespace FourPaws\SaleBundle\Payment;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;
use FourPaws\SaleBundle\Exception\PaymentException;

/**
 * @todo remove this shit
 */
include $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/sberbank.ecom/config.php';

/**
 * Class Sberbank
 *
 * RBS (Sberbank ecom extension)
 *
 * @see RBS
 * @package FourPaws\SaleBundle\Payment
 */
class Sberbank
{
    /**
     * АДРЕС ТЕСТОВОГО ШЛЮЗА
     *
     * @var string
     */
    private const test_url = \API_TEST_URL;

    /**
     * АДРЕС БОЕВОГО ШЛЮЗА
     *
     * @var string
     */
    private const prod_url = \API_PROD_URL;

    private const SUCCESS_CODE = 0;

    private const ERROR_CODES = [1, 2, 3, 4, 5, 7, 8, 999];

    /**
     * ЛОГИН МЕРЧАНТА
     *
     * @var string
     */
    protected $user_name;

    /**
     * ПАРОЛЬ МЕРЧАНТА
     *
     * @var string
     */
    protected $password;

    /**
     * ДВУХСТАДИЙНЫЙ ПЛАТЕЖ
     *
     * Если значение true - будет производиться двухстадийный платеж
     *
     * @var boolean
     */
    protected $two_stage;

    /**
     * ТЕСТОВЫЙ РЕЖИМ
     *
     * Если значение true - плагин будет работать в тестовом режиме
     *
     * @var boolean
     */
    protected $test_mode;

    /**
     * ЛОГИРОВАНИЕ
     *
     * Если значение true - плагин будет логировать запросы в ПШ
     *
     * @var boolean
     */
    protected $logging;

    /**
     * КОНСТРУКТОР КЛАССА
     *
     * Заполнение свойств объекта
     *
     * @param string $user_name логин мерчанта
     * @param string $password пароль мерчанта
     * @param boolean $logging логирование
     * @param boolean $two_stage двухстадийный платеж
     * @param boolean $test_mode тестовый режим
     */
    public function __construct($user_name, $password, $two_stage, $test_mode, $logging)
    {
        [$this->user_name, $this->password, $this->two_stage, $this->test_mode, $this->logging] = \func_get_args();
    }

    /**
     * ЗАПРОС В ПШ
     *
     * Формирование запроса в платежный шлюз и парсинг JSON-ответа
     *
     * @param string $method метод запроса в ПШ
     * @param mixed[] $data данные в запросе
     *
     * @return mixed[]
     *
     * @throws ArgumentException
     */
    protected function gatewayQuery($method, $data): array
    {
        $data['userName'] = $this->user_name;
        $data['password'] = $this->password;
        $data['CMS'] = 'Bitrix';
        $data['Module-Version'] = RBS_VERSION;
        $dataEncoded = \http_build_query($data);

        if (\SITE_CHARSET !== 'UTF-8') {
            global $APPLICATION;
            $dataEncoded = $APPLICATION->ConvertCharset($dataEncoded, 'windows-1251', 'UTF-8');
            /** @noinspection CallableParameterUseCaseInTypeContextInspection */
            $data = $APPLICATION->ConvertCharsetArray($data, 'windows-1251', 'UTF-8');
        }

        if ($this->test_mode) {
            $url = self::test_url;
        } else {
            $url = self::prod_url;
        }

        $curl = \curl_init();
        \curl_setopt_array($curl, [
            \CURLOPT_URL => $url . $method,
            \CURLOPT_RETURNTRANSFER => true,
            \CURLOPT_POST => true,
            \CURLOPT_POSTFIELDS => $dataEncoded,
            \CURLOPT_HTTPHEADER => ['CMS: Bitrix', 'Module-Version: ' . RBS_VERSION],
            \CURLOPT_SSLVERSION => 6,
        ]);
        $response = \curl_exec($curl);
        \curl_close($curl);

        if (!$response) {
            $client = new HttpClient([
                'waitResponse' => true,
            ]);

            $client->setHeader('CMS', 'Bitrix');
            $client->setHeader('Module-Version', RBS_VERSION);
            $response = $client->post($url . $method, $data);
        }

        if ($response) {
            if (\SITE_CHARSET !== 'UTF-8') {
                global $APPLICATION;
                $APPLICATION->ConvertCharset($response, 'windows-1251', 'UTF-8');
            }

            $response = Json::decode($response);

            if ($this->logging) {
                $this->log($url, $method, $data, $response);
            }
        } else {
            $response = [
                'errorCode' => 999,
                'errorMessage' => 'The server does not have SSL/TLS encryption on port 443',
            ];
        }

        return $response;
    }

    /**
     * ЛОГГЕР
     *
     * Логирование запроса и ответа от ПШ
     *
     * @param string $url
     * @param string $method
     * @param mixed[] $data
     * @param mixed[] $response
     *
     * @return void
     */
    protected function log($url, $method, $data, $response): void
    {
        $message = \sprintf(
            'RBS PAYMENT %s%s REQUEST: %s RESPONSE: %s sberbank.ecom',
            $url, $method, \json_encode($data), \json_encode($response)
        );

        \AddMessage2Log($message);
    }

    /**
     * @param string $orderId
     *
     * @return array|mixed[]
     *
     * @throws ArgumentException
     */
    public function reversePayment(string $orderId): array
    {
        $data = \compact('orderId');

        return $this->gatewayQuery('reverse.do', $data);
    }

    /**
     * @param string $orderId
     * @param int $amount
     *
     * @return array|mixed[]
     *
     * @throws ArgumentException
     */
    public function refundPayment(string $orderId, int $amount): array
    {
        $data = \compact('orderId', 'amount');

        return $this->gatewayQuery('refund.do', $data);
    }

    /**
     * @param string $orderId
     * @param int $amount
     * @param array $fiscal
     *
     * @return array
     *
     * @throws ArgumentException
     */
    public function depositPayment(string $orderId, int $amount, array $fiscal = []): array
    {
        $depositItems = $fiscal['fiscal']['orderBundle']['cartItems'] ?? [];
        $depositItems = \json_encode($depositItems);
        $data = \array_merge(\compact('orderId', 'depositItems', 'amount'), $fiscal);
        return $this->gatewayQuery('deposit.do', $data);
    }

    /**
     * @param $response
     *
     * @return bool
     *
     * @throws PaymentException
     */
    public function parseResponse($response): bool
    {
        if (!\is_array($response) ||
            (((int)$response['errorCode'] !== self::SUCCESS_CODE) && \in_array((int)$response['errorCode'], self::ERROR_CODES, true))
        ) {
            /** @noinspection ForgottenDebugOutputInspection */
            throw new PaymentException(
                \sprintf(
                    'Unknown payment exception from response %s',
                    \var_export($response, true)
                )
            );
        }

        if ((int)$response['errorCode'] !== self::SUCCESS_CODE) {
            throw new PaymentException(
                \sprintf(
                    'Deposit payment error: %s',
                    $response['errorMessage']
                )
            );
        }

        return true;
    }

    /**
     * @param $orderId
     *
     * @return array
     *
     * @throws ArgumentException
     */
    public function getOrderStatusByOrderId($orderId): array
    {
        $data = ['orderId' => $orderId];

        return $this->gatewayQuery('getOrderStatusExtended.do', $data);
    }

    /**
     * @param $orderNumber
     *
     * @return array
     *
     * @throws ArgumentException
     */
    public function getOrderStatusByOrderNumber($orderNumber): array
    {
        $data = ['orderNumber' => $orderNumber];

        return $this->gatewayQuery('getOrderStatusExtended.do', $data);
    }
}
