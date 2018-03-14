<?php
/**
 * @var array             $arParams
 * @var array             $arResult
 *
 * @var ProductsByProp    $component
 *
 * @var Product           $product
 * @var ProductCollection $products
 *
 * @global \CMain         $APPLICATION
 */

use FourPaws\Catalog\Collection\ProductCollection;
use FourPaws\Catalog\Model\Product;
use FourPaws\Components\ProductsByProp;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$products = $arResult['PRODUCTS'];
if (!($products instanceof ProductCollection) || $products->isEmpty()) {
    return;
} ?>
<div class="b-container">
    <section class="b-common-section">
        <div class="b-common-section__title-box b-common-section__title-box--sale">
            <h2 class="b-title b-title--sale"><?=$arParams['TITLE']?></h2>
        </div>
        <div class="<?=$arParams['SLDIER'] === 'Y' ? 'b-common-section__content b-common-section__content--sale js-popular-product' : 'b-common-wrapper b-common-wrapper--visible js-catalog-wrapper'?>">
            <?php foreach ($products as $product) {
                $APPLICATION->IncludeComponent(
                    'fourpaws:catalog.element.snippet',
                    '',
                    ['PRODUCT' => $product]
                );
            } ?>
        </div>
    </section>
</div>
<?php $APPLICATION->IncludeComponent(
    'bitrix:system.pagenavigation',
    'pagination',
    [
        'NAV_TITLE'      => '',
        'NAV_RESULT'     => $products->getCdbResult(),
        'SHOW_ALWAYS'    => false,
        'PAGE_PARAMETER' => 'page',
        'AJAX_MODE'      => 'N',
    ],
    null,
    [
        'HIDE_ICONS' => 'Y',
    ]
); ?>