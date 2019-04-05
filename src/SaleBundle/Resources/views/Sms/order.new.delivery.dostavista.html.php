<?php


use FourPaws\App\Application;
use FourPaws\DeliveryBundle\Service\DeliveryService;

/**
 * @var string $accountNumber
 * @var float $price
 * @var float $bonusSum
 * @var string $deliveryCode
 */

/** @var DeliveryService $deliveryService */
$deliveryService = Application::getInstance()->getContainer()->get('delivery.service');
$periodTo = $deliveryService->getDeliveryByCode($deliveryCode)['CONFIG']['MAIN']['PERIOD']['TO'];
?>
Спасибо. Ваш заказ № <?= $accountNumber ?> на сумму <?= $price - $bonusSum ?> руб. оформлен! И будет доставлен в течение <?= ceil($periodTo / 60); ?> часов компанией Достависта