<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Application;
use Bitrix\Sale\Order;
use FourPaws\Decorators\FullHrefDecorator;
use FourPaws\SaleBundle\Exception\PaymentException;

IncludeModuleLangFile(__FILE__);

CModule::IncludeModule('sale');
CModule::IncludeModule('catalog');

/** @noinspection PhpIncludeInspection */
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/classes/general/update_class.php';

/**
 * Подключение файла настроек
 */
/** @noinspection PhpIncludeInspection */
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/sberbank.ecom/config.php';

/**
 * Подключение класса RBS
 */
/** @noinspection PhpIncludeInspection */
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/sberbank.ecom/payment/rbs.php';


/** @noinspection PhpDeprecationInspection */
$paySystemAction = new CSalePaySystemAction();

if ($paySystemAction->GetParamValue('TEST_MODE') === 'Y') {
    $testMode = true;
} else {
    $testMode = false;
}
if ($paySystemAction->GetParamValue('TWO_STAGE') === 'Y') {
    $twoStage = true;
} else {
    $twoStage = false;
}
if ($paySystemAction->GetParamValue('LOGGING') === 'Y') {
    $logging = true;
} else {
    $logging = false;
}

$rbs = new RBS(
    $paySystemAction->GetParamValue('USER_NAME'),
    $paySystemAction->GetParamValue('PASSWORD'),
    $twoStage,
    $testMode,
    $logging
);

/** @noinspection PhpUnhandledExceptionInspection */
$app = Application::getInstance();
$request = $app->getContext()->getRequest();

$orderNumber = $paySystemAction->GetParamValue('ORDER_NUMBER');

$entityId = $paySystemAction->GetParamValue('ORDER_PAYMENT_ID');

if (CUpdateSystem::GetModuleVersion('sale') <= '16.0.11') {
    $orderId = $orderNumber;
} else {
    list($orderId, $paymentId) = \Bitrix\Sale\PaySystem\Manager::getIdsByPayment($entityId);
}

if (!$orderNumber) {
    $orderNumber = $orderId;
}
if (!$orderNumber) {
    $orderNumber = $GLOBALS['SALE_INPUT_PARAMS']['ID'];
}

if (!$orderNumber) {
    $orderNumber = $_REQUEST['ORDER_ID'];
}

$order = Order::load($orderId);

$currency = $order->getCurrency();

$amount = $paySystemAction->GetParamValue('AMOUNT') * 100;
if (is_float($amount)) {// Если сумма с плавающей точкой
    $amount = ceil($amount); // Производим округление в большую сторону
}


$returnUrl = '/sale/payment/result.php?ORDER_ID=' . $order->getField('ACCOUNT_NUMBER');
if ($hash = $request->getQuery('HASH')) {
    $returnUrl .= '&HASH=' . $hash;
}

$fiscalization = COption::GetOptionString('sberbank.ecom', 'FISCALIZATION', serialize([]));
$fiscalization = unserialize($fiscalization, null);

/* Фискализация */
$fiscal = [];
if ($fiscalization['ENABLE'] === 'Y') {

    $amount = 0; //Для фискализации общая сумма берется путем суммирования округленных позиций.

    $fiscal = [
        'orderBundle' => [
            'orderCreationDate' => strtotime($order->getField('DATE_INSERT')),
            'customerDetails' => [
                'email' => false,
                'contact' => false,
            ],
            'cartItems' => [
                'items' => [],
            ],
        ],
        'taxSystem' => $fiscalization['TAX_SYSTEM'],
    ];

    /** @var \Bitrix\Sale\PropertyValue $propertyValue */
    foreach ($order->getPropertyCollection() as $propertyValue) {
        if ($propertyValue->getProperty()['IS_PAYER'] === 'Y') {
            $fiscal['orderBundle']['customerDetails']['contact'] = $propertyValue->getValue();
        } elseif ($propertyValue->getProperty()['IS_EMAIL'] === 'Y') {
            $fiscal['orderBundle']['customerDetails']['email'] = $propertyValue->getValue();
        }
    }

    if (!$fiscal['orderBundle']['customerDetails']['email'] || !$fiscal['orderBundle']['customerDetails']['contact']) {
        global $USER;
        if (!$fiscal['orderBundle']['customerDetails']['email']) {
            $fiscal['orderBundle']['customerDetails']['email'] = $USER->GetEmail();
        }
        if (!$fiscal['orderBundle']['customerDetails']['contact']) {
            $fiscal['orderBundle']['customerDetails']['contact'] = $USER->GetFullName();
        }
    }

    $measureList = [];
    $dbMeasure = CCatalogMeasure::getList();
    while ($arMeasure = $dbMeasure->GetNext()) {
        $measureList[$arMeasure['ID']] = $arMeasure['MEASURE_TITLE'];
    }

    $vatList = [];
    $dbRes = CCatalogVat::GetListEx(
        [], //order
        [], //filter
        false, //group
        false, //nav
        [] //select
    );
    while ($arRes = $dbRes->Fetch()) {
        $vatList[$arRes['ID']] = $arRes['RATE'];
    }

    $vatGateway = [
        -1 => 0,
        0 => 1,
        10 => 2,
        18 => 3,
    ];

    $itemsCnt = 1;
    $arCheck = null;

    /** @var \Bitrix\Sale\BasketItem $basketItem */
    foreach ($order->getBasket() as $basketItem) {
        $arProduct = CCatalogProduct::GetByID($basketItem->getProductId());
        $taxType = $arProduct['VAT_ID'] > 0 ? (int)$vatList[$arProduct['VAT_ID']] : -1;

        $itemAmount = $basketItem->getPrice() * 100;
        if (!($itemAmount % 1)) {
            $itemAmount = round($itemAmount);
        }

        $amount += $itemAmount * $basketItem->getQuantity(); //Для фискализации общая сумма берется путем суммирования округленных позиций.

        $fiscal['orderBundle']['cartItems']['items'][] = [
            'positionId' => $itemsCnt++,
            'name' => $basketItem->getField('NAME'),
            'quantity' => [
                'value' => $basketItem->getQuantity(),
                'measure' => $measureList[$arProduct['MEASURE']],
            ],
            'itemAmount' => $itemAmount * $basketItem->getQuantity(),
            'itemCode' => $basketItem->getProductId(),
            'itemPrice' => $itemAmount,
            'tax' => [
                'taxType' => $vatGateway[$taxType],
            ],
        ];
    }
    if ($order->getDeliveryPrice() > 0) {
        $fiscal['orderBundle']['cartItems']['items'][] = [
            'positionId' => $itemsCnt++,
            'name' => GetMessage('RBS_PAYMENT_DELIVERY_TITLE'),
            'quantity' => [
                'value' => 1,
                'measure' => GetMessage('RBS_PAYMENT_MEASURE_DEFAULT'),
            ],
            'itemAmount' => $order->getDeliveryPrice() * 100,
            'itemCode' => $order->getId() . '_DELIVERY',
            'itemPrice' => $order->getDeliveryPrice() * 100,
            'tax' => [
                'taxType' => 0,
            ],
        ];

        $amount += $order->getDeliveryPrice() * 100; //Для фискализации общая сумма берется путем суммирования округленных позиций.
    }
}

/* END Фискализация */
for ($i = 0; $i <= 10; $i++) {
    $response = $rbs->register_order(
        $orderNumber . '_' . $i,
        $amount,
        (string)new FullHrefDecorator($returnUrl),
        $currency,
        $order->getField('USER_DESCRIPTION'),
        $fiscal
    );

    if ((int)$response['errorCode'] !== 1) {
        break;
    }
}

/**
 * Разбор ответа
 */
if ((int)$response['errorCode'] !== 0) {
    $code = null;
    if (in_array((int)$response['errorCode'], [999, 1, 2, 3, 4, 5, 7, 8], true)) {
        $message = $response['errorMessage'];
        $code = $response['errorCode'];
    } else {
        $message = GetMessage('RBS_PAYMENT_PAY_ERROR');
    }
    throw new PaymentException($message, $code);
}

echo '<script>window.location="' . $response['formUrl'] . '"</script>';
