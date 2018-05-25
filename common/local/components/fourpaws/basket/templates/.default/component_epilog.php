<?php

use FourPaws\Catalog\Model\Offer;
use FourPaws\Components\BasketComponent;

/** @global BasketComponent $component */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

if ($arParams['IS_AJAX']) {
    return;
}
$offers = $templateData['OFFERS'];
if (\is_array($offers) && !empty($offers)) {
    $userDiscount = $component->getCurrentUserService()->getDiscount();
    foreach ($offers as $offerFields) {
        $offer = $component->getOffer((int)$offerFields['ID']);
        if ($offer === null) {
            continue;
        }

        /** @var Offer $offer */
        $bonus = $offer->getBonusFormattedText($userDiscount, (int)$offerFields['QUANTITY'], 0);
        if (!empty($bonus)) {
            ?>
            <script type="text/javascript">
                $(function () {
                    $('.js-bonus-<?=$offer->getId()?>').html('<?=$bonus?>');
                });
            </script>
        <?php }
    }
}
