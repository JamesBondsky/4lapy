<?php
/**
 * @var string $accountNumber
 * @var bool $userRegistered
 * @var string $phone
 * @var string $email
 * @var float $price
 * @var \DateTime $deliveryDate
 * @var string $deliveryCode
 * @var array $shop
 */
?>
Спасибо. Ваш заказ № <?= $accountNumber ?> на сумму <?= $price ?> руб. оформлен! И будет собран в течение 1 часа в магазине: <?= $shop['address'] ?>. Режим работы: <?= $shop['schedule'] ?>