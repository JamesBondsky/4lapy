<?php
/**
 * @var string $phone
 * @var int $periodDays
 */

use FourPaws\Helpers\WordHelper;

$periodDays = (int)$periodDays;

$periodTxt = '';
if ($periodDays === 0) {
    $periodTxt = 'Завтра ';
} else {
    $periodTxt = 'Через '.$periodDays.' '.WordHelper::declension($periodDays, ['день', 'дня', 'дней']).' ';
}

echo $periodTxt.'Вам будет доставлен заказ по подписке. Подробности в личном кабинете';
