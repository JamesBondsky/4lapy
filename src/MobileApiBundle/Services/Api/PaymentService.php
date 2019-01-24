<?php

/**
 * @copyright Copyright (c) NotAgency
 */


namespace FourPaws\MobileApiBundle\Services\Api;

/**
 * Подключение класса RBS
 */
/** @noinspection PhpIncludeInspection */
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/sberbank.ecom/payment/rbs.php';

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Sale\Payment;
use FourPaws\App\Application;
use FourPaws\Decorators\FullHrefDecorator;
use FourPaws\PersonalBundle\Entity\Order;
use FourPaws\SaleBundle\Enum\OrderPayment;
use FourPaws\SaleBundle\Exception\NotFoundException;
use FourPaws\SaleBundle\Service\PaymentService as AppPaymentService;
use FourPaws\PersonalBundle\Repository\OrderRepository;

class PaymentService
{
    use LazyLoggerAwareTrait;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var AppPaymentService
     */
    private $appPaymentService;


    public function __construct(
        OrderRepository $orderRepository,
        AppPaymentService $appPaymentService
    )
    {
        $this->orderRepository = $orderRepository;
        $this->appPaymentService = $appPaymentService;
    }

    protected function getPaymentGatewaySettings()
    {
        return [
            'test_mode' => true,
            'two_stage' => false,
            'logging' => true,
            'user_name' => '4lapy-api',
            'password' => '4lapy',
        ];
    }

    /**
     * @param int $orderNumber
     * @param string $payType
     * @param string $payToken
     * @return string
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\PersonalBundle\Exception\BitrixOrderNotFoundException
     * @throws \Exception
     */
    public function getPaymentUrl(int $orderNumber, string $payType, string $payToken = ''): string
    {
        /**
         * @var $order Order
         */
        $order = $this->orderRepository->findBy([
            'filter' => [
                'ACCOUNT_NUMBER' => $orderNumber
            ]
        ])->current();
        if (!$order) {
            throw new NotFoundException("Заказ с номером $orderNumber не найден");
        }

        $bitrixOrder = $order->getBitrixOrder();

        if (!$bitrixOrder->getPaymentCollection()->count()) {
            throw new \Exception("У заказа $orderNumber не указана платежная система");
        }

        $paymentItem = null;
        foreach ($bitrixOrder->getPaymentCollection() as $payment) {
            /** @var $payment Payment */
            if ($payment->isInner()) {
                continue;
            }

            if ($payment->getPaySystem()->getField('CODE') === OrderPayment::PAYMENT_ONLINE) {
                $paymentItem = $payment;
            }
        }

        if (!$paymentItem) {
            throw new \Exception("У заказа $orderNumber не выбран способ оплаты - онлайн");
        }

        $rbs = new \RBS($this->getPaymentGatewaySettings());

        $fiscalization = \COption::GetOptionString('sberbank.ecom', 'FISCALIZATION', serialize([]));
        /** @noinspection UnserializeExploitsInspection */
        $fiscalization = unserialize($fiscalization, []);

        /* Фискализация */
        $fiscal = [];
        if ($fiscalization['ENABLE'] === 'Y') {
            /**
             * @var PaymentService $paymentService
             * @global             $USER
             */
            $paymentService = Application::getInstance()->getContainer()->get(AppPaymentService::class);
            $fiscal = $paymentService->getFiscalization($order->getBitrixOrder(), (int)$fiscalization['TAX_SYSTEM']);
            $amount = $paymentService->getFiscalTotal($fiscal);
            $fiscal = $paymentService->fiscalToArray($fiscal)['fiscal'];
        }

        $returnUrl = '/sale/payment/result.php?ORDER_ID=' . $order->getId();

        $url = '';

        switch ($payType) {
            case 'cashless':
                $response = $rbs->register_order(
                    $order->getAccountNumber(),
                    $amount, //toDo - уточнить что это за amount
                    (string)new FullHrefDecorator($returnUrl),
                    $order->getCurrency(),
                    $order->getBitrixOrder()->getField('USER_DESCRIPTION'),
                    $fiscal
                );
                if ($response['errorMessage']) {
                    throw new \Exception($response['errorMessage']);
                }
                $url = $response['formUrl'];
                break;
            case 'applepay':
                // log_(array($OrderNumberDesc, $pay_token, 'applepay'));
                // toDo в библиотеке RBS нет поддержки метода payment.do который используется для applepay и для android
                // $response = $sbrf->payment($OrderNumberDesc, $pay_token, 'applepay');
                // log_($response);
                // log_('------------------------------------------------------------');
                break;
            case 'android':
                // log_(array($OrderNumberDesc, $pay_token, 'applepay'));
                // toDo в библиотеке RBS нет поддержки метода payment.do который используется для applepay и для android
                // $response = $sbrf->payment($OrderNumberDesc, $pay_token, 'android');
                // log_($response);
                // log_('------------------------------------------------------------');
                break;

            default:
                // $this->addError('required_params_missed');
                break;
        }


        return $url;
    }

    /**
     * ЗАПРОС ОПЛАТЫ ЗАКАЗА APPLEPAY
     *
     * Метод payment.do
     *
     * @param string $orderid номер заказа в Bitrix
     * @param string $paymentToken
     * @return mixed[]
     * @throws \Bitrix\Main\ArgumentException
     */
    protected function payment($orderId, $paymentToken, $device, $amount = 0) {
        $data = array(
            'merchant' => '4lapy',
            'orderNumber' => $orderId,
            'paymentToken' => $paymentToken,
            'preAuth' => true
        );
        // echo "<pre>";print_r($data);echo "</pre>"."\r\n";
        if ($device == 'android')
        {
            $data['amount'] = $amount;
            // $data['ip'] = '89.108.84.88';
        }
        $response = $this->gateway('payment.do', $data, $device);
        // echo "<pre>";print_r($response);echo "</pre>"."\r\n";
        return $response;
    }

    /**
     * Копия метода RBS::gateway потому что он приватный и его нельзя использовать в дочерних классах :(
     * @param $method
     * @param $data
     * @return array|bool|mixed|string
     * @throws \Bitrix\Main\ArgumentException
     */
    protected function gateway($method, $data, $device)
    {
        $settings = $this->getPaymentGatewaySettings();

        $data['userName'] = $settings['user_name'];
        $data['password'] = $settings['password'];
        $data['CMS'] = 'Bitrix ' . SM_VERSION;
        $data['jsonParams'] = json_encode( array('CMS' => $data['CMS']) );
        $dataEncoded = http_build_query($data);

        if (SITE_CHARSET != 'UTF-8') {
            global $APPLICATION;
            $dataEncoded = $APPLICATION->ConvertCharset($dataEncoded, 'windows-1251', 'UTF-8');
            $data = $APPLICATION->ConvertCharsetArray($data, 'windows-1251', 'UTF-8');
        }


        if ($settings['test_mode']) {
            $url = \RBS::test_url;
        } else {
            $url = \RBS::prod_url;
        }

        if(isset($device) and in_array($device, array('android', 'applepay'))) {
            if ($settings['test_mode']) {
                $url = self::test_url_apple_android;
            }
            else
            {
                $url = self::prod_url_apple_android;
            }

            $url .= $device . '/';

            $data = json_encode($data);

            // $obHttp->SetAdditionalHeaders(array("Content-Type"=>"application/json"));
        }

        var_dump($url);
        die();


        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url . $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $dataEncoded,
            CURLOPT_HTTPHEADER => array('CMS: Bitrix'),
            CURLOPT_SSLVERSION => 6
        ));
        $response = curl_exec($curl);
        curl_close($curl);

        if (!$response) {

            $client = new \Bitrix\Main\Web\HttpClient(array(
                'waitResponse' => true
            ));
            $client->setHeader('CMS', 'Bitrix');
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

            $this->log()->info('PaymentService' . $url . $method . ' REQUEST: ' . json_encode($data) . ' RESPONSE: ' . json_encode($response), 'sberbank.ecom');
        }
        return $response;
    }
}
