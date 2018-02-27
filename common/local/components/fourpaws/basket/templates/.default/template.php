<?php
/**
 * Created by PhpStorm.
 * Date: 29.12.2017
 * Time: 16:26
 *
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 */

/** @global \FourPaws\Components\BasketComponent $component */

use FourPaws\Decorators\SvgDecorator;
use FourPaws\Helpers\WordHelper;

/** @var \Bitrix\Sale\Basket $basket */
$basket = $arResult['BASKET'];
$orderableBasket = $basket->getOrderableItems();

/** @var \Bitrix\Sale\Order $order */
$order = $basket->getOrder();

if (!isset($arParams['IS_AJAX']) || $arParams['IS_AJAX'] !== true) {
    ?>
    <div class="b-shopping-cart">
    <div class="b-preloader">
        <div class="b-preloader__spinner">
            <img class="b-preloader__image" src="/static/build/images/inhtml/spinner.svg" alt="spinner" title="">
        </div>
    </div>
    <?php
}
?>
    <div class="b-container js-cart-wrapper">
        <h1 class="b-title b-title--h1 b-title--shopping-cart">Корзина</h1>
        <main class="b-shopping-cart__main" role="main">
            <?php
            if ($arResult['POSSIBLE_GIFT_GROUPS']) {

                ?>
                <section class="b-stock b-stock--shopping-cart">
                    <h3 class="b-title b-title--h2-cart">Подарки к заказу
                    </h3>
                    <?php
                    foreach ($arResult['POSSIBLE_GIFT_GROUPS'] as $group) {
                        $group = current($group);
                        $disableClass = '';
                        if (1 > $component->basketService->getAdder()->getExistGiftsQuantity($group, false)) {
                            $disableClass = ' b-link-gift--disabled';
                        }
                        ?>
                        <div class="b-gift-order">
                            <div class="b-gift-order__info">
                        <span class="b-gift-order__text">
                            Мы решили подарить вам подарок на весь заказ за красивые глаза
                        </span>
                                <a
                                        class="b-link-gift js-presents-order-open<?= $disableClass; ?>"
                                        href="javascript:void(0);"
                                        data-url="/ajax/sale/basket/gift/get/"
                                        data-url-gift="/ajax/sale/basket/gift/select/"
                                        data-discount-id="<?= $group['discountId']; ?>" title=""
                                        data-popup-id="popup-choose-gift">
                                    <span class="b-link-gift__text">Выбрать подарок</span>
                                    <span class="b-icon b-icon--gift">
                                    <?= new SvgDecorator('icon-gift', 18, 18); ?>
                                    </span>
                                </a>
                            </div>
                            <?php
                            if (
                                isset($arResult['SELECTED_GIFTS'][$group['discountId']])
                                && !empty($arResult['SELECTED_GIFTS'][$group['discountId']])
                            ) {
                                ?>
                                <div class="b-gift-order__gift-product js-section-remove-stock">
                                    <?php
                                    foreach ($arResult['SELECTED_GIFTS'][$group['discountId']] as $gift) {
                                        for ($i = 0; $i < $gift['quantity']; ++$i) {
                                            $offer = $component->offerCollection->getById($gift['offerId']);
                                            $image = $component->getImage($gift['offerId']);
                                            $product = $offer->getProduct();
                                            $name = '<strong>' . $product->getBrandName() . '</strong> ' . lcfirst(trim($product->getName()));
                                            ?>
                                            <div class="b-common-item b-common-item--shopping-cart js-remove-shopping">
                                    <span class="b-common-item__image-wrap b-common-item__image-wrap--shopping-cart">
                                        <img class="b-common-item__image b-common-item__image--shopping-cart"
                                             src="<?= $image; ?>" alt="<?= $product->getName(); ?>">
                                    </span>
                                                <div class="b-common-item__info-center-block b-common-item__info-center-block--shopping-cart">
                                                    <a class="b-common-item__description-wrap"
                                                       href="javascript:void(0);"
                                                       title="">
                                                    <span class="b-clipped-text b-clipped-text--shopping-cart">
                                                        <span>
                                                            <?= $name; ?>
                                                        </span>
                                                    </span>
                                                        <!--                                            <span class="b-common-item__variant b-common-item__variant--shopping-cart">-->
                                                        <!--                                                <span class="b-common-item__name-value">-->
                                                        <!--                                                    Цвет:-->
                                                        <!--                                                </span>-->
                                                        <!--                                                <span>прозрачные</span>-->
                                                        <!--                                            </span>-->
                                                    </a>
                                                    <a class="b-common-item__delete js-present-delete-item"
                                                       href="javascript:void(0);" title=""
                                                       data-url="/ajax/sale/basket/gift/refuse/"
                                                       data-gift-id="<?= $gift['basketId']; ?>">
                                            <span class="b-icon b-icon--delete">
                                                <?= new SvgDecorator('icon-delete-cart-product', 12, 14); ?>
                                            </span>
                                                    </a>
                                                </div>
                                            </div>
                                            <?php
                                        }
                                    }
                                    ?>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                        <?php
                    }
                    ?>
                </section>
                <?php
            }
            ?>
            <section class="b-stock b-stock--shopping-cart b-stock--shopping-product js-section-remove-stock">
                <h3 class="b-title b-title--h2-cart b-title--shopping-product">Ваш заказ</h3>
                <?php
                /** @var \Bitrix\Sale\BasketItem $basketItem */
                foreach ($orderableBasket as $basketItem) {
                    if (isset($basketItem->getPropertyCollection()->getPropertyValues()['IS_GIFT'])) {
                        continue;
                    }
                    $image = $component->getImage($basketItem->getProductId()); ?>
                    <div class="b-item-shopping">
                        <div class="b-common-item b-common-item--shopping-cart b-common-item--shopping">
                        <span class="b-common-item__image-wrap b-common-item__image-wrap--shopping-cart">
                            <?php
                            if (null !== $image) {
                                ?>
                                <img class="b-common-item__image b-common-item__image--shopping-cart"
                                     src="<?= $image; ?>"
                                     alt="<?= $basketItem->getField('NAME') ?>" title=""/>
                                <?php
                            }
                            ?>
                        </span>
                            <div class="b-common-item__info-center-block b-common-item__info-center-block--shopping-cart b-common-item__info-center-block--shopping">
                                <a class="b-common-item__description-wrap b-common-item__description-wrap--shopping"
                                   href="<?= $basketItem->getField('DETAIL_PAGE_URL'); ?>" title="">
                                    <span class="b-clipped-text b-clipped-text--shopping-cart">
                                        <span>
                                            <?php if ($offer !== 'null') { ?>
                                                <strong><?= $offer->getProduct()->getBrand() ?>  </strong>
                                            <?php } ?>
                                            <?= $basketItem->getField('NAME') ?>
                                        </span>
                                    </span>
                                    <span class="b-common-item__variant b-common-item__variant--shopping-cart b-common-item__variant--shopping">
                                         <span class="b-common-item__name-value">Вес: </span>
                                         <span><?= WordHelper::showWeight($basketItem->getWeight(), true) ?></span>
                                    </span>
                                    <?php if ($offer !== null) {
                                        $color = $offer->getColor();
                                        if ($color !== null) { ?>
                                            <span class="b-common-item__variant b-common-item__variant--shopping-cart b-common-item__variant--shopping">
                                                <span class="b-common-item__name-value">Цвет: </span>
                                                <span><?= $color->getName() ?></span>
                                            </span>
                                        <?php }
                                        $article = $offer->getXmlId();
                                        if (!empty($article)) { ?>
                                            <span class="b-common-item__variant b-common-item__variant--shopping-cart b-common-item__variant--shopping">
                                                <span class="b-common-item__name-value">Артикул: </span>
                                                <span class="b-common-item__name-value b-common-item__name-value--shopping-mobile">, Арт. </span><span><?= $article ?></span>
                                            </span>
                                        <?php }
                                    } ?>
                                </a>
                                <?php if ($offer !== null) {
                                    $bonus = $component->getItemBonus($offer);
                                    if ($bonus > 0) {
                                        $bonus = floor($bonus); ?>
                                        <span class="b-common-item__rank-text b-common-item__rank-text--red b-common-item__rank-text--shopping">+ <?= WordHelper::numberFormat($bonus,
                                                0) ?>
                                            <?= WordHelper::declension($bonus,
                                                ['бонус', 'бонуса', 'бонусов']) ?> </span>
                                    <?php }
                                } ?>
                            </div>
                        </div>
                        <div class="b-item-shopping__operation">
                            <?php $offer = $component->getOffer($basketItem->getProductId());
                            $maxQuantity = 1000;
                            if ($offer !== null) {
                                $maxQuantity = $offer->getQuantity();
                            } ?>
                            <div class="b-plus-minus b-plus-minus--half-mobile b-plus-minus--shopping js-plus-minus-cont">
                                <a class="b-plus-minus__minus js-minus" data-url="/ajax/sale/basket/update/"
                                   href="javascript:void(0);"></a>

                                <input title="" class="b-plus-minus__count js-plus-minus-count"
                                       value="<?= WordHelper::numberFormat($basketItem->getQuantity(), 0) ?>"
                                       data-one-price="<?= $basketItem->getPrice() ?>"
                                       data-cont-max="<?= $maxQuantity ?>"
                                       data-basketid="<?= $basketItem->getId(); ?>" type="text"/>

                                <a class="b-plus-minus__plus js-plus" data-url="/ajax/sale/basket/update/"
                                   href="javascript:void(0);"></a>
                            </div>
                            <div class="b-select b-select--shopping-cart">
                                <?php /** @todo mobile max quantity */
                                $maxMobileQuantity = 100;
                                if ($maxQuantity < $maxMobileQuantity) {
                                    $maxMobileQuantity = $maxMobileQuantity;
                                } ?>
                                <select title="" class="b-select__block b-select__block--shopping-cart"
                                        name="shopping-cart">
                                    <option value="" disabled="disabled" selected="selected">выберите</option>
                                    <?php
                                    for ($i = 0; $i < $maxMobileQuantity; $i++) { ?>
                                        <option value="one-click-<?= $i ?>"><?= $i + 1 ?></option>
                                        <?php
                                    } ?>
                                </select>
                            </div>
                            <div class="b-price">
                                <span class="b-price__current"><?= WordHelper::numberFormat($basketItem->getPrice()
                                        * $basketItem->getQuantity()) ?>  </span>
                                <span class="b-ruble">₽</span>
                                <?php
                                if ($basketItem->getDiscountPrice() > 0) {
                                    ?>
                                    <span class="b-old-price b-old-price--crossed-out">
                                        <span class="b-old-price__old"><?= WordHelper::numberFormat($basketItem->getBasePrice()
                                                * $basketItem->getQuantity()) ?>  </span>
                                        <span class="b-ruble b-ruble--old-weight-price">₽</span>
                                    </span>
                                    <?php
                                }
                                ?>
                            </div>
                            <a class="b-item-shopping__delete js-cart-delete-item" href="javascript:void(0);" title=""
                               data-url="/ajax/sale/basket/delete/" data-basketId="<?= $basketItem->getId(); ?>">
                            <span class="b-icon b-icon--delete b-icon--shopping">
                                <?= new SvgDecorator('icon-delete-cart-product', 12, 14); ?>
                            </span>
                            </a>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </section>
        </main>

        <aside class="b-shopping-cart__aside">
            <div class="b-information-order">
                <div class="b-information-order__client">
                    <?php
                    /** @todo available user bonus */ ?>
                    <!-- <span class="b-information-order__pay-points">
                        <span class="b-information-order__name">Константин, </span>
                        вы можете оплатить этот заказ баллами (до 299).
                    </span> -->
                    <?php
                    $APPLICATION->IncludeComponent(
                        'fourpaws:city.selector',
                        'basket.summary',
                        [],
                        $this,
                        ['HIDE_ICONS' => 'Y']
                    );
                    ?>

                    <p class="b-information-order__additional-info">
                        От города доставки зависит наличие товаров и параметры доставки.
                    </p>
                    <?php
                    $APPLICATION->IncludeComponent(
                        'fourpaws:city.delivery.info',
                        'basket.summary',
                        ['BASKET_PRICE' => $basket->getPrice()],
                        false,
                        ['HIDE_ICONS' => 'Y']
                    );
                    ?>

                </div>
                <div class="b-information-order__order-wrapper">
                    <div class="b-information-order__order">
                        <div class="b-information-order__order-price"><?= WordHelper::numberFormat($arResult['TOTAL_QUANTITY'],
                                0) ?> <?= WordHelper::declension($arResult['TOTAL_QUANTITY'],
                                ['товар', 'товара', 'товаров']) ?>
                            (<?= WordHelper::showWeight($arResult['BASKET_WEIGHT'], true) ?>)
                        </div>
                        <div class="b-price b-price--information-order">
                            <span class="b-price__current">
                                <?= WordHelper::numberFormat($basket->getBasePrice()); ?>
                            </span><span class="b-ruble">₽</span>
                        </div>
                    </div>
                    <?php
                    if ($basket->getBasePrice() - $basket->getPrice() > 0.01) {
                        ?>
                        <div class="b-information-order__order">
                            <div class="b-information-order__order-price">Общая скидка
                            </div>
                            <div class="b-price b-price--information-order">
                                <span class="b-price__current">
                                    - <?= WordHelper::numberFormat($basket->getBasePrice() - $basket->getPrice()); ?>
                                </span><span class="b-ruble">₽</span>
                            </div>
                        </div>
                        <?php
                    }
                    /** @todo promo */
                    ?>
                    <!--                    <form class="b-information-order__form-promo js-form-validation">-->
                    <!--                        <div class="b-input b-input--form-promo"><input-->
                    <!--                                    class="b-input__input-field b-input__input-field--form-promo" type="text"-->
                    <!--                                    id="promocode-delivery" placeholder="Промо-код" name="text" data-url=""/>-->
                    <!--                            <div class="b-error"><span class="js-message"></span>-->
                    <!--                            </div>-->
                    <!--                        </div>-->
                    <!--                        <button class="b-button b-button--form-promo">Применить-->
                    <!--                        </button>-->
                    <!--                    </form>-->
                    <div class="b-information-order__order b-information-order__order--total">
                        <div class="b-information-order__order-price">Итого без учета доставки
                        </div>
                        <div class="b-price b-price--information-order b-price--total-price">
                            <span class="b-price__current">
                                <?= WordHelper::numberFormat($basket->getPrice()); ?>
                            </span><span class="b-ruble">₽</span>
                        </div>
                    </div>
                    <a class="b-button b-button--start-order" href="/sale/order/" title="Начать оформление">
                        Начать оформление
                    </a>
                    <div class="b-information-order__one-click">
                        <a class="b-link b-link--one-click js-open-popup js-open-popup--one-click js-open-popup"
                           href="javascript:void(0)" title="Купить в 1 клик" data-popup-id="buy-one-click"
                           data-url="/ajax/fast_order/load/">
                            <span class="b-link__text b-link__text--one-click js-open-popup">Купить в 1 клик</span>
                        </a>
                    </div>
                </div>
            </div>
        </aside>

        <?php

        /**
         * Выгодная покупка
         */
        $productsIds = [];
        foreach ($orderableBasket as $basketItem) {
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
