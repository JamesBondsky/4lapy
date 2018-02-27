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
Спасибо. Ваш заказ № <?= $accountNumber ?> собран и готов к выдаче в магазине: <?= $shop['address'] ?>. Режим работы: <?= $shop['schedule'] ?>. Заказ будет ждать вас в течение трех дней