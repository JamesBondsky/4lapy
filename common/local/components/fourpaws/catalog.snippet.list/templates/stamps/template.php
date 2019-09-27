<?php
/**
 * @var CatalogSaleListComponent $component
 *
 * @var CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 */


use FourPaws\Catalog\Model\Offer;
use FourPaws\Components\CatalogSaleListComponent;
use FourPaws\PersonalBundle\Service\StampService;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

if (0 === $component->getProductCollection()->count()) {
    return;
}

if ($arResult['ECOMMERCE_VIEW_SCRIPT']) {
    echo $arResult['ECOMMERCE_VIEW_SCRIPT'];
} ?>


<div class="item-category-landing__slider" data-slider-category-landing="true">
        <?php
        $i = 0;
        $onlyProductsXmlIds = $arParams['ONLY_PRODUCTS_XML_ID'] ?? false;
        foreach ($component->getProductCollection() as $key => $product) {
            if ($onlyProductsXmlIds) {
                $product->setOffers(
                    $product->getOffers()->filter(static function (Offer $item) use ($onlyProductsXmlIds) {
                        return in_array($item->getXmlId(), $onlyProductsXmlIds, false);
                    })
                );
            }

            $APPLICATION->IncludeComponent(
                'fourpaws:catalog.element.snippet',
                'stamps',
                [
                    'PRODUCT' => $product,
                    'OFFER_FILTER' => $arParams['OFFER_FILTER'] ?? [],
                    'COUNTER' => $i,
                ],
                $component,
                [
                    'HIDE_ICONS' => 'Y',
                ]
            );
            $i++;
        }
        ?>
</div>
