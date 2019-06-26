<?php

use FourPaws\Decorators\SvgDecorator;
use FourPaws\Helpers\WordHelper;

?>
<section class="b-subscribe-delivery-cart <?=$mobile ? 'mobile' : ''?>">
    <div class="b-subscribe-delivery-cart__anchor" data-subscribe-delivery-cart="mobile"></div>
    <div class="b-subscribe-delivery-cart__content">
        <div class="b-subscribe-delivery-cart__info-list">
            <div class="item">
                <div class="item__icon">
                    <?= new SvgDecorator('icon-calendar', 24, 24) ?>
                </div>
                <div class="item__text">
                    Установите удобное расписание доставок
                </div>
            </div>
            <div class="item">
                <div class="item__icon">
                    <?= new SvgDecorator('icon-cancel', 24, 24) ?>
                </div>
                <div class="item__text">
                    Переносите или&nbsp;отменяйте доставку в&nbsp;любое время
                </div>
            </div>
            <div class="item">
                <div class="item__icon">
                    <?= new SvgDecorator('icon-price', 24, 24) ?>
                </div>
                <div class="item__text">
                    Наслаждайтесь экономией денег и&nbsp;времени
                </div>
            </div>
        </div>
    </div>
    <div class="b-subscribe-delivery-cart__bottom">
        <ul class="b-price-subscribe-delivery-cart">
            <?/*<li class="b-price-subscribe-delivery-cart__item">
                <div class="b-price-subscribe-delivery-cart__text">
                    <div class="b-price-subscribe-delivery-cart__clipped-text">
                        <?= WordHelper::numberFormat($arResult['TOTAL_QUANTITY'],
                            0) ?> <?= WordHelper::declension($arResult['TOTAL_QUANTITY'],
                            ['товар', 'товара', 'товаров']) ?>
                        <?php if ($arResult['BASKET_WEIGHT'] > 0) { ?>(<?= WordHelper::showWeight($arResult['BASKET_WEIGHT'],
                            true) ?>)<?php } ?>
                    </div>
                </div>
                <div class="b-price-subscribe-delivery-cart__value">
                    <div class="b-price b-price--subscribe-cart">
                        <? if($arResult['TOTAL_PRICE'] != $arResult['SUBSCRIBE_PRICE']) { ?>
                            <span class="b-old-price b-old-price--crossed-out b-old-price--inline">
                                <span class="b-old-price__old"><?= WordHelper::numberFormat($arResult['TOTAL_BASE_PRICE']); ?></span>
                                <span class="b-ruble">₽</span>
                            </span>
                        <? } ?>
                        <span class="b-price__current b-price__current--light"><?= WordHelper::numberFormat($arResult['SUBSCRIBE_PRICE']); ?></span>
                        <span class="b-ruble">₽</span>
                    </div>
                </div>
            </li>*/?>
            <li class="b-price-subscribe-delivery-cart__item">
                <div class="b-price-subscribe-delivery-cart__text">
                    <div class="b-price-subscribe-delivery-cart__clipped-text">
                        Итого стоимость по подписке
                    </div>
                </div>
                <div class="b-price-subscribe-delivery-cart__value">
                    <div class="b-price b-price--subscribe-cart b-price--result-subscribe-cart">
                        <? if($arResult['TOTAL_PRICE'] != $arResult['SUBSCRIBE_PRICE']) { ?>
                            <span class="b-old-price b-old-price--crossed-out b-old-price--inline">
                                                <span class="b-old-price__old"><?= WordHelper::numberFormat($arResult['TOTAL_BASE_PRICE']); ?></span>
                                                <span class="b-ruble">₽</span>
                                            </span>
                        <? } ?>
                        <span class="b-price__current"><?= WordHelper::numberFormat($arResult['SUBSCRIBE_PRICE']); ?></span>
                        <span class="b-ruble">₽</span>
                    </div>
                </div>
            </li>
            <li class="b-price-subscribe-delivery-cart__item b-price-subscribe-delivery-cart__item--discount">
                <div class="b-price-subscribe-delivery-cart__text">
                    <div class="b-price-subscribe-delivery-cart__clipped-text">
                        Уже на первой Подписке вы экономите
                    </div>
                </div>
                <div class="b-price-subscribe-delivery-cart__value">
                    <div class="b-price b-price--subscribe-cart b-price--result-subscribe-cart">
                        <span class="b-price__current"><?= WordHelper::numberFormat($subscribePriceDiff) ?></span>
                        <span class="b-ruble">₽</span>
                    </div>
                </div>
            </li>
        </ul>

        <form action="/sale/order/" method="post" <?=(!$user) ? 'onsubmit="return false;"' : ''?>>
            <button class="b-button b-button--subscribe-delivery-cart <?=(!$user) ? 'js-open-popup' : ''?>"
                    title="Подписка на доставку" <?= (int)$arResult['TOTAL_PRICE'] === 0 ? ' disabled' : '' ?>>
                Подписаться на доставку
            </button>
            <input type="hidden" name="subscribe" value="1">
        </form>
    </div>
</section>

