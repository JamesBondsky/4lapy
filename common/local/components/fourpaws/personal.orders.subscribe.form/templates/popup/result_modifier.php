<?php

use FourPaws\PersonalBundle\Entity\Order;
use FourPaws\PersonalBundle\Entity\OrderSubscribe;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var \CBitrixComponentTemplate $this
 */

//$this->getComponent()->arParams = $arParams;

/** @var FourPawsPersonalCabinetOrdersSubscribeFormComponent $component */
$component = $this->getComponent();

// Запрашиваемое представление страницы
$arResult['CURRENT_STAGE'] = 'initial';

$arResult['isCorrect'] = true;

if (!$arResult['ORDER']) {
    $arResult['isCorrect'] = true;
    return;
}

/** @var Order $order */
$order = $arResult['ORDER'];
/** @var OrderSubscribe $orderSubscribe */
$orderSubscribe = $arResult['ORDER_SUBSCRIBE'];

$arResult['isActualSubscription'] = $orderSubscribe && $orderSubscribe->isActive();

// следует учитывать ситуацию, когда заказ может быть подписан,
// но подписка на него по новым условиям уже может быть недоступна
$arResult['canBeSubscribed'] = $component->getOrderSubscribeService()->canBeSubscribed($order);
if (!$arResult['canBeSubscribed'] && !$arResult['isActualSubscription']) {
    $arResult['isCorrect'] = false;
    return;
}
