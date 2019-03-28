<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\Decorators\SvgDecorator;

/**
 * @var array $arResult
 */
?>
<li class="b-delivery-list__item">
    <a class="b-delivery-list__link js-shop-link b-active"
       id="shop_id{{id}}"
       data-shop-id="{{id}}"
       href="javascript:void(0);"
       title="">
        <span class="b-delivery-list__col b-delivery-list__col--addr">
            <span class="b-delivery-list__col b-delivery-list__col--color b-delivery-list__col{{metroClass}}"></span>
            {{adress}}
        </span>
        <span class="b-delivery-list__col b-delivery-list__col--time">
            {{schedule}}
        </span>
    </a>
    <div class="b-catalog-stores-info-baloon">
        <a class="b-link b-link--popup-back b-link--catalog-stores b-link--desktop js-close-catalog-stores-baloon"
           href="javascript:void(0);"
           title="">
            <span class="b-icon b-icon--back-long b-icon--balloon">
                <?= new SvgDecorator('icon-back-form', 13, 11) ?>
            </span>
            Вернуться к списку
        </a>
        <a class="b-link b-link--popup-back b-link--baloon js-close-catalog-stores-baloon" href="javascript:void(0);">
            <?= $arResult['IS_DPD'] ? 'Пункт самовывоза' : 'Магазин' ?>
        </a>
        <div class="b-catalog-stores-info-baloon__content-wrap">
            <div class="b-catalog-stores-info-baloon__content" id="scroll-{{id}}">
                <ul class="b-delivery-list">
                    <li class="b-delivery-list__item b-delivery-list__item--myself">
                        <span class="b-delivery-list__link b-delivery-list__link--myself">
                            <span class="b-delivery-list__col b-delivery-list__col--color b-delivery-list__col{{metroClass}}"></span>
                            {{adress}}
                        </span>
                    </li>
                </ul>
                <div class="b-input-line b-input-line--myself">
                    <div class="b-input-line__label-wrapper">
                        <span class="b-input-line__label">Время работы</span>
                    </div>
                    <div class="b-input-line__text-line b-input-line__text-line--myself">
                        {{schedule}}
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
                            <span class="b-input-line_pay-type--name">наличными</span>
                        </span>
                        <span class="b-input-line__pay-type">
                            <span class="b-icon b-icon--icon-bank">
                                <?= new SvgDecorator('icon-bank-card', 16, 12) ?>
                            </span>
                            <span class="b-input-line_pay-type--name">банковской картой</span>
                        </span>
                    </div>
                </div>
                <div class="b-input-line b-input-line--pin">
                    <a class="b-link b-link--pin js-shop-link"
                       href="javascript:void(0);"
                       title=""
                       data-shop-id="{{id}}">
                        <span class="b-icon b-icon--pin">
                            <?= new SvgDecorator('icon-geo', 16, 16) ?>
                        </span>
                        Показать на карте
                    </a>
                </div>
            </div>
            <a class="b-button b-button--catalog-stores-balloon js-btn-shop-filter"
               href="javascript:void(0);"
               title=""
               data-shopId="{{id}}">
                Выбрать этот <?= $arResult['IS_DPD'] ? 'пункт самовывоза' : 'магазин' ?>
            </a>
        </div>
    </div>
</li>
