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
                            <?php foreach ($arResult['SHOPS'] as $shop) {
                                /** @var StockResultCollection $stockResult */
                                $stockResult = $arResult['STOCK_RESULT_BY_SHOP'][$shop->getXmlId()]['STOCK_RESULT'];
                                $available = $stockResult->getAvailable();
                                $delayed = $stockResult->getDelayed();
                                ?>
                                <li class="b-delivery-list__item">
                                    <a class="b-delivery-list__link js-shop-link b-active"
                                       id="shop_id<?= $shop->getXmlId() ?>"
                                       data-shop-id="<?= $shop->getXmlId() ?>"
                                       href="javascript:void(0);"
                                       title="">
                                        <span class="b-delivery-list__col b-delivery-list__col--addr">
                                            <span class="b-delivery-list__col b-delivery-list__col--color b-delivery-list__col--blue"></span>
                                            <?= $shop->getAddress() ?>
                                        </span>
                                        <span class="b-delivery-list__col b-delivery-list__col--time"><?= $shop->getSchedule(
                                            ) ?></span>
                                        <?php if (!$available->isEmpty()) { ?>
                                            <span class="b-delivery-list__col b-delivery-list__col--self-picked">
                                                <?php
                                                $deliveryDate = $available->getDeliveryDate();
                                                $str = getDateDiffString($currentDate, $deliveryDate);
                                                ?>
                                                заказ можно забрать <?= $str ?>
                                                </span>
                                        <?php } else { ?>
                                            <span class="b-delivery-list__col b-delivery-list__col--self-picked">
                                                полный заказ будет доступен <?= mb_strtolower(
                                                    FormatDate(
                                                        'd.m (D) с H:00',
                                                        $stockResult->getDeliveryDate()->getTimestamp()
                                                    )
                                                ) ?>
                                            </span>
                                        <?php } ?>
                                    </a>
                                    <div class="b-order-info-baloon">
                                        <a class="b-link b-link--popup-back b-link--order b-link--desktop js-close-order-baloon"
                                           href="javascript:void(0);"
                                           title="">
                                            <span class="b-icon b-icon--back-long b-icon--balloon">
                                                <?= new SvgDecorator('icon-back-form', 13, 11) ?>
                                            </span>
                                            Вернуться к списку
                                        </a>
                                        <a class="b-link b-link--popup-back b-link--baloon js-close-order-baloon"
                                           href="javascript:void(0);">Пункт самовывоза</a>
                                        <div class="b-order-info-baloon__content js-order-info-baloon-scroll">
                                            <ul class="b-delivery-list">
                                                <li class="b-delivery-list__item b-delivery-list__item--myself">
                                                <span class="b-delivery-list__link b-delivery-list__link--myself">
                                                    <span class="b-delivery-list__col b-delivery-list__col--color <?= $shop->getMetro(
                                                    ) ?>"></span>
                                                    <?= $shop->getAddress() ?></span>
                                                </li>
                                            </ul>
                                            <div class="b-input-line b-input-line--myself">
                                                <div class="b-input-line__label-wrapper">
                                                    <span class="b-input-line__label">Время работы</span>
                                                </div>
                                                <div class="b-input-line__text-line b-input-line__text-line--myself">
                                                    <?= $shop->getSchedule() ?>
                                                </div>
                                            </div>
                                            <?php if (!$available->isEmpty()) { ?>
                                                <div class="b-input-line b-input-line--myself">
                                                    <div class="b-input-line__label-wrapper">
                                                        <? $str = getDateDiffString(
                                                            $currentDate,
                                                            $available->getDeliveryDate()
                                                        ) ?>
                                                        <?php if ($delayed->isEmpty()) { ?>
                                                        <span class="b-input-line__label"> Можно забрать <?= $str ?></span>
                                                        <?php } else { ?>
                                                        <span class="b-input-line__label"> Можно забрать <?= $str ?>, кроме </span>
                                                        <ol class="b-input-line__text-list">
                                                            <?php /** @var StockResult $delayedItem */ ?>
                                                            <?php foreach ($delayed as $delayedItem) { ?>
                                                                <li class="b-input-line__text-item">
                                                                    <?php $delayedItem->getOffer()
                                                                                      ->getProduct()
                                                                                      ->getName() ?>
                                                                    <?php if ($delayedItem->getAmount() > 1) { ?>
                                                                        (<?= $delayedItem->getAmount() ?> шт)
                                                                    <?php } ?>
                                                                </li>
                                                            <?php } ?>
                                                        </ol>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            <?php } ?>
                                            <?php if (!$delayed->isEmpty()) { ?>
                                                <div class="b-input-line b-input-line--myself">
                                                    <div class="b-input-line__label-wrapper">
                                                        <span class="b-input-line__label"> Полный заказ будет доступен </span>
                                                    </div>
                                                    <div class="b-input-line__text-line">
                                                        <?= mb_strtolower(
                                                            FormatDate(
                                                                'd.m (D) с H:00',
                                                                $stockResult->getDeliveryDate()->getTimestamp()
                                                            )
                                                        ) ?>
                                                    </div>
                                                </div>
                                            <?php } ?>
                                            <div class="b-input-line b-input-line--myself">
                                                <div class="b-input-line__label-wrapper">
                                                    <span class="b-input-line__label"> Оплата в магазине </span>
                                                </div>
                                                <div class="b-input-line__text-line">
                                                    <span class="b-input-line__pay-type">
                                                        <span class="b-icon b-icon--icon-cash">
                                                            <?= new SvgDecorator('icon-cash', 16, 12) ?>
                                                        </span>
                                                        наличными
                                                    </span>
                                                    <span class="b-input-line__pay-type">
                                                        <span class="b-icon b-icon--icon-bank">
                                                            <?= new SvgDecorator('icon-bank-card', 16, 12) ?>
                                                        </span>
                                                        банковской картой
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="b-input-line b-input-line--pin">
                                                <a class="b-link b-link--pin js-shop-link"
                                                   href="javascript:void(0);"
                                                   title="">
                                                <span class="b-icon b-icon--pin">
                                                    <?= new SvgDecorator('icon-geo', 16, 16) ?>
                                                </span> Показать на карте </a>
                                            </div>
                                            <a class="b-button b-button--order-balloon js-shop-myself"
                                               href="javascript:void(0);"
                                               title=""
                                               data-shopId="<?= $shop->getXmlId() ?>"
                                               data-url="json/mapobjects-order-shop.json"> Выбрать этот пункт
                                                самовывоза </a>
                                        </div>
                                    </div>
                                </li>
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
