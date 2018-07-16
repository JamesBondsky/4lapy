<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\SaleBundle\Exception\PaymentException;
use FourPaws\SaleBundle\Service\PaymentService;

IncludeModuleLangFile(__FILE__);

$orderId = $_REQUEST['ORDER_ID'];
if (!$sberbankOrderId = $_REQUEST['orderId']) {
    throw new PaymentException('Заказ не найден');
}

$order = \Bitrix\Sale\Order::load($orderId);

/** @noinspection PhpMethodParametersCountMismatchInspection */
$rbs = new RBS(\compact('test_mode', 'two_stage', 'logging', 'user_name', 'password'));

/** @var PaymentService $paymentService */
$paymentService = \FourPaws\App\Application::getInstance()->getContainer()->get(PaymentService::class);
$paymentService->processOnlinePaymentByOrderId($order, $sberbankOrderId);


