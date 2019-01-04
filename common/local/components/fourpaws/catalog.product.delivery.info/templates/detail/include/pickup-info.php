<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\DeliveryBundle\Entity\CalculationResult\PickupResult;

/**
 * @var array $pickup
 */

/**
 * @var PickupResult $pickupResult
 */
$pickupResult = $pickup['RESULT'];

?>

<li class="b-product-information__item">
    <div class="b-product-information__title-info">Самовывоз</div>
    <div class="b-product-information__value">
        <?=$pickupResult->getTextForOffer()?>
    </div>
</li>
