<?php


require_once(realpath(dirname(dirname(__FILE__))) ."/config.php");

define('LOG_FILENAME', realpath(dirname(dirname(__FILE__))) . "/rbspayment.log");

class RBS
{

   
    const test_url = API_TEST_URL;
 
    const prod_url = API_PROD_URL;
 
    const log_file = LOG_FILE;

    /**
     * Массив с НДС
     *
     * @var integer
     * 0 = Без НДС
     * 10 = НДС чека по ставке 10%
     * 18 = НДС чека по ставке 10%
     */
    private static $arr_tax = [
        0 => 0,
        2 => 10, 
        3 => 18,
    ];

    private $user_name;

    private $password;

    private $two_stage;
 
    private $test_mode;

    private $language = 'ru';

    private $logging;



    /**
     * КОНСТРУКТОР КЛАССА
     * Заполнение свойств объекта
     * @param $params
     * @return RBS
     * @internal param string $user_name логин мерчанта
     * @internal param string $password пароль мерчанта
     * @internal param bool $logging логирование
     * @internal param bool $two_stage двухстадийный платеж
     * @internal param bool $test_mode тестовый режим
     */

    public function RBS($params = array()) {
        foreach ($params as $key => $value) {
            $this->{$key} = $value;
        }
    }


    private function gateway($method, $data)
    {
        $data['userName'] = $this->user_name;
        $data['password'] = $this->password;
        $data['CMS'] = 'Bitrix ' . SM_VERSION;
        $data['jsonParams'] = json_encode( array('CMS' => $data['CMS'],'Module-Version' => RBS_VERSION) );
        $dataEncoded = http_build_query($data);

        if (SITE_CHARSET != 'UTF-8') {
            global $APPLICATION;
            $dataEncoded = $APPLICATION->ConvertCharset($dataEncoded, 'windows-1251', 'UTF-8');
            $data = $APPLICATION->ConvertCharsetArray($data, 'windows-1251', 'UTF-8');
        }


        if ($this->test_mode) {
            $url = self::test_url;
        } else {
            $url = self::prod_url;
        }


        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url . $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $dataEncoded,
            CURLOPT_HTTPHEADER => array('CMS: Bitrix', 'Module-Version: ' . RBS_VERSION),
            CURLOPT_SSLVERSION => 6
        ));
        $response = curl_exec($curl);
        curl_close($curl);

        if (!$response) {
 
            $client = new \Bitrix\Main\Web\HttpClient(array(
                'waitResponse' => true
            ));
            $client->setHeader('CMS', 'Bitrix');
            $client->setHeader('Module-Version', RBS_VERSION);
            $response = $client->post($url . $method, $data);
        }

        if (!$response) {
            $response = array(
                'errorCode' => 999,
                'errorMessage' => 'The server does not have SSL/TLS encryption on port 443',
            );
        } else {
            if (SITE_CHARSET != 'UTF-8') {
                global $APPLICATION;
                $APPLICATION->ConvertCharset($response, 'windows-1251', 'UTF-8');
            }
            $response = \Bitrix\Main\Web\Json::decode($response);
 
            if ($this->logging) {
                $this->logger($url, $method, $data, $response);
            }
        }
        return $response;
    }


    private function logger($url, $method, $data, $response)
    {
        return AddMessage2Log('RBS PAYMENT ' . $url . $method . ' REQUEST: ' . json_encode($data) . ' RESPONSE: ' . json_encode($response), 'sberbank.ecom');

    }


    function register_order($order_number, $amount, $return_url, $currency, $orderDescription = '', $arCheck = null)
    {
        $iso = COption::GetOptionString("sberbank.ecom", "iso", serialize(array()));
        $arCurrency = unserialize($iso);
        $arCurrency = array_filter($arCurrency);
        $arDefaultIso = unserialize(DEFAULT_ISO);
        if (is_array($arDefaultIso))
            $arCurrency = array_merge($arDefaultIso, $arCurrency);

        $data = array(
            'orderNumber' => $order_number,
            'language' => $this->language,
            'amount' => $amount,
            'returnUrl' => $return_url,
            'description' => $orderDescription,
        );
        if ($currency && isset($arCurrency[$currency]))
            $data['currency'] = $arCurrency[$currency];

        if ($arCheck) {


            $data = array_merge($data, $arCheck);
//            print_r($data['orderBundle']);die;

            $data['orderBundle'] = \Bitrix\Main\Web\Json::encode($data['orderBundle']);
        }

        if ($this->two_stage) {
            $method = 'registerPreAuth.do';
        } else {
            $method = 'register.do';
        }
        $response = $this->gateway($method, $data);
        return $response;
    }


    public function get_order_status_by_orderId($orderId)
    {
        $data = array('orderId' => $orderId);
        $response = $this->gateway('getOrderStatusExtended.do', $data);
        return $response;
    }

 
    public function get_order_status_by_orderNumber($order_number)
    {
        $data = array('orderNumber' => $order_number);
        $response = $this->gateway('getOrderStatusExtended.do', $data);
        return $response;
    }
    
    public function get_tax_list() {
        return self::$arr_tax;
    }
}