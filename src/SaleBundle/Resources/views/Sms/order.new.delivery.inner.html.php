<?php
/**
 * @var string $accountNumber
 * @var bool $userRegistered
 * @var string $phone
 * @var string $email
 * @var float $price
 * @var \DateTime $deliveryDate
 * @var string $deliveryCode
 */

$deliveryDateFormatted = $deliveryDate ? $deliveryDate->format('d.m.Y') : ''

?>
    Спасибо. Ваш заказ № <?= $accountNumber ?> на сумму <?= $price ?> руб. оформлен!
    И будет доставлен <?= $deliveryDateFormatted ?>
