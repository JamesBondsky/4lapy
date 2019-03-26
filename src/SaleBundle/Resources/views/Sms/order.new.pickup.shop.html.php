<?php
/**
 * @var string $accountNumber
 * @var bool $userRegistered
 * @var string $phone
 * @var string $email
 * @var float $price
 * @var float $bonusSum
 * @var \DateTime $deliveryDate
 * @var string $deliveryCode
 * @var array $shop
 */

use FourPaws\DeliveryBundle\Helpers\DeliveryTimeHelper; ?>
Спасибо. Ваш заказ № <?= $accountNumber ?> на сумму <?= $price - $bonusSum ?> руб. оформлен! И будет собран в течение <?=DeliveryTimeHelper::PACKING_TIME_TEXT?> в магазине: <?= $shop['address'] ?>. Режим работы: <?= $shop['schedule'] ?>