<?php
/**
 * Created by PhpStorm.
 * Date: 29.12.2017
 * Time: 16:26
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 */

use FourPaws\Decorators\SvgDecorator;

/** @var \Bitrix\Sale\Basket $arResult ['BASKET'] */
$orderableBasket = $arResult['BASKET']->getOrderableItems();

?>

<div class="b-shopping-cart">
    <div class="b-container">
        <h1 class="b-title b-title--h1 b-title--shopping-cart">Корзина</h1>
        <main class="b-shopping-cart__main" role="main">
            <section class="b-stock b-stock--shopping-cart b-stock--shopping-product">
                <h3 class="b-title b-title--h2-cart b-title--shopping-product">Ваш заказ
                </h3>
                <?php
                /** @var \Bitrix\Sale\BasketItem $basketItem */
                foreach ($orderableBasket as $basketItem) {

                    ?>
                    <div class="b-item-shopping">
                        <div class="b-common-item b-common-item--shopping-cart b-common-item--shopping">
                        <span class="b-common-item__image-wrap b-common-item__image-wrap--shopping-cart">
                            <img class="b-common-item__image b-common-item__image--shopping-cart"
                                 src=""
                                 alt="<?= $basketItem->getField('NAME') ?>" title=""/>
                        </span>
                            <div class="b-common-item__info-center-block b-common-item__info-center-block--shopping-cart b-common-item__info-center-block--shopping">
                                <a class="b-common-item__description-wrap b-common-item__description-wrap--shopping"
                                   href="javascript:void(0);" title="">
                                    <span class="b-clipped-text b-clipped-text--shopping-cart">
                                        <span>
                                            <!--
                                            <strong>Moderna  </strong>
                                            миска пластиковая для кошек 210 мл friends forever
                                            -->
                                            <?= $basketItem->getField('NAME') ?>
                                        </span>
                                    </span>
                                    <!--
                                    <span class="b-common-item__variant b-common-item__variant--shopping-cart b-common-item__variant--shopping">
                                        <span class="b-common-item__name-value">Цвет: </span>
                                        <span>Синяя</span>
                                    </span>
                                    <span class="b-common-item__variant b-common-item__variant--shopping-cart b-common-item__variant--shopping">
                                        <span class="b-common-item__name-value">Артикул: </span>
                                        <span class="b-common-item__name-value b-common-item__name-value--shopping-mobile">, Арт. </span><span>1021531</span>
                                    </span>
                                    -->
                                </a>
                                <!--
                                <span class="b-common-item__rank-text b-common-item__rank-text--red b-common-item__rank-text--shopping">+ 6 бонусов </span>
                                -->
                            </div>
                        </div>
                        <div class="b-item-shopping__operation">
                            <div class="b-plus-minus b-plus-minus--half-mobile b-plus-minus--shopping js-plus-minus-cont">
                                <a
                                        class="b-plus-minus__minus js-minus" data-url="/ajax/sale/basket/update/"
                                        href="javascript:void(0);"></a>
                                <input
                                        title=""
                                        class="b-plus-minus__count js-plus-minus-count"
                                        value="<?= $basketItem->getQuantity() ?>"
                                        data-basketid="<?= $basketItem->getId(); ?>" type="text"/><a
                                        class="b-plus-minus__plus js-plus" data-url="/ajax/sale/basket/update/"
                                        href="javascript:void(0);"></a>
                            </div>
                            <div class="b-select b-select--shopping-cart">
                                <select title="" class="b-select__block b-select__block--shopping-cart" name="shopping-cart">
                                    <option value="" disabled="disabled" selected="selected">выберите</option>
                                    <option value="shopping-cart-0">1</option>
                                    <option value="shopping-cart-1">2</option>
                                    <option value="shopping-cart-2">3</option>
                                    <option value="shopping-cart-3">4</option>
                                    <option value="shopping-cart-4">5</option>
                                    <option value="shopping-cart-5">6</option>
                                    <option value="shopping-cart-6">7</option>
                                    <option value="shopping-cart-7">8</option>
                                    <option value="shopping-cart-8">9</option>
                                    <option value="shopping-cart-9">10</option>
                                </select>
                            </div>
                            <div class="b-price">
                                <span class="b-price__current"><?= $basketItem->getPrice() ?>  </span>
                                <span class="b-ruble">₽</span>
                                <?php
                                if ($basketItem->getDiscountPrice() > 0) {
                                    ?>
                                    <span class="b-old-price b-old-price--crossed-out">
                                        <span class="b-old-price__old"><?= $basketItem->getBasePrice() ?>  </span>
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
    </div>
</div>
