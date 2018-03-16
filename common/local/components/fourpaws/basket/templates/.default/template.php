<?php
/**
 * Created by PhpStorm.
 * Date: 29.12.2017
 * Time: 16:26
 *
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 */

/** @global BasketComponent $component */

use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Order;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\Components\BasketComponent;
use FourPaws\Decorators\SvgDecorator;
use FourPaws\Helpers\WordHelper;
use FourPaws\SaleBundle\Entity\UserAccount;
use FourPaws\UserBundle\Entity\User;

/** @var User $user */
$user = $arResult['USER'];
/** @var UserAccount $userAccount */
$userAccount = $arResult['USER_ACCOUNT'];

/** @var Basket $basket */
$basket = $arResult['BASKET'];
$orderableBasket = $basket->getOrderableItems();

/** @var ArrayCollection $notAlowedItems */
$notAlowedItems = $arResult['NOT_ALOWED_ITEMS'];

/** @var Order $order */
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
                        /** @noinspection PhpUndefinedMethodInspection */
                        if (1 > $component->basketService->getAdder('gift')->getExistGiftsQuantity($group, false)) {
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
            if (!$orderableBasket->isEmpty()) { ?>
                <section class="b-stock b-stock--shopping-cart b-stock--shopping-product js-section-remove-stock">
                    <h3 class="b-title b-title--h2-cart b-title--shopping-product">Ваш заказ</h3>
                    <?php
                    /** @var BasketItem $basketItem */
                    foreach ($orderableBasket as $basketItem) {
                        if (isset($basketItem->getPropertyCollection()->getPropertyValues()['IS_GIFT'])) {
                            continue;
                        }
                        require __DIR__ . '/basketItem.php';
                    }
                    ?>
                </section>
            <?php }
            if (!$notAlowedItems->isEmpty()) { ?>
                <section class="b-stock b-stock--shopping-cart b-stock--shopping-product js-section-remove-stock">
                    <h3 class="b-title b-title--h2-cart b-title--shopping-product">Под заказ</h3>
                    <?php foreach ($notAlowedItems as $basketItem) {
                        if (isset($basketItem->getPropertyCollection()->getPropertyValues()['IS_GIFT'])) {
                            continue;
                        }
                        require __DIR__ . '/basketItem.php';
                    } ?>
                </section>
            <?php } ?>
        </main>

        <aside class="b-shopping-cart__aside">
            <div class="b-information-order">
                <div class="b-information-order__client">
                    <?php if ($user) { ?>
                        <?php if (!empty($userAccount)) { ?>
                            <span class="b-information-order__pay-points">
                                <span class="b-information-order__name"><?= $user->getName() ?>, </span>
                                вы можете оплатить этот заказ баллами (до <?= WordHelper::numberFormat($userAccount->getCurrentBudget()) ?>
                                ).
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
                                <?= WordHelper::numberFormat($arResult['TOTAL_BASE_PRICE']); ?>
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
                                    - <?= WordHelper::numberFormat($arResult['TOTAL_DISCOUNT']); ?>
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
                                <?= WordHelper::numberFormat($arResult['TOTAL_PRICE']); ?>
                            </span><span class="b-ruble">₽</span>
                        </div>
                    </div>
                    <a class="b-button b-button--start-order"
                       href="<?= (int)$arResult['TOTAL_PRICE'] === 0 ? 'javascript:void(0)' : '/sale/order/' ?>"
                       title="Начать оформление" <?= (int)$arResult['TOTAL_PRICE'] === 0 ? ' disabled' : '' ?>>
                        Начать оформление
                    </a>
                    <div class="b-information-order__one-click">
                        <a class="b-link b-link--one-click <?= (int)$arResult['TOTAL_PRICE'] === 0 ? '' : ' js-open-popup js-open-popup--one-click' ?>"
                           href="javascript:void(0)" title="Купить в 1 клик"
                            <?= (int)$arResult['TOTAL_PRICE'] === 0 ? '' : ' data-popup-id="buy-one-click" data-url="/ajax/sale/fast_order/load/"' ?>
                            <?= (int)$arResult['TOTAL_PRICE'] === 0 ? ' disabled' : '' ?>>
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
