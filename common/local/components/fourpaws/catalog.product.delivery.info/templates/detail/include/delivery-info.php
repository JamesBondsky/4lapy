<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\Helpers\CurrencyHelper;
use FourPaws\Helpers\WordHelper;

/**
 * @var array $delivery
 */

?>

<li class="b-product-information__item">
    <div class="b-product-information__title-info">Доставка
    </div>
    <div class="b-product-information__value">
        #DELIVERY_DATE#
        <?php if ($delivery['FREE_FROM']) { ?>
            бесплатно от <?= $delivery['FREE_FROM'] ?>
        <span class="b-ruble b-ruble--value-information">₽</span>
        <?php } ?>
    </div>
</li>
