<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\SaleBundle\Exception\PaymentException;
use FourPaws\SaleBundle\Service\OrderService;

IncludeModuleLangFile(__FILE__);

$orderId = $_REQUEST['ORDER_ID'];

$order = new CSaleOrder();
$arOrder = $order->GetByID($orderId);

$paysystem = new CSalePaySystemAction();
$paysystem->InitParamArrays($arOrder);

/**
 * Подключение файла настроек
 */
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/sberbank.ecom/config.php');

/**
 * Подключение класса RBS
 */
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

$response = $rbs->get_order_status_by_orderId($orderId);

if ((int)$response['errorCode'] === 0) {
    $arOrderFields = [
        'PS_SUM'                => $response['amount'] / 100,
        'PS_CURRENCY'           => $response['currency'],
        'PS_RESPONSE_DATE'      => Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat('FULL', LANG))),
        'PS_STATUS'             => 'Y',
        'PS_STATUS_DESCRIPTION' => $response['cardAuthInfo']['pan'] . ';' . $response['cardAuthInfo']['cardholderName'],
        'PS_STATUS_MESSAGE'     => $response['paymentAmountInfo']['paymentState'],
        'PS_STATUS_CODE'        => 'Y',
    ];

    $order->StatusOrder(
        $orderId,
        OrderService::STATUS_PAID
    );

    $order->PayOrder($orderId, 'Y', true, true);
    if ($paysystem->GetParamValue('SHIPMENT_ENABLE') === 'Y') {
        $order->DeliverOrder($orderId, 'Y');
    }

    $orderNumberPrint = $paysystem->GetParamValue('ORDER_NUMBER');

    $order->Update($orderId, $arOrderFields);
} else {
    throw new PaymentException($response['errorMessage'], $response['errorCode']);
}
