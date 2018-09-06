<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;
use FourPaws\Catalog\Collection\OfferCollection;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Model\Product;
use FourPaws\CatalogBundle\Helper\MarkHelper;
use FourPaws\Components\CatalogElementSnippet;
use FourPaws\Decorators\SvgDecorator;
use FourPaws\EcommerceBundle\Service\GoogleEcommerceService;
use FourPaws\EcommerceBundle\Service\RetailRocketService;
use FourPaws\Helpers\WordHelper;

/**
 * @global CMain              $APPLICATION
 * @var array                 $arParams
 * @var array                 $arResult
 * @var CatalogElementSnippet $component
 * @var string                $templateName
 * @var string                $componentPath
 *
 * @var Product               $product
 * @var OfferCollection       $offers
 * @var Offer                 $offer
 * @var Offer                 $currentOffer
 */
$product = $arResult['PRODUCT'];
$offers = $product->getOffersSorted();

$pickupText = Loc::getMessage('CATALOG_ITEM_SNIPPET_VERTICAL.ORDER_BY_REQUEST');

/**
 * @var $ecommerceService    GoogleEcommerceService
 * @var $retailRocketService RetailRocketService
 */
$ecommerceService = $component->getEcommerceService();
$retailRocketService = $component->getRetailRocketService();

$getOnClick = function (Offer $offer) use ($ecommerceService, $arParams) {
    return \str_replace(
        '"', '\'', $ecommerceService->renderScript($ecommerceService->buildClickFromOffer($offer, $arParams['GOOGLE_ECOMMERCE_TYPE']))
    );
};
$getOnMouseDown = function (Offer $offer) use ($retailRocketService) {
    return \str_replace(
        '"', '\'', $retailRocketService->renderAddToBasket($offer->getXmlId())
    );
};

$currentOffer = $arResult['CURRENT_OFFER'];

$arParams['ITEM_ATTR_ID'] = isset($arParams['ITEM_ATTR_ID']) ? trim($arParams['ITEM_ATTR_ID']) : '';

if (!$arParams['ITEM_ATTR_ID']) {
    $arParams['ITEM_ATTR_ID'] = $this->GetEditAreaId($product->getId() . '_' . md5($this->randString()));
}

$value = '';
if ($mainCombinationType === 'SIZE') {
    if ($currentOffer->getClothingSize()) {
        $value = $currentOffer->getClothingSize()->getName();
    }
} else {
    if ($currentOffer->getVolumeReference()) {
        $value = $currentOffer->getVolumeReference()->getName();
    } elseif ($weight = $currentOffer->getCatalogProduct()->getWeight()) {
        if ($weight > 0) {
            $value = WordHelper::showWeight($weight, true, 999);
        }
    }
} ?>
    <div class="b-common-item js-product-item" id="<?= $arParams['ITEM_ATTR_ID'] ?>"
         data-productid="<?= $product->getId() ?>">
        <?= MarkHelper::getMark($currentOffer, '', $arParams['SHARE_ID']) ?>
        <span class="b-common-item__image-wrap">
            <?php if ($currentOffer->getImagesIds()) { ?>
                <a class="b-common-item__image-link js-item-link" href="<?= $currentOffer->getLink() ?>"
                   onclick="<?= $getOnClick($currentOffer) ?>">
                    <img class="b-common-item__image js-weight-img"
                         src="<?= $currentOffer->getResizeImages(240, 240)->first() ?>"
                         alt="<?= $currentOffer->getName() ?>"
                         title="">
                </a>
            <?php } ?>
        </span>
        <div class="b-common-item__info-center-block">
            <a class="b-common-item__description-wrap js-item-link track-recommendation"
               href="<?= $currentOffer->getLink() ?>" onclick="<?= $getOnClick($currentOffer) ?>">
                <span class="b-clipped-text b-clipped-text--three">
                    <span>
                        <?php if ($product->getBrand()) {
                            echo '<span class="span-strong">' . $product->getBrand()->getName() . '</span> ';
                        }

                        echo $product->getName(); ?>
                    </span>
                </span>
            </a>
            <?php if ($offers->count() > 0) {
                $isOffersPrinted = false;
                $mainCombinationType = '';

                if ($currentOffer->getClothingSize()) {
                    $mainCombinationType = 'SIZE';
                } elseif ($currentOffer->getVolumeReference()) {
                    $mainCombinationType = 'VOLUME';
                } else {
                    $mainCombinationType = 'WEIGHT';
                }

                ob_start(); ?>
                <div class="b-weight-container b-weight-container--list">
                    <a class="b-weight-container__link <?= ($offers->count()
                                                            > 1) ? ' b-weight-container__link--mobile ' : '' ?>js-mobile-select js-select-mobile-package"
                       href="javascript:void(0);"><?= $value ?></a>
                    <div class="b-weight-container__dropdown-list__wrapper">
                        <div class="b-weight-container__dropdown-list"></div>
                    </div>
                    <ul class="b-weight-container__list">
                        <?php
                        $countSizes = 0;
                        foreach ($offers as $offer) {
                            $value = '';
                            switch ($mainCombinationType) {
                                case 'SIZE':
                                    $value = $offer->getClothingSize()->getName();
                                    break;

                                case 'VOLUME':
                                    $value = $offer->getVolumeReference()->getName();
                                    break;

                                case 'WEIGHT':
                                    $catalogProduct = $offer->getCatalogProduct();
                                    $weightGrams = $catalogProduct->getWeight();
                                    if ($weightGrams > 0) {
                                        $value = WordHelper::showWeight($weightGrams);
                                    }
                                    break;
                            }
                            if (empty($value)) {
                                continue;
                            }
                            $countSizes++;
                            $isOffersPrinted = true;

                            $addAttr = ' data-price="' . $offer->getCatalogPrice() . '"';
                            $addAttr .= ' data-offerid="' . $offer->getId() . '"';
                            $addAttr .= ' data-image="' . $offer->getResizeImages(240, 240)->first() . '"';
                            $addAttr .= ' data-pickup="' . ($offer->isByRequest() ? $pickupText : '') . '"';
                            $addAttr .= ' data-name="' . $offer->getName() . '"';
                            $addAttr .= ' data-link="' . $offer->getLink() . '"';
                            $addAttr .= ' data-onclick="' . $getOnClick($offer) . '"';
                            $addAttr .= ' data-onmousedown="' . $getOnMouseDown($offer) . '"';
                            $addAttr .= ' data-oldprice="' . ($offer->getCatalogOldPrice()
                                                              !== $offer->getCatalogPrice() ? $offer->getCatalogOldPrice() : '')
                                        . '"';
                            $addAttr .= ' data-discount="' . ($offer->getDiscountPrice() ?: '') . '"';
                            $addAttr .= ' data-available="' . (!$offer->isAvailable() ? 'Нет в наличии' : '') . '"';

                            $addClass = $currentOffer->getId() === $offer->getId() ? ' active-link' : ''; ?>
                            <li class="b-weight-container__item">
                                <a<?= $addAttr ?> href="javascript:void(0)"
                                                  class="b-weight-container__link js-price<?= $addClass ?>">
                                    <?= $value ?>
                                </a>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
                <?php if ($isOffersPrinted) {
                    echo ob_get_clean();
                } else {
                    ob_end_clean();
                }
            } else { ?>
                <div class="b-weight-container b-weight-container--list">
                    <ul class="b-weight-container__list">
                        <li class="b-weight-container__item">
                            <a href="javascript:void(0)"
                               class="b-weight-container__link js-price active-link"
                               data-oldprice="<?= ($currentOffer->getCatalogOldPrice()
                                                   !== $currentOffer->getCatalogPrice() ? $currentOffer->getCatalogOldPrice() : '') ?>"
                               data-discount="<?= ($offer->getDiscountPrice() ?: '') ?>"
                               data-onclick="<?= $getOnClick($currentOffer) ?>"
                               data-onmousedown="<?= $getOnMouseDown($currentOffer) ?>"
                               data-pickup="<?= $currentOffer->isByRequest() ? $pickupText : '' ?>"
                               data-available="<?= !$currentOffer->isAvailable() ? 'Нет в наличии' : '' ?>"
                               data-price="<?= $currentOffer->getCatalogPrice() ?>"
                               data-offerid="<?= $currentOffer->getId() ?>"
                               data-image="<?= $currentOffer->getResizeImages(240, 240)->first() ?>"
                               data-link="<?= $currentOffer->getLink() ?>"></a>
                        </li>
                    </ul>
                </div>
            <?php } ?>
            <a class="b-common-item__add-to-cart js-basket-add track-recommendation"
               href="javascript:void(0);"
               onmousedown="<?= $getOnMouseDown($currentOffer) ?>"
               data-url="/ajax/sale/basket/add/"
               data-offerid="<?= $currentOffer->getId() ?>">
                <span class="b-common-item__wrapper-link">
                    <span class="b-cart">
                        <span class="b-icon b-icon--cart">
                            <?php echo new SvgDecorator('icon-cart', 16, 16); ?>
                        </span>
                    </span>
                    <span class="b-common-item__price js-price-block"><?= $currentOffer->getCatalogPrice() ?></span>
                    <span class="b-common-item__currency"><span class="b-ruble">₽</span></span>
                </span>
                <span class="b-common-item__incart">+1</span>
            </a>
            <?php ob_start();
            if ($currentOffer->hasDiscount()) {
                ?>
                <div class="b-common-item__benefin js-sale-block">
                    <span class="b-common-item__prev-price js-sale-origin">
                        <?= $currentOffer->getOldPrice() ?>
                        <span class="b-ruble b-ruble--prev-price">₽</span>
                    </span>
                    <span class="b-common-item__discount">
                        <span class="b-common-item__disc">Скидка</span>
                        <span class="b-common-item__discount-price js-sale-sale"><?= $currentOffer->getDiscountPrice() ?></span>
                        <span class="b-common-item__currency"> <span class="b-ruble b-ruble--discount">₽</span></span>
                    </span>
                </div>
            <?php }

            if ($currentOffer->isByRequest()) {
                ?>
                <div class="b-common-item__info-wrap">
                    <span class="b-common-item__text">
                        <?= $pickupText ?>
                    </span>
                </div>
            <?php }

            /** @todo инфо о доставке/самовывозе */
            $addInfo = ob_get_clean();
            if ($addInfo) {
                echo '<div class="b-common-item__additional-information">' . $addInfo . '</div>';
            } ?>
        </div>
    </div><?php

//
// BigData
// При клике на ссылку по рекомендации (переход в карточку/добавление в корзину) нужно писать в куки 4LP_RCM_PRODUCT_LOG значение лога:
// {PRODUCT_ID}-{RCM_ID}-{current_server_time}
// записи для каждого элемента разделять точкой, пример: {PRODUCT_ID_1}-{RCM_ID_1}-{current_server_time_1}.{PRODUCT_ID_2}-{RCM_ID_2}-{current_server_time_2}
// При этом следует удалить из лога, хранимого в куке, все записи старше 30 дней, а саму куку следует хранить 10 лет
// (штатную реализация см. в js-функции rememberProductRecommendation шаблона bitrix:catalog.item)
//
// Поскольку на данный момент выполнение js имеет смысл только для фиксации действий с рекомендованными товарами,
// то проверяем наличие переданного id рекомендации, иначе не нагружаем впустую браузеры
//
if (isset($arParams['BIG_DATA']['RCM_ID']) && !empty($arParams['BIG_DATA']['RCM_ID'])) {
    $jsProduct = [
        'ID'     => $product->getId(),
        'RCM_ID' => $arParams['BIG_DATA']['RCM_ID'] ?? '',
    ];
    $jsSelectors = [
        'item'                => '#' . $arParams['ITEM_ATTR_ID'],
        'trackRecommendation' => '#' . $arParams['ITEM_ATTR_ID'] . ' .track-recommendation',
    ];
    $jsParams = [
        'cookiePrefix' => $arParams['BIG_DATA']['cookiePrefix'] ?? '',
        'cookieDomain' => $arParams['BIG_DATA']['cookieDomain'] ?? '',
        'serverTime'   => $arParams['BIG_DATA']['serverTime'] ?? 0,
        'product'      => $jsProduct,
        'selectors'    => $jsSelectors,
    ];

    ?>
    <script type="text/javascript">
        new FourPawsCatalogElementSnippet(<?=\CUtil::PhpToJSObject($jsParams)?>);
    </script>
<?php }
