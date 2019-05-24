<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Application;
use Bitrix\Sale\Order;
use Bitrix\Sale\PaySystem\Manager;
use FourPaws\App\Application as PawsApplication;
use FourPaws\SaleBundle\Service\PaymentService;

IncludeModuleLangFile(__FILE__);

/** @noinspection PhpIncludeInspection */
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/classes/general/update_class.php';

/** @noinspection PhpDeprecationInspection */
$paySystemAction = new CSalePaySystemAction();

/** @noinspection PhpUnhandledExceptionInspection */
$app = Application::getInstance();
$request = $app->getContext()->getRequest();

/** @noinspection PhpUnhandledExceptionInspection */
$entityId = $paySystemAction->GetParamValue('ORDER_PAYMENT_ID');
/** @noinspection PhpUnhandledExceptionInspection */
$amount = $paySystemAction->GetParamValue('AMOUNT') * 100;

/** @noinspection PhpUnhandledExceptionInspection */
[
    $orderId,
    $paymentId,
] = Manager::getIdsByPayment($entityId);

/** @noinspection PhpUnhandledExceptionInspection */
$order = Order::load($orderId);

if (is_float($amount)) {// Если сумма с плавающей точкой
    $amount = ceil($amount); // Производим округление в большую сторону
}

/**
 * @var PaymentService $paymentService
 * @global             $USER
 */
$paymentService = PawsApplication::getInstance()->getContainer()->get(PaymentService::class);

/** @noinspection PhpUnhandledExceptionInspection */
$formUrl = $paymentService->registerOrder($order, $amount);

echo '<script>window.location.assign("' . $formUrl . '")</script>';
