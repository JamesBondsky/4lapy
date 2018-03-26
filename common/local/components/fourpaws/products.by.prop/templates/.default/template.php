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
<div class="b-container">
    <section class="b-common-section">
        <div class="b-common-section__title-box b-common-section__title-box--sale">
            <h2 class="b-title b-title--sale"><?=$arParams['TITLE']?></h2>
        </div>
        <div class="<?=$arParams['SLIDER'] === 'Y' ? 'b-common-section__content b-common-section__content--sale js-popular-product' : 'b-common-wrapper b-common-wrapper--visible js-catalog-wrapper'?>">
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
    </section>
</div>
<?php
if($arParams['SHOW_PAGE_NAVIGATION'] && $arParams['SLIDER'] !== 'Y') {
    $APPLICATION->IncludeComponent(
        'bitrix:system.pagenavigation',
        'pagination',
        [
            'NAV_TITLE'      => '',
            'NAV_RESULT'     => $offers->getCdbResult(),
            'SHOW_ALWAYS'    => false,
            'PAGE_PARAMETER' => 'page',
            'AJAX_MODE'      => 'N',
        ],
        null,
        [
            'HIDE_ICONS' => 'Y',
        ]
    );
}?>