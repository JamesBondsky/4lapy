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
use Bitrix\Main\Web\Uri;
use Bitrix\Sale\Payment;
use FourPaws\App\Application;
use FourPaws\Decorators\FullHrefDecorator;
use FourPaws\SaleBundle\Enum\OrderPayment;
use FourPaws\SaleBundle\Exception\NotFoundException;
use FourPaws\PersonalBundle\Service\OrderService as PersonalOrderService;
use FourPaws\SaleBundle\Service\PaymentService as AppPaymentService;

class PaymentService
{
    use LazyLoggerAwareTrait;

    /**
     * @var PersonalOrderService
     */
    private $personalOrderService;

    /**
     * @var AppPaymentService
     */
    private $appPaymentService;


    public function __construct(
        PersonalOrderService $personalOrderService,
        AppPaymentService $appPaymentService
    )
    {
        $this->personalOrderService = $personalOrderService;
        $this->appPaymentService = $appPaymentService;
    }

    /**
     * @param int $orderId
     * @param string $payType
     * @param string $payToken
     * @return string
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\PersonalBundle\Exception\BitrixOrderNotFoundException
     * @throws \Exception
     */
    public function getPaymentUrl(int $orderId, string $payType, string $payToken = ''): string
    {
        $order = $this->personalOrderService->getOrderById($orderId);
        $bitrixOrder = $order->getBitrixOrder();
        if (!$order) {
            throw new NotFoundException("Заказ ID $orderId не найден");
        }

        if (!$bitrixOrder->getPaymentCollection()->count()) {
            throw new \Exception("У заказа ID $orderId не указана платежная система");
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
            throw new \Exception("У заказа ID $orderId не выбран способ оплаты - онлайн");
        }

        $rbs = new \RBS([
            'test_mode' => true,
            'two_stage' => false,
            'logging' => true,
            'user_name' => '4lapy-api',
            'password' => '4lapy',
        ]);

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
}
