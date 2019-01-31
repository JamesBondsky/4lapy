<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\Catalog\Model\Offer;
use FourPaws\DeliveryBundle\Entity\CalculationResult\DeliveryResult;
use FourPaws\DeliveryBundle\Helpers\DeliveryTimeHelper;

/**
 * @var array $delivery
 */

/**
 * @var Offer $offer
 */
/**
 * @var DeliveryResult $deliveryResult
 */
$offer = $arParams['OFFER'];
$deliveryResult = $delivery['RESULT'];
$isByRequest = $offer->isByRequest();
$text = $deliveryResult->getTextForOffer($isByRequest);

// toDo refactor
?>

<li class="b-product-information__item">
    <div class="b-product-information__title-info">Доставка
    </div>
    <div class="b-product-information__value">
        <?= DeliveryTimeHelper::showByDate($delivery['DELIVERY_DATE'], 0, ['DATE_FORMAT' => 'XX']) ?>
        <?php if ($offer->isByRequest()) { ?>
            ближайшая
        <?php } elseif ($delivery['FREE_FROM'] && $offer->getPrice() > $delivery['FREE_FROM']) { ?>
            бесплатно
        <?php } elseif ($delivery['FREE_FROM']) { ?>
            бесплатно от <?= $delivery['FREE_FROM'] ?>
            <span class="b-ruble b-ruble--value-information">₽</span>
        <?php } ?>
        <?= $text ?>
        <?php if (!$isByRequest && $delivery['FREE_FROM']) { ?>
          <span class="b-ruble b-ruble--value-information"><?= $delivery['CURRENCY'] ?></span>
        <?php } ?>
    </div>
</li>
