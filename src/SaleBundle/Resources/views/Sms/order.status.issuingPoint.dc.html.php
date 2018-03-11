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
Спасибо. Ваш заказ № <?= $accountNumber ?> доставлен в магазин по адресу: <?= $shop['address'] ?>. Режим работы: <?= $shop['schedule'] ?>. Заказ можно забрать в течение трех дней