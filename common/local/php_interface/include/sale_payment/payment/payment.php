<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Application;
use Bitrix\Sale\Order;
use Bitrix\Sale\PaySystem\Manager;
use FourPaws\App\Application as PawsApplication;
use FourPaws\Decorators\FullHrefDecorator;
use FourPaws\SaleBundle\Exception\PaymentException;
use FourPaws\SaleBundle\Service\PaymentService;

IncludeModuleLangFile(__FILE__);

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

$testMode = $paySystemAction->GetParamValue('TEST_MODE') === 'Y';
$twoStage = $paySystemAction->GetParamValue('TWO_STAGE') === 'Y';
$logging = $paySystemAction->GetParamValue('LOGGING') === 'Y';

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

[$orderId, $paymentId] = Manager::getIdsByPayment($entityId);

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
$fiscalization = unserialize($fiscalization, []);

/* Фискализация */
$fiscal = [];
if ($fiscalization['ENABLE'] === 'Y') {
    /**
     * @var PaymentService $paymentService
     * @global $USER
     */
    $paymentService = PawsApplication::getInstance()->getContainer()->get(PaymentService::class);
    [$amount, $fiscal] = \array_values($paymentService->getFiscalization($order, $USER, (int)$fiscalization['TAX_SYSTEM']));
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
 *
 * @var array $response
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
