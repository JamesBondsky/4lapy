<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var array $arParams
 * @var array $arResult
 * @var Basket $basket
 */

use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use FourPaws\Helpers\CurrencyHelper;
use FourPaws\Helpers\WordHelper;
use Bitrix\Main\Grid\Declension;
use FourPaws\Decorators\SvgDecorator;

$basket = $arResult['BASKET'];

$totalQuantity = array_sum($basket->getQuantityList());

/* @todo отображение акционных товаров */

?>
<aside class="b-order__list">
    <h4 class="b-title b-title--order-list js-popup-mobile-link">
        Заказ: <?= $totalQuantity ?> <?= (new Declension('товар', 'товара', 'товаров'))->get($totalQuantity) ?>
        (<?= WordHelper::showWeight($basket->getWeight()) ?>) на сумму <?= CurrencyHelper::formatPrice(
            $basket->getPrice()
        ) ?>
    </h4>
    <div class="b-order-list js-popup-mobile">
        <a class="b-link b-link--popup-back b-link--popup-choose-shop js-popup-mobile-close">Информация о
            заказе</a>
        <ul class="b-order-list__list js-order-list-block">
            <?php /* @var BasketItem $item */ ?>
            <?php foreach ($basket as $item) { ?>
                <li class="b-order-list__item">
                    <div class="b-order-list__order-text">
                        <div class="b-order-list__clipped-text">
                            <div class="b-order-list__text-backed">
                                <?= $item->getField('NAME') ?>
                                <?php if ($item->getQuantity() > 1) { ?>
                                    (<?= $item->getQuantity() ?> шт)
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <div class="b-order-list__order-value">
                        <?= CurrencyHelper::formatPrice($item->getPrice() * $item->getQuantity()) ?>
                    </div>
                </li>
            <?php } ?>
        </ul>
    </div>
    <?php /*
    <h4 class="b-title b-title--order-list js-popup-mobile-link js-basket-link"><span class="js-mobile-title-order">Останется в корзине: 5</span>
        товаров (4 кг) на сумму 8 566 ₽
    </h4>
    <div class="b-order-list b-order-list--aside js-popup-mobile">
        <a class="b-link b-link--popup-back b-link--popup-choose-shop js-popup-mobile-close">Информация о заказе</a>
        <ul class="b-order-list__list js-order-list-block">
            <li class="b-order-list__item b-order-list__item--aside">
                <div class="b-order-list__order-text b-order-list__order-text--aside">
                    <div class="b-order-list__clipped-text">
                        <div class="b-order-list__text-backed">Корм для кошек Хиллс Тунец стерилайз, меш. 8 кг
                        </div>
                    </div>
                </div>
                <div class="b-order-list__order-value b-order-list__order-value--aside">3 556 ₽
                </div>
            </li>
            <li class="b-order-list__item b-order-list__item--aside">
                <div class="b-order-list__order-text b-order-list__order-text--aside">
                    <div class="b-order-list__clipped-text">
                        <div class="b-order-list__text-backed">Фурминатор для больших кошек короткошерстных пород
                            7см
                        </div>
                    </div>
                </div>
                <div class="b-order-list__order-value b-order-list__order-value--aside">2 012 ₽
                </div>
            </li>
            <li class="b-order-list__item b-order-list__item--aside">
                <div class="b-order-list__order-text b-order-list__order-text--aside">
                    <div class="b-order-list__clipped-text">
                        <div class="b-order-list__text-backed">Moderna Туалет-домик для кошек 50см Friends forever
                            синий
                        </div>
                    </div>
                </div>
                <div class="b-order-list__order-value b-order-list__order-value--aside">2 669 ₽
                </div>
            </li>
            <li class="b-order-list__item b-order-list__item--aside">
                <div class="b-order-list__order-text b-order-list__order-text--aside">
                    <div class="b-order-list__clipped-text">
                        <div class="b-order-list__text-backed">Petmax Игрушка для кошек Мыши с перьями 7 см (2 шт)
                        </div>
                    </div>
                </div>
                <div class="b-order-list__order-value b-order-list__order-value--aside">299 ₽
                </div>
            </li>
        </ul>
    </div>
    <div class="b-order__link-wrapper">
        <a class="b-link b-link--order-gotobusket b-link--order-gotobusket"
           href="javascript:void(0)"
           title="Вернуться в корзину">
                <span class="b-icon b-icon--order-busket">
                    <?= new SvgDecorator('icon-reason', 16, 16) ?>
                </span>
            <span class="b-link__text b-link__text--order-gotobusket">Вернуться в корзину</span>
        </a>
    </div>
    */ ?>
</aside>
