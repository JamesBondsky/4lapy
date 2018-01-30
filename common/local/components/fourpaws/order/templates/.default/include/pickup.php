<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Sale\Delivery\CalculationResult;
use FourPaws\DeliveryBundle\Collection\StockResultCollection;
use FourPaws\Decorators\SvgDecorator;

/**
 * @var array $arResult
 * @var array $arParams
 * @var CalculationResult $pickup
 */

/** @var StockResultCollection $stockResult */
$stockResult = $pickup->getData()['STOCK_RESULT'];
?>
<div class="b-input-line b-input-line--address b-input-line--myself">
    <div class="b-input-line__label-wrapper">
        <span class="b-input-line__label">Адрес доставки</span>
    </div>
    <ul class="b-delivery-list">
        <li class="b-delivery-list__item b-delivery-list__item--myself">
            <span class="b-delivery-list__link b-delivery-list__link--myself">
                <span class="b-delivery-list__col b-delivery-list__col--color b-delivery-list__col--grey"></span>
                м. Улица Академика Янгеля, ул. Чертановская, д. 63/2, Москва
            </span>
        </li>
    </ul>
</div>
<div class="b-input-line b-input-line--myself">
    <div class="b-input-line__label-wrapper">
        <span class="b-input-line__label">Время работы</span>
    </div>
    <div class="b-input-line__text-line b-input-line__text-line--myself">
        пн&mdash;пт: 09:00&ndash;21:00, сб: 10:00&ndash;21:00, вс: 10:00&ndash;20:00
    </div>
</div>
<div class="b-input-line b-input-line--myself">
    <div class="b-input-line__label-wrapper">
        <span class="b-input-line__label">Оплата в магазине</span>
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
<div class="b-input-line b-input-line--partially">
    <div class="b-input-line__label-wrapper b-input-line__label-wrapper--order-full">
        <span class="b-input-line__label">Заказ в наличии частично</span>
    </div>
    <div class="b-radio b-radio--tablet-big">
        <input class="b-radio__input"
               type="radio"
               name="order-pick-time"
               id="order-pick-time-now"
               checked="checked"/>
        <label class="b-radio__label b-radio__label--tablet-big"
               for="order-pick-time-now">
        </label>
        <div class="b-order-list b-order-list--myself">
            <ul class="b-order-list__list">
                <li class="b-order-list__item b-order-list__item--myself">
                    <div class="b-order-list__order-text b-order-list__order-text--myself">
                        <div class="b-order-list__clipped-text">
                            <div class="b-order-list__text-backed">Забрать через час
                            </div>
                        </div>
                    </div>
                    <div class="b-order-list__order-value b-order-list__order-value--myself">
                        4 703 ₽
                    </div>
                </li>
            </ul>
        </div>
        <div class="b-radio__addition-text">
            <p>За исключением:</p>
            <ol>
                <li>Корм для кошек Хиллс Тунец стерилайз, меш. 8 кг</li>
                <li>Фурминатор для больших кошек короткошерстных пород 7см</li>
                <li>Moderna Туалет-домик для кошек 50см Friends forever синий</li>
                <li>Petmax Игрушка для кошек Мыши с перьями 7 см (2 шт)</li>
            </ol>
        </div>
    </div>
    <div class="b-radio b-radio--tablet-big">
        <input class="b-radio__input"
               type="radio"
               name="order-pick-time"
               id="order-pick-time-then"/>
        <label class="b-radio__label b-radio__label--tablet-big"
               for="order-pick-time-then">
        </label>
        <div class="b-order-list b-order-list--myself">
            <ul class="b-order-list__list">
                <li class="b-order-list__item b-order-list__item--myself">
                    <div class="b-order-list__order-text b-order-list__order-text--myself">
                        <div class="b-order-list__clipped-text">
                            <div class="b-order-list__text-backed">Забрать полный
                                заказ
                            </div>
                        </div>
                    </div>
                    <div class="b-order-list__order-value b-order-list__order-value--myself">
                        13 269 ₽
                    </div>
                </li>
            </ul>
        </div>
        <div class="b-radio__addition-text">
            <p>среда, 5 сентября в 15:00</p>
        </div>
    </div>
</div>
<a class="b-link b-link--another-point" href="javascript:void(0);" title="">
    Выбрать другой пункт самовывоза
</a>
