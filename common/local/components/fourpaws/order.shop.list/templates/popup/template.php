<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

if (!$arResult['PICKUP_DELIVERY']) {
    return;
}

use Bitrix\Sale\Delivery\CalculationResult;
use Bitrix\Main\Grid\Declension;
use FourPaws\Decorators\SvgDecorator;
use FourPaws\DeliveryBundle\Collection\StockResultCollection;
use FourPaws\DeliveryBundle\Entity\StockResult;
use FourPaws\StoreBundle\Entity\Store;

/**
 * @var array $arResult
 * @var array $arParams
 * @var CalculationResult $pickup
 * @var StockResultCollection $resultByShop
 */

function getDateDiffString(\DateTime $currentDate, \DateTime $deliveryDate)
{
    if ($deliveryDate->format('d') == $currentDate->format('d')) {
        $hdiff = $deliveryDate->format('H') - $currentDate->format('H');
        $str = 'через ' . ($hdiff > 1 ? $hdiff : '') . ' ' . (new Declension(
                'час', 'часа', 'часов'
            ))->get($hdiff);
    } else {
        $str = FormatDate('X', $deliveryDate->getTimestamp());
    }

    return $str;
}

$pickup = $arResult['PICKUP_DELIVERY'];
$currentDate = new \DateTime();
?>
<section class="b-popup-wrapper__wrapper-modal b-popup-wrapper__wrapper-modal--order js-popup-section"
         data-popup="popup-order-stores">
    <section class="b-popup-pick-city b-popup-pick-city--order-stores js-popup-section" data-popup="popup-order-stores">
        <a class="b-popup-pick-city__close b-popup-pick-city__close--order js-close-popup"
           href="javascript:void(0);"
           title="Закрыть"></a>
        <div class="b-availability b-availability--order">
            <div class="b-availability__content b-availability__content--order js-availability-content">
                <div class="b-availability__info-block">
                    <a class="b-link b-link--popup-back b-link--popup-choose-shop js-close-popup"
                       href="javascript:void(0);">Выберите пункт самовывоза</a>
                    <h4 class="b-availability__header b-availability__header--desktop">
                        Наши магазины
                        <span class="b-availability__header-amount">(всего <?= count(
                                $arResult['STOCK_RESULT_BY_SHOP']
                            ) ?>)</span>
                    </h4>
                    <h4 class="b-availability__header b-availability__header--tablet active">Выберите пункт самовывоза
                    </h4>
                    <h4 class="b-availability__header b-availability__header--tablet b-availability__header--popuped">
                        Пункт самовывоза
                    </h4>
                    <ul class="b-availability-tab-list b-availability-tab-list--order js-availability-list">
                        <li class="b-availability-tab-list__item active">
                            <a class="b-availability-tab-list__link js-product-list"
                               href="javascript:void(0)"
                               aria-controls="shipping-list"
                               title="Списком">Списком</a>
                        </li>
                        <li class="b-availability-tab-list__item">
                            <a class="b-availability-tab-list__link js-product-map"
                               href="javascript:void(0)"
                               aria-controls="on-map"
                               title="На карте">На карте</a>
                        </li>
                    </ul>
                    <div class="b-stores-sort b-stores-sort--order b-stores-sort--balloon">
                        <div class="b-stores-sort__checkbox-block b-stores-sort__checkbox-block--balloon">
                            <?php /*
                            <div class="b-checkbox b-checkbox--stores b-checkbox--order">
                                <input class="b-checkbox__input"
                                       type="checkbox"
                                       name="stores-sort-time"
                                       id="stores-sort-1"/>
                                <label class="b-checkbox__name b-checkbox__name--stores b-checkbox__name--order"
                                       for="stores-sort-1">
                                    <span class="b-checkbox__text">работают
                                        <span class="b-checkbox__text-desktop">круглосуточно</span>
                                        <span class="b-checkbox__text-mobile">24 часа</span></span>
                                </label>
                            </div>
                            */ ?>
                            <?php if (!empty($arResult['SHOPS_FULL'])) { ?>
                                <div class="b-checkbox b-checkbox--stores b-checkbox--order">
                                    <input class="b-checkbox__input"
                                           type="checkbox"
                                           name="stores-sort-avlbl"
                                           id="stores-sort-2"
                                           value="в наличии сегодня"/>
                                    <label class="b-checkbox__name b-checkbox__name--stores b-checkbox__name--order"
                                           for="stores-sort-2"><span class="b-checkbox__text">в наличии сегодня</span>
                                    </label>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="b-form-inline b-form-inline--order-search">
                        <form class="b-form-inline__form">
                            <div class="b-input b-input--stores-search b-input--order-search">
                                <input class="b-input__input-field b-input__input-field--stores-search b-input__input-field--order-search"
                                       type="text"
                                       id="stores-search"
                                       placeholder="Поиск по адресу, метро и названию ТЦ"
                                       name="text"
                                       data-url="json/mapobjects-stores.json"/>
                                <div class="b-error">
                                    <span class="js-message"></span>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="b-tab-delivery b-tab-delivery--order js-content-list js-map-list-scroll">
                        <ul class="b-delivery-list b-delivery-list--order js-delivery-list">
                            <?php /** @var Store $shop */ ?>
                            <?php foreach ($arResult['SHOPS_FULL'] as $shop) {
                                /** @var StockResultCollection $stockResult */
                                $stockResult = $arResult['STOCK_RESULT_BY_SHOP'][$shop->getXmlId()]['STOCK_RESULT'];
                                if ($stockResult->getDelayed()->isEmpty()) {
                                    continue;
                                }
                                include 'include/shop.php';
                                ?>
                            <?php } ?>
                        </ul>
                        <h4 class="b-tab-delivery__addition-header" <?= empty($arResult['SHOPS_PARTIAL']) ? 'style="display:none"' : '' ?>>
                            Заказ в наличии частично
                        </h4>
                        <ul class="b-delivery-list b-delivery-list--order js-delivery-part-list" <?= empty($arResult['SHOPS_PARTIAL']) ? 'style="display:none"' : '' ?>>
                            <?php /** @var Store $shop */ ?>
                            <?php foreach ($arResult['SHOPS_PARTIAL'] as $shop) {
                                /** @var StockResultCollection $stockResult */
                                $stockResult = $arResult['STOCK_RESULT_BY_SHOP'][$shop->getXmlId()]['STOCK_RESULT'];
                                if ($stockResult->getDelayed()->isEmpty()) {
                                    continue;
                                }
                                include 'include/shop.php';
                                ?>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
                <div class="b-availability__show-block">
                    <div class="b-tab-delivery-map b-tab-delivery-map--order js-content-map">
                        <div class="b-tab-delivery-map__map" id="map" data-url="/ajax/store/list/order/">
                        </div>
                        <a class="b-link b-link--close-baloon js-product-list" href="javascript:void(0);" title="">
                            <span class="b-icon b-icon--close-baloon">
                                <?= new SvgDecorator('icon-close-baloon', 18, 18) ?>
                            </span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</section>
