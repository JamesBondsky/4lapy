<?php

namespace FourPaws\SaleBundle\Payment;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;
use RBS;

/**
 * Class Sberbank
 *
 * RBS (Sberbank ecom extension)
 *
 * @package FourPaws\SaleBundle\Payment
 */
class Sberbank extends RBS
{
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

        parent::__construct($user_name, $password, $two_stage, $test_mode, $logging);
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
        $data['Module-Version'] = \VERSION;
        $dataEncoded = \http_build_query($data);

        if (\SITE_CHARSET !== 'UTF-8') {
            global $APPLICATION;
            $dataEncoded = $APPLICATION->ConvertCharset($dataEncoded, 'windows-1251', 'UTF-8');
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
            \CURLOPT_HTTPHEADER => ['CMS: Bitrix', 'Module-Version: ' . \VERSION],
            \CURLOPT_SSLVERSION => 6,
        ]);
        $response = \curl_exec($curl);
        \curl_close($curl);

        if (!$response) {
            $client = new HttpClient([
                'waitResponse' => true,
            ]);
            $client->setHeader('CMS', 'Bitrix');
            $client->setHeader('Module-Version', \VERSION);
            $response = $client->post($url . $method, $data);
        }

        if (!$response) {
            $response = [
                'errorCode' => 999,
                'errorMessage' => 'The server does not have SSL/TLS encryption on port 443',
            ];
        } else {
            if (\SITE_CHARSET !== 'UTF-8') {
                global $APPLICATION;
                $APPLICATION->ConvertCharset($response, 'windows-1251', 'UTF-8');
            }

            $response = Json::decode($response);

            if ($this->logging) {
                $this->log($url, $method, $data, $response);
            }
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
     * @return integer
     */
    protected function log($url, $method, $data, $response): int
    {
        return AddMessage2Log('RBS PAYMENT ' . $url . $method . ' REQUEST: ' . \json_encode($data) . ' RESPONSE: ' . \json_encode($response), 'sberbank.ecom');
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
        $data = ['orderId' => $orderId];

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
    public function depositPayment(string $orderId, int $amount): array
    {
        $data = \compact('orderId', 'amount');

        return $this->gatewayQuery('deposit.do', $data);
    }
}
