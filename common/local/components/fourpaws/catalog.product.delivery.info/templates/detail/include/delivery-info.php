<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\Catalog\Model\Offer;
use FourPaws\DeliveryBundle\Helpers\DeliveryTimeHelper;

/**
 * @var array $delivery
 */

/**
 * @var Offer $offer
 */
$offer = $arParams['OFFER'];
?>

<li class="b-product-information__item">
    <div class="b-product-information__title-info">Доставка
    </div>
    <div class="b-product-information__value b-product-information__value--link js-open-tab-link" data-tab="data">
        <?= DeliveryTimeHelper::showByDate($delivery['DELIVERY_DATE'], 0, ['DATE_FORMAT' => 'XX']) ?>
        <?php if ($offer->isByRequest()) { ?>
            ближайшая
        <?php } elseif ($delivery['FREE_FROM'] && $offer->getPrice() > $delivery['FREE_FROM']) { ?>
            бесплатно
        <?php } elseif ($delivery['FREE_FROM']) { ?>
            бесплатно от <?= $delivery['FREE_FROM'] ?>
            <span class="b-ruble b-ruble--value-information">₽</span>
        <?php } ?>
    </div>
</li>
