<?php
/**
 * @var array             $arParams
 * @var array             $arResult
 *
 * @var ProductsByProp    $component
 *
 * @var Offer           $offer
 * @var OfferCollection $offers
 *
 * @global \CMain         $APPLICATION
 */

use FourPaws\Catalog\Collection\OfferCollection;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Components\ProductsByProp;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
$offers = $arResult['OFFERS'];
if (!($offers instanceof OfferCollection) || $offers->isEmpty()) {
    return;
} ?>
<h3 class="b-title b-title--light"><?=$arParams['TITLE']?></h3>
<div class="b-common-wrapper b-common-wrapper--stock js-product-stock-mobile">
    <?php foreach ($offers as $offer) {
        $params = ['PRODUCT' => $offer->getProduct(), 'CURRENT_OFFER' => $offer];
        if ($arParams['SLIDER']) {
            $params['NOT_CATALOG_ITEM_CLASS'] = 'Y';
        }
        $APPLICATION->IncludeComponent(
            'fourpaws:catalog.element.snippet',
            '',
            $params
        );
    } ?>
</div>