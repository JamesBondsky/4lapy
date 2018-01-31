<?php

use Bitrix\Main\Localization\Loc;
use FourPaws\Catalog\Collection\OfferCollection;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Model\Product;
use FourPaws\Decorators\SvgDecorator;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var CatalogSectionComponent $component
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $componentPath
 *
 * @var Product $product
 * @var OfferCollection $offers
 * @var Offer $offer
 * @var Offer $firstOffer
 */

$product = $arResult['PRODUCT'];
$offers = $product->getOffers();
$firstOffer = $offers->first();

$arParams['ITEM_ATTR_ID'] = isset($arParams['ITEM_ATTR_ID']) ? trim($arParams['ITEM_ATTR_ID']) : '';
if (!strlen($arParams['ITEM_ATTR_ID'])) {
    $arParams['ITEM_ATTR_ID'] = $this->GetEditAreaId($product->getId().'_'.md5($this->randString()));
}

?>
    <div class="b-common-item js-product-item" id="<?= $arParams['ITEM_ATTR_ID'] ?>"><?php
        
        /** @todo шильдики акций */
        
        ?><span class="b-common-item__image-wrap"><?php
            if ($currentOffer->getImagesIds()) {
                ?><img class="b-common-item__image js-weight-img"
                       src="<?= $currentOffer->getResizeImages(240, 240)->first() ?>"
                       alt="<?= $currentOffer->getName() ?>"
                       title=""><?php
            }
            ?></span>
        <div class="b-common-item__info-center-block">
            <a class="b-common-item__description-wrap track-recommendation" href="<?= $product->getDetailPageUrl() ?>">
            <span class="b-clipped-text b-clipped-text--three">
                <span><?php
                    echo '<strong>' . $product->getBrand()->getName() . '</strong>';
                    echo ' ';
                    echo $currentOffer->getName();
                    ?></span>
            </span>
            </a><?php
            
            //
            // Переключение торговых предложений
            //
            //$isWeightCapacityPacking = strlen(trim($product->getWeightCapacityPacking())) ? true : false;
            $isWeightCapacityPacking = true;
            if ($offers->count() > 1 || $isWeightCapacityPacking) {
                $isOffersPrinted     = false;
                $mainCombinationType = '';
                if ($currentOffer->getClothingSize()) {
                    $mainCombinationType = 'SIZE';
                } elseif ($currentOffer->getVolumeReference()) {
                    $mainCombinationType = 'VOLUME';
                } elseif ($isWeightCapacityPacking) {
                    $mainCombinationType = 'WEIGHT';
                }
                ob_start();
                ?>
                <div class="b-weight-container b-weight-container--list">
                <a class="b-weight-container__link b-weight-container__link--mobile js-mobile-select"
                   href="javascript:void(0);"></a>
                <ul class="b-weight-container__list"><?php
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
                                $weightGrams    = $catalogProduct->getWeight();
                                if ($weightGrams > 1000) {
                                    $value =
                                        ($weightGrams / 1000) . '&nbsp;' . Loc::getMessage(
                                            'CATALOG_ITEM_SNIPPET_VERTICAL.MEASURE_KG'
                                        );
                                } else {
                                    $value =
                                        $weightGrams . '&nbsp;' . Loc::getMessage(
                                            'CATALOG_ITEM_SNIPPET_VERTICAL.MEASURE_G'
                                        );
                                }
                                break;
                        }
                        if (!strlen($value)) {
                            continue;
                        }
                        $isOffersPrinted = true;
                        $addAttr         = '';
                        $addAttr         .= ' data-price="' . $offer->getPrice() . '"';
                        $addAttr         .= ' data-offerid="' . $offer->getId() . '"';
                        $addAttr         .= ' data-image="' . $offer->getResizeImages(240, 240)->first() . '"';
                        $addAttr         .= ' data-name="' . $offer->getName() . '"';
                        $addClass        = $currentOffer->getId() === $offer->getId() ? ' active-link' : '';
                        ?>
                        <li class="b-weight-container__item">
                        <a<?= $addAttr ?> href="javascript:void(0)"
                                          class="b-weight-container__link js-price<?= $addClass ?>"><?= $value ?></a>
                        </li><?php
                    }
                    ?></ul>
                </div><?php
                if ($isOffersPrinted) {
                    echo ob_get_clean();
                } else {
                    ob_end_clean();
                }
            }
            
            //
            // Кнопка добавления в корзину
            //
            ?><a class="b-common-item__add-to-cart js-basket-add track-recommendation"
                 href="javascript:void(0);"
                 data-url="/ajax/sale/basket/add/"
                 data-offerid="<?= $currentOffer->getId() ?>">
            <span class="b-common-item__wrapper-link">
                <span class="b-cart">
                    <span class="b-icon b-icon--cart"><?php
                        echo new SvgDecorator('icon-cart', 16, 16);
                        ?></span>
                </span>
                <span class="b-common-item__price js-price-block"><?=$firstOffer->getPrice()?></span>
                <span class="b-common-item__currency"><span class="b-ruble">₽</span></span>
            </span>
            </a><?php
            
            //
            // Информация об особенностях покупки товара
            //
            ob_start();
            
            /** @todo инфо о скидке */
            
            if ($currentOffer->isByRequest()) {
                ?>
                <div class="b-common-item__info-wrap">
                <span class="b-common-item__text"><?= Loc::getMessage(
                        'CATALOG_ITEM_SNIPPET_VERTICAL.ORDER_BY_REQUEST'
                    ) ?></span>
                </div><?php
            }
            
            /** @todo инфо о доставке/самовывозе */
            $addInfo = ob_get_clean();
            if (strlen($addInfo)) {
                echo '<div class="b-common-item__additional-information">' . $addInfo . '</div>';
            }
            ?>
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
    $jsProduct   = [
        'ID'     => $product->getId(),
        'RCM_ID' => $arParams['BIG_DATA']['RCM_ID'] ?? '',
    ];
    $jsSelectors = [
        'item' => '#'.$arParams['ITEM_ATTR_ID'],
        'trackRecommendation' => '#'.$arParams['ITEM_ATTR_ID'].' .track-recommendation',
    ];
    $jsParams    = [
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