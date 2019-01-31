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
$text = $deliveryResult->getTextForOffer($offer->getPrice(), $isByRequest);
?>

<li class="b-product-information__item">
    <div class="b-product-information__title-info">Доставка
    </div>
    <div class="b-product-information__value">
        <?= $text ?>
        <?php if (!$isByRequest && $delivery['FREE_FROM']) { ?>
          <span class="b-ruble b-ruble--value-information"><?= $delivery['CURRENCY'] ?></span>
        <?php } ?>
    </div>
</li>
