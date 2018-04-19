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

/** @noinspection PhpDeprecationInspection */
$paysystem = new CSalePaySystemAction();
$paysystem->InitParamArrays(null, $orderId);
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

if ($paysystem->GetParamValue('TEST_MODE') === 'Y') {
    $testMode = true;
}
if ($paysystem->GetParamValue('TWO_STAGE') === 'Y') {
    $twoStage = true;
}
if ($paysystem->GetParamValue('LOGGING') === 'Y') {
    $logging = true;
}

$rbs = new RBS(
    $paysystem->GetParamValue('USER_NAME'),
    $paysystem->GetParamValue('PASSWORD'),
    $twoStage,
    $testMode,
    $logging
);

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

