<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\SaleBundle\Exception\PaymentException;
use FourPaws\SaleBundle\Service\OrderService;

IncludeModuleLangFile(__FILE__);

$orderId = $_REQUEST['ORDER_ID'];
if (!$sberbankOrderId = $_REQUEST['orderId']) {
    throw new PaymentException('Заказ не найден');
}

$order = \Bitrix\Sale\Order::load($orderId);

/**
 * Подключение файла настроек
 */
/** @noinspection PhpIncludeInspection */
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/sberbank.ecom/config.php');

/**
 * Подключение класса RBS
 */
/** @noinspection PhpIncludeInspection */
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/sberbank.ecom/payment/rbs.php');

$testMode = false;
$twoStage = false;
$logging = false;
$paySystemAction = new CSalePaySystemAction();
$paySystemAction->InitParamArrays(null, $orderId);
$test_mode = $paySystemAction->GetParamValue('TEST_MODE') === 'Y';
$two_stage = $paySystemAction->GetParamValue('TWO_STAGE') === 'Y';
$logging = $paySystemAction->GetParamValue('LOGGING') === 'Y';
$password = $paySystemAction->GetParamValue('PASSWORD');
$user_name = $paySystemAction->GetParamValue('USER_NAME');
/** @noinspection PhpMethodParametersCountMismatchInspection */
$rbs = new RBS(\compact('test_mode', 'two_stage', 'logging', 'user_name', 'password'));

$response = $rbs->get_order_status_by_orderId($sberbankOrderId);
$isSuccess = false;
if ((int)$response['errorCode'] === 0) {
    $isSuccess = in_array((int)$response['orderStatus'], [1, 2], true);
    /** @var \Bitrix\Sale\Payment $payment */
    $onlinePayment = null;
    foreach ($order->getPaymentCollection() as $payment) {
        if ($payment->isInner()) {
            continue;
        }

        if ($payment->getPaySystem()->getField('CODE') === OrderService::PAYMENT_ONLINE) {
            $onlinePayment = $payment;
        }
    }

    if (!$onlinePayment) {
        throw new PaymentException('Неверный тип оплаты у заказа');
    }

    if ($isSuccess) {
        $onlinePayment->setPaid('Y');
        $onlinePayment->setField('PS_SUM', $response['amount'] / 100);
        $onlinePayment->setField('PS_CURRENCY', $response['currency']);
        $onlinePayment->setField('PS_RESPONSE_DATE', new \Bitrix\Main\Type\DateTime());
        $onlinePayment->setField('PS_INVOICE_ID', $sberbankOrderId);
        $onlinePayment->setField('PS_STATUS', 'Y');
        $onlinePayment->setField(
            'PS_STATUS_DESCRIPTION',
            $response['cardAuthInfo']['pan'] . ';' . $response['cardAuthInfo']['cardholderName']
        );
        $onlinePayment->setField('PS_STATUS_CODE', 'Y');
        $onlinePayment->setField('PS_STATUS_MESSAGE', $response['paymentAmountInfo']['paymentState']);
        $onlinePayment->save();
        $order->save();
    } else {
        $errorMessage = $response['actionCodeDescription'];
        $errorCode = $response['orderStatus'];
    }
} else {
    $errorMessage = $response['errorMessage'];
    $errorCode = $response['errorCode'];
}
if (!$isSuccess) {
    throw new PaymentException($errorMessage, $errorCode);
}

