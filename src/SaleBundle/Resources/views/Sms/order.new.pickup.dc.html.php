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
?>
Спасибо. Ваш заказ № <?= $accountNumber ?> на сумму <?= $price - $bonusSum ?> руб. оформлен! И будет доставлен <?= $deliveryDate->format('d.m.Y') ?> в магазин: <?= $shop['address'] ?>.