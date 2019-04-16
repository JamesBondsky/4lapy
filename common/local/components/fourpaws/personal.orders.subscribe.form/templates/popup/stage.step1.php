<?php

use Bitrix\Sale\BasketItem;
use FourPaws\Catalog\Collection\OfferCollection;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Decorators\SvgDecorator;
use FourPaws\Helpers\DateHelper;
use FourPaws\Helpers\WordHelper;
use FourPaws\PersonalBundle\Entity\Order;
use FourPaws\PersonalBundle\Entity\OrderSubscribe;

global $APPLICATION;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var FourPawsPersonalCabinetOrdersSubscribeFormComponent $component
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $componentPath
 * @var BasketItem $basketItem
 * @var OfferCollection $offers
 * @var Offer $offer
 */
?>

<div class="b-popup-subscribe-delivery__inner js-step1-inner-subscribe-delivery">
    <div class="b-tab-list">
        <ul class="b-tab-list__list js-scroll-tabs-subscribe-delivery">
            <li class="b-tab-list__item active" data-step-subscribe-delivery="1">
                <span class="b-tab-list__step">Шаг </span>1. Товары в подписке
            </li>
            <li class="b-tab-list__item" data-step-subscribe-delivery="2">
                <span class="b-tab-list__step">Шаг </span>2. Доставка и оплата
            </li>
        </ul>
    </div>
    <div class="b-product-subscribe-delivery">
        <div class="b-product-subscribe-delivery__list js-list-product-subscribe-delivery">

            <? foreach ($arResult['BASKET'] as $basketItem) {
                include __DIR__ . '/include/basketItem.php';
            } ?>

            <div class="b-product-subscribe-delivery__add" data-open-catalog-in-popup="true">
                <div class="add-product-subscribe"
                     data-subscribe-delivery-popup="open-catalog"
                     data-popup-id="catalog-subscribe-delivery">
                    <div class="add-product-subscribe__plus"></div>
                    <div class="add-product-subscribe__info">
                        <div class="add-product-subscribe__title">
                            Добавить товар
                        </div>
                        <div class="add-product-subscribe__descr">
                            При добавлении нового товара и&nbsp;изменении количества товаров будет необходимо
                            обновить параметры доставки
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="b-popup-subscribe-delivery__btns">
        <a href="javascript:void(0);"
           class="b-button b-button--next-subscribe-delivery"
           data-step2-change-subscribe-delivery="true"
           title="Далее">
            Далее
        </a>
        <a href="javascript:void(0);"
           class="b-button b-button--cancel-subscribe-delivery"
           data-close-subscribe-delivery-popup="true"
           title="Отменить">
            Отменить
        </a>
    </div>
</div>
