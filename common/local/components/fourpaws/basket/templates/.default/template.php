<?php

/**
 * @global BasketComponent $component
 * @global \CMain $APPLICATION
 */

use Bitrix\Sale\Basket;use Bitrix\Sale\BasketItem;use Bitrix\Sale\Order;use Doctrine\Common\Collections\ArrayCollection;use FourPaws\Catalog\Collection\OfferCollection;use FourPaws\Catalog\Model\Offer;use FourPaws\Components\BasketComponent;use FourPaws\Decorators\SvgDecorator;use FourPaws\Helpers\WordHelper;use FourPaws\KioskBundle\Service\KioskService;use FourPaws\UserBundle\Entity\User;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var User $user */
$user = $arResult['USER'];

/**
 * @var $sViewportCookie - Значение куки отвечающе за переключение вьпорта с мобильного на десктоп.
 */
$sViewportCookie = $_COOKIE['viewport'] ?? null;

/** @var Basket $basket */
$basket = $arResult['BASKET'];
$orderableItems = $basket->getOrderableItems();
$hasNormalItems = false;
/** @var BasketItem $item */
foreach ($orderableItems as $item) {
    if (isset($item->getPropertyCollection()->getPropertyValues()['IS_GIFT'])) {
        continue;
    }

    $hasNormalItems = true;
    break;
}

/** @var ArrayCollection $notAllowedItems */
$notAllowedItems = $arResult['NOT_ALLOWED_ITEMS'];
$hasNotAllowedItems = false;
/** @var BasketItem $item */
foreach ($notAllowedItems as $item) {
    if (isset($item->getPropertyCollection()->getPropertyValues()['IS_GIFT'])) {
        continue;
    }

    $hasNotAllowedItems = true;
    break;
}

/** @var Order $order */
$order = $basket->getOrder();

if ($arResult['ECOMMERCE_VIEW_BASKET']) {
    echo $arResult['ECOMMERCE_VIEW_BASKET'];
}

if (!isset($arParams['IS_AJAX']) || $arParams['IS_AJAX'] !== true) { ?>
    <div class="b-shopping-cart">
    <?php
    /** @noinspection UntrustedInclusionInspection */
    include SITE_TEMPLATE_PATH . '/blocks/preloader.php';
}

if ($arParams['IS_AJAX']) {
    $userDiscount = $component->getCurrentUserService()->getDiscount();
}

// разница цены по подписке
$subscribePriceDiff = $arResult['TOTAL_PRICE'] - $arResult['SUBSCRIBE_PRICE'];

?>
    <div class="b-container js-cart-wrapper">
        <h1 class="b-title b-title--h1 b-title--shopping-cart">Корзина</h1>
        <main class="b-shopping-cart__main" role="main">
            <div class="b-panel-subscribe-cart">
                <div class="b-panel-subscribe-cart__content">
                    <div class="b-panel-subscribe-cart__info">
                        Получите <span class="orange">скидку <?= ($subscribePriceDiff > 0) ? WordHelper::numberFormat($subscribePriceDiff, 2).' ₽' : ''?></span> на&nbsp;вашу корзину&nbsp;&mdash; используйте
                        <nobr><a href="javascript:void(0);" class="b-link" data-show-subscribe-delivery-cart="true">
                            <span class="logo-subscr"><?= new SvgDecorator('icon-logo-subscription', 20, 18) ?></span>
                            Подписку
                        </a></nobr> на доставку
                    </div>
                </div>
            </div>

            <?php if ($arResult['POSSIBLE_GIFT_GROUPS']) {
                ?>
                <section class="b-stock b-stock--shopping-cart">
                    <h3 class="b-title b-title--h2-cart">Подарки к заказу</h3>
                    <?php foreach ($arResult['POSSIBLE_GIFT_GROUPS'] as $group) {
                        $group = current($group);
                        $disableClass = '';

                        /** @noinspection PhpUndefinedMethodInspection */
                        $selectedCount = 0;
                        foreach($arResult['SELECTED_GIFTS'][$group['discountId']] as $giftOffer){
                            $selectedCount += $giftOffer['quantity'];
                        }
                        $availableGifts = $group['count'] - $selectedCount;

                        if (0 >= $availableGifts) {
                            $disableClass = ' b-link-gift--disabled';
                        }
                        if ($group['list'] instanceof OfferCollection) {
                            $giftCanBeRefused = $group['list']->count() > 1;
                        } elseif (\is_array($group['list'])) {
                            $giftCanBeRefused = count($group['list']) > 1;
                        }
                        ?>
                        <div class="b-gift-order">
                            <div class="b-gift-order__info">
                                <span class="b-gift-order__text">
                                    Подарок по акции —
                                    <span class="b-gift-order__text-additional"><?= $group['name'] ?></span>
                                </span>
                                <a class="b-link-gift js-presents-order-open<?= $disableClass ?>"
                                   href="javascript:void(0);"
                                   data-url="/ajax/sale/basket/gift/get/"
                                   data-url-gift="/ajax/sale/basket/gift/select/"
                                   data-available-gifts="<?= $availableGifts ?>"
                                   data-discount-id="<?= $group['discountId']; ?>" title=""
                                   data-popup-id="popup-choose-gift">
                                    <span class="b-link-gift__text">
                                        <?php if (
                                            isset($arResult['SELECTED_GIFTS'][$group['discountId']])
                                            && !empty($arResult['SELECTED_GIFTS'][$group['discountId']])
                                        ) { ?>
                                            Выбрать ещё
                                        <?php }else{ ?>
                                            Выбрать подарок
                                        <?php } ?>
                                    </span>
                                    <span class="b-icon b-icon--gift">
                                        <?= new SvgDecorator('icon-gift', 18, 18) ?>
                                    </span>
                                </a>
                            </div>
                            <?php if (
                                isset($arResult['SELECTED_GIFTS'][$group['discountId']])
                                && !empty($arResult['SELECTED_GIFTS'][$group['discountId']])
                            ) { ?>
                                <div class="b-gift-order__gift-product js-section-remove-stock">
                                    <?php foreach ($arResult['SELECTED_GIFTS'][$group['discountId']] as $gift) {
                                            $quantity = $gift['quantity'];
                                            $offer = $component->getOffer((int)$gift['offerId']);
                                            $image = $component->getImage((int)$gift['offerId']);
                                            /**
                                             * @var Offer $offer
                                             */
                                            $product = $offer->getProduct();
                                            $name = '<strong>' . $product->getBrandName() . '</strong> ' . \lcfirst(\trim($product->getName())); ?>
                                            <div class="b-common-item b-common-item--shopping-cart js-remove-shopping">
                                                <span class="b-common-item__image-wrap b-common-item__image-wrap--shopping-cart">
                                                    <img class="b-common-item__image b-common-item__image--shopping-cart"
                                                         src="<?= $image ?>" alt="<?= $product->getName() ?>">
                                                </span>
                                                <div class="b-common-item__info-center-block b-common-item__info-center-block--shopping-cart">
                                            <?php
                                                if ($offer->getPrice() && strpos($offer->getXmlId(), '1') === 0) {
                                                    ?>
                                                    <a class="b-common-item__description-wrap"
                                                       href="<?= $offer->getLink(); ?>" title="">
                                            <?php
                                                } else {
                                                    ?>
                                                    <div class="b-common-item__description-wrap">
                                            <?php
                                                }
                                                ?>

                                                        <span class="b-clipped-text b-clipped-text--shopping-cart b-clipped-text--gift-order">
                                                            <span><?= $name ?></span>
                                                        </span>
                                                            <?php /* <span class="b-common-item__variant b-common-item__variant--shopping-cart"><span class="b-common-item__name-value">Цвет:</span><span>прозрачные</span></span>*/ ?>
                                            <?php
                                                if (strpos($offer->getXmlId(), '1') === 0) {
                                                    ?>
                                                    </a>
                                            <?php
                                                } else {
                                                ?>
                                                    </div>
                                            <?php
                                                }
                                                ?>
                                                    <span class="b-common-item__description-wrap">
                                                        <span class="b-clipped-text b-clipped-text--shopping-cart b-clipped-text--gift-order">
                                                            <span>Количество: <?= $quantity;?></span>
                                                        </span>
                                                    </span>
                                                    <a class="b-common-item__delete js-present-delete-item"
                                                       href="javascript:void(0);" title=""
                                                       data-url="/ajax/sale/basket/gift/refuse/"
                                                       data-gift-id="<?= $gift['basketId']; ?>"
                                                       data-gift-quantity="<?= $quantity; ?>">
                                                        <?php
                                                        if ($giftCanBeRefused) {
                                                            ?>
                                                            <span class="b-icon b-icon--delete">
                                                                <?= new SvgDecorator(
                                                                    'icon-delete-cart-product',
                                                                    12,
                                                                    14
                                                                ); ?>
                                                            </span>
                                                            <?php
                                                        }
                                                        ?>
                                                    </a>
                                                </div>
                                            </div>
                                    <? } ?>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </section>
            <?php }

            if ($hasNormalItems) { ?>
                <section class="b-stock b-stock--shopping-cart b-stock--shopping-product js-section-remove-stock">

                    <div class="b-stock__header">
                        <h3 class="b-stock__header-title">Ваш заказ</h3>
                        <?
                        $APPLICATION->IncludeComponent(
                                'fourpaws:order.coupon.list',
                                '',
                                [
                                    'BASKET_ITEMS' => $orderableItems
                                ],
                                null,
                                ['HIDE_ICONS' => 'Y']
                        );
                        ?>
                    </div>

                    <?php
                    /** @var BasketItem $basketItem */
                    foreach ($orderableItems as $basketItem) {
                        if (isset($basketItem->getPropertyCollection()->getPropertyValues()['IS_GIFT'])) {
                            continue;
                        }

                        $offer = $component->getOffer((int)$basketItem->getProductId());

                        if (
                            $offer->isByRequest()
                            ||
                            in_array($basketItem->getBasketCode(),$arResult['SKIP_ROWS'], true)
                        ) {
                            continue;
                        }

                        require __DIR__ . '/basketItem.php';
                    }

                    $isOnlyPickup = false;
                    ?>
                </section>
            <?php }

            if ($hasNotAllowedItems) { ?>
                <section class="b-stock b-stock--shopping-cart b-stock--shopping-product js-section-remove-stock">
                    <h3 class="b-title b-title--h2-cart b-title--shopping-product">Под заказ</h3>
                    <?php foreach ($notAllowedItems as $basketItem) {
                        if (isset($basketItem->getPropertyCollection()->getPropertyValues()['IS_GIFT'])) {
                            continue;
                        }
                        $offer = $component->getOffer((int)$basketItem->getProductId());
                        require __DIR__ . '/basketItem.php';
                    } ?>
                </section>
            <?php } ?>

            <? if($arResult['SUBSCRIBE_ALLOWED']){
                include('include/subscribe_promo.php');
            } ?>
        </main>

        <aside class="b-shopping-cart__aside">
            <div class="b-information-order">
                <div class="b-information-order__client">
                    <?php if ($user) { ?>
                        <?php if ($arResult['MAX_BONUS_SUM']) { ?>
                            <span class="b-information-order__pay-points">
                                <span class="b-information-order__name"><?= $user->getName() ?>, </span>
                                вы можете оплатить этот заказ баллами (до <?= $arResult['MAX_BONUS_SUM'] ?>).
                            </span>
                        <?php }
                    } else { ?>
                        <span class="b-information-order__pay-points b-information-order__pay-points--flex">
                            Уже покупали у нас?
                            <a class="b-link-gift b-link-gift--shopping-aside js-open-popup" href="javascript:void(0);"
                               data-popup-id="authorization">
                                <span class="b-link-gift__text">Войти</span>
                            </a>
                        </span>
                    <?php }

                    $APPLICATION->IncludeComponent(
                        'fourpaws:city.selector',
                        'basket.summary',
                        [],
                        $component,
                        ['HIDE_ICONS' => 'Y']
                    ); ?>
                    <p class="b-information-order__additional-info">От Вашего города зависит наличие товаров и параметры
                        доставки.</p>
                    <?php $APPLICATION->IncludeComponent(
                        'fourpaws:city.delivery.info',
                        'basket.summary',
                        ['BASKET_PRICE' => $orderableItems->getPrice()],
                        false,
                        ['HIDE_ICONS' => 'Y']
                    ); ?>

                </div>
                <div class="b-information-order__order-wrapper">
                    <div class="b-information-order__order">
                        <div class="b-information-order__order-price"><?= WordHelper::numberFormat($arResult['TOTAL_QUANTITY'],
                                0) ?> <?= WordHelper::declension($arResult['TOTAL_QUANTITY'],
                                ['товар', 'товара', 'товаров']) ?>
                            <?php if ($arResult['BASKET_WEIGHT'] > 0) { ?>(<?= WordHelper::showWeight($arResult['BASKET_WEIGHT'],
                                true) ?>)<?php } ?>
                        </div>
                        <div class="b-price b-price--information-order">
                            <span class="b-price__current" data-basket-total-base-price>
                                <?= WordHelper::numberFormat($arResult['TOTAL_BASE_PRICE']); ?>
                            </span><span class="b-ruble">₽</span>
                        </div>
                    </div>

                    <?php if ($arResult['TOTAL_DISCOUNT'] >= 0.01) { ?>
                        <div class="b-information-order__order">
                            <div class="b-information-order__order-price">Общая скидка</div>
                            <div class="b-price b-price--information-order">
                                <span class="b-price__current">
                                    - <?= WordHelper::numberFormat($arResult['TOTAL_DISCOUNT']) ?>
                                </span>
                                <span class="b-ruble">₽</span>
                            </div>
                        </div>
                    <?php } ?>

                    <? $marksForBasket = 4; //TODO correct value (из $this->manzana->getStampsToBeAdded()) ?>
                    <div class="b-information-order__order b-information-order__order--mark">
                        <div class="">За заказ будет начислено:</div>
                        <div class="">
                            <?= $marksForBasket . '&nbsp;' . WordHelper::declension($marksForBasket, ['марка', 'марки', 'марок']) ?>
                        </div>
                    </div>

                    <?php if ($arResult['COUPON_DISCOUNT']) { ?>
                        <div class="b-information-order__order">
                            <div class="b-information-order__order-price">Скидка по промокоду</div>
                            <div class="b-price b-price--information-order">
                                <span class="b-price__current">-
                                    <?= WordHelper::numberFormat($arResult['COUPON_DISCOUNT']) ?>
                                </span>
                                <span class="b-ruble">₽</span>
                            </div>
                            <a class="b-information-order__delete-promocode js-promo-code-del"
                               href="javascript:void(0)" title="Сбросить промокод"
                               data-url="/ajax/sale/basket/promo/delete/"
                               data-promocode-id="<?= $arResult['COUPON'] ?>">
                                <span class="b-icon b-icon--delete-promocode">
                                    <?= new SvgDecorator('filter-change', 8, 8) ?>
                                </span>
                            </a>
                        </div>
                    <?php } else { ?>
                        <form class="b-information-order__form-promo js-form-validation js-promo-code-add" method="post"
                              data-url="/ajax/sale/basket/promo/apply/">
                            <div class="b-input b-input--form-promo">
                                <input class="b-input__input-field b-input__input-field--form-promo" type="text"
                                       id="promocode-delivery" placeholder="Промо-код" name="text"
                                       value="<?= $arResult['COUPON'] ?>"/>
                                <div class="b-error"><span class="js-message"></span></div>
                            </div>
                            <button class="b-button b-button--form-promo">Применить</button>
                        </form>
                    <?php } ?>

                    <div class="b-information-order__order-basediscount hidden" data-basket-base-discount>
                        <div class="b-information-order__order">
                            <div class="b-information-order__order-price">Общая скидка</div>
                            <div class="b-price b-price--information-order">
                                <span class="b-price__current" data-basket-base-discount-summ></span>
                                <span class="b-ruble">₽</span>
                            </div>
                        </div>
                    </div>

                    <div class="b-information-order__order-discounts" data-basket-coupons-discount-list></div>

                    <div class="b-information-order__order b-information-order__order--total">
                        <div class="b-information-order__order-price">Итого без учета доставки</div>
                        <div class="b-price b-price--information-order b-price--total-price">
                            <span class="b-price__current" data-basket-total-price>
                                <?= WordHelper::numberFormat($arResult['TOTAL_PRICE']); ?>
                            </span><span class="b-ruble">₽</span>
                        </div>
                    </div>
                    <form action="/sale/order/" method="post">
                        <button class="b-button b-button--start-order <?= $sViewportCookie === null ? 'b-button--bottom-indent' : '' ?>"
                           title="Начать оформление" <?= (int)$arResult['TOTAL_PRICE'] === 0 ? ' disabled' : '' ?>>
                            Начать оформление
                        </button>
                        <input type="hidden" name="default" value="1">
                    </form>
                    <?php if ($arResult['SHOW_FAST_ORDER']) { ?>
                        <div class="b-information-order__one-click">
                            <a class="b-link b-link--one-click <?= (int)$arResult['TOTAL_PRICE'] === 0 ? '' : ' js-open-popup js-open-popup--one-click' ?>"
                               href="javascript:void(0)" title="Купить в 1 клик"
                                <?= (int)$arResult['TOTAL_PRICE'] === 0 ? '' : ' data-popup-id="buy-one-click" data-url="/ajax/sale/fast_order/load/"' ?>
                                <?= (int)$arResult['TOTAL_PRICE'] === 0 ? ' disabled' : '' ?>>
                                <span class="b-link__text b-link__text--one-click js-open-popup">Купить в 1 клик</span>
                            </a>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </aside>

        <? if($arResult['SUBSCRIBE_ALLOWED']){
            $mobile = true;
            include('include/subscribe_promo.php');
        } ?>

        <script id="gtag-cart">
            let offers = [];
            $('a.b-common-item__description-wrap.b-common-item__description-wrap--shopping span.b-common-item__variant.b-common-item__variant--shopping-cart.b-common-item__variant--shopping span').each(function() {
                if(/^\d+$/.test($(this).html())) offers.push($(this).html());
            });
            $(document).ready(function () {
                gtag('event', 'page_view', {
                    'send_to': 'AW-832765585',
                    'ecomm_pagetype': 'cart',
                    'ecomm_prodid': offers,
                });
                $('#gtag-cart').appendTo('head');
            });
            </script>
        <?php

        /**
         * Выгодная покупка
         */
        $productsIds = [];
        foreach ($orderableItems as $basketItem) {
            $pId = (int)$basketItem->getProductId();
            $productInfo = CCatalogSku::GetProductInfo($pId);
            if ($productInfo) {
                $pId = (int)$productInfo['ID'];
            }
            if ($pId > 0) {
                $productsIds[] = $pId;
            }
        }

        if ($productsIds) {
            $APPLICATION->IncludeFile('blocks/components/followup_products.php',
                [
                    'WRAP_CONTAINER_BLOCK' => 'N',
                    'SHOW_TOP_LINE'        => 'Y',
                    'POSTCROSS_IDS'        => array_unique($productsIds),
                ],
                [
                    'SHOW_BORDER' => false,
                    'NAME'        => 'Блок выгодной покупки',
                    'MODE'        => 'php',
                ]);
        }

        /**
         * Просмотренные товары
         */
        $APPLICATION->IncludeFile('blocks/components/viewed_products.php',
            [
                'WRAP_CONTAINER_BLOCK' => 'N',
                'WRAP_SECTION_BLOCK'   => 'Y',
                'SHOW_TOP_LINE'        => 'Y',
                'SHOW_BOTTOM_LINE'     => 'N',
            ],
            [
                'SHOW_BORDER' => false,
                'NAME'        => 'Блок просмотренных товаров',
                'MODE'        => 'php',
            ]);
        ?></div>
<?php
if (!isset($arParams['IS_AJAX']) || $arParams !== true) {
    echo '</div>';
}
