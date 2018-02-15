<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

IncludeModuleLangFile(__FILE__);

CModule::IncludeModule('sale');
CModule::IncludeModule('catalog');

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/classes/general/update_class.php');

/**
 * Подключение файла настроек
 */
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/sberbank.ecom/config.php');

/**
 * Подключение класса RBS
 */
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/sberbank.ecom/payment/rbs.php');

if (CSalePaySystemAction::GetParamValue('TEST_MODE') === 'Y') {
    $testMode = true;
} else {
    $testMode = false;
}
if (CSalePaySystemAction::GetParamValue('TWO_STAGE') === 'Y') {
    $twoStage = true;
} else {
    $twoStage = false;
}
if (CSalePaySystemAction::GetParamValue('LOGGING') === 'Y') {
    $logging = true;
} else {
    $logging = false;
}

$rbs = new RBS(
    CSalePaySystemAction::GetParamValue('USER_NAME'),
    CSalePaySystemAction::GetParamValue('PASSWORD'),
    $twoStage,
    $testMode,
    $logging
);

$app = \Bitrix\Main\Application::getInstance();
$request = $app->getContext()->getRequest();

/**
 * Запрос register.do или regiterPreAuth.do в ПШ
 */

$orderNumber = CSalePaySystemAction::GetParamValue('ORDER_NUMBER');

$entityId = CSalePaySystemAction::GetParamValue('ORDER_PAYMENT_ID');

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

$arOrder = CSaleOrder::GetByID($orderId);

$currency = $arOrder['CURRENCY'];

$amount = CSalePaySystemAction::GetParamValue('AMOUNT') * 100;

if (is_float($amount)) {// Если сумма с плавающей точкой
    $amount = ceil($amount); // Производим округление в большую сторону
}

$returnUrl = 'http://' . $_SERVER['SERVER_NAME'] . '/sale/payment/result.php?ORDER_ID=' . $arOrder['ACCOUNT_NUMBER'];
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
            'orderCreationDate' => strtotime($arOrder['DATE_INSERT']),
            'customerDetails'   => [
                'email'   => false,
                'contact' => false,
            ],
            'cartItems'         => [
                'items' => [],
            ],
        ],
        'taxSystem'   => $fiscalization['TAX_SYSTEM'],
    ];
    $db_props = CSaleOrderPropsValue::GetOrderProps($arOrder['ID']);

    while ($props = $db_props->Fetch()) {
        if ($props['IS_PAYER'] === 'Y') {
            $fiscal['orderBundle']['customerDetails']['contact'] = $props['VALUE'];
        } elseif ($props['IS_EMAIL'] === 'Y') {
            $fiscal['orderBundle']['customerDetails']['email'] = $props['VALUE'];
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
        0  => 1,
        10 => 2,
        18 => 3,
    ];

    $itemsCnt = 1;
    $arCheck = null;

    $dbRes = CSaleBasket::GetList([], ['ORDER_ID' => $orderId]);
    while ($arRes = $dbRes->Fetch()) {

        $arProduct = CCatalogProduct::GetByID($arRes['PRODUCT_ID']);

        $taxType = $arProduct['VAT_ID'] > 0 ? (int)$vatList[$arProduct['VAT_ID']] : -1;

        $itemAmount = $arRes['PRICE'] * 100;
        /*if($itemAmount % 1)
            $itemAmount = round($itemAmount);*/
        if (!($itemAmount % 1)) {
            $itemAmount = round($itemAmount);
        }

        $amount += $itemAmount * $arRes['QUANTITY']; //Для фискализации общая сумма берется путем суммирования округленных позиций.

        $fiscal['orderBundle']['cartItems']['items'][] = [
            'positionId' => $itemsCnt++,
            'name'       => $arRes['NAME'],
            'quantity'   => [
                'value'   => $arRes['QUANTITY'],
                'measure' => $measureList[$arProduct['MEASURE']],
            ],
            'itemAmount' => $itemAmount * $arRes['QUANTITY'],
            'itemCode'   => $arRes['PRODUCT_ID'],
            'itemPrice'  => $itemAmount,
            'tax'        => [
                'taxType' => $vatGateway[$taxType],
            ],
        ];
    }
    if ($arOrder['PRICE_DELIVERY'] > 0) {
        $fiscal['orderBundle']['cartItems']['items'][] = [
            'positionId' => $itemsCnt++,
            'name'       => GetMessage('RBS_PAYMENT_DELIVERY_TITLE'),
            'quantity'   => [
                'value'   => 1,
                'measure' => GetMessage('RBS_PAYMENT_MEASURE_DEFAULT'),
            ],
            'itemAmount' => $arOrder['PRICE_DELIVERY'] * 100,
            'itemCode'   => $arOrder['ID'] . '_DELIVERY',
            'itemPrice'  => $arOrder['PRICE_DELIVERY'] * 100,
            'tax'        => [
                'taxType' => 0,
            ],
        ];

        $amount += $arOrder['PRICE_DELIVERY'] * 100; //Для фискализации общая сумма берется путем суммирования округленных позиций.
    }
}

/* END Фискализация */
for ($i = 0; $i <= 10; $i++) {
    $response = $rbs->register_order(
        $orderNumber . '_' . $i,
        $amount,
        $returnUrl,
        $currency,
        $arOrder['USER_DESCRIPTION'],
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
    throw new \FourPaws\SaleBundle\Exception\PaymentException($message, $code);
}

echo '<script>window.location="' . $response['formUrl'] . '"</script>';
