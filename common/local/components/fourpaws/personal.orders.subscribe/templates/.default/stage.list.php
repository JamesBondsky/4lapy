<?php

use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\PersonalBundle\Entity\Order;
use FourPaws\PersonalBundle\Entity\OrderSubscribe;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $component
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $componentPath
 */

?>
<div class="b-account__accordion b-account__accordion--subscribe">
    <ul class="b-account__accordion-order-list">
        <?php
        /** @var ArrayCollection $subscriptions */
        $subscriptions = $arResult['SUBSCRIPTIONS'];
        foreach ($arResult['ORDERS'] as $order) {
            /** @var Order $order */
            $orderId = $order->getId();
            $APPLICATION->IncludeComponent(
                'fourpaws:personal.order.item',
                '',
                [
                    'ORDER' => $order,
                    'ORDER_SUBSCRIBE' => $subscriptions->get($orderId)
                ],
                $component,
                [
                    'HIDE_ICONS' => 'Y'
                ]
            );
        }
        ?>
    </ul>
</div>
<?php

/*
?>
<li class="b-accordion-order-item js-permutation-li js-item-content"
    data-first-subscribe="16.02.2018"
    data-interval="delivery-interval-0"
    data-frequency="frequency-delivery-3"
    data-id="145">
    <div class="b-accordion-order-item__visible js-premutation-accordion-content">
        <div class="b-accordion-order-item__info">
            <a class="b-accordion-order-item__open-accordion js-open-accordion" href="javascript:void(0);" title="">
                        <span class="b-accordion-order-item__arrow">
                            <span class="b-icon b-icon--account">
                            <svg class="b-icon__svg" viewBox="0 0 25 25 " width="25px" height="25px">
                              <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-account" href="icons.svg#icon-arrow-account">
                              </use>
                            </svg></span></span><span class="b-accordion-order-item__number-order">1 раз в 2 недели, четверг</span></a>
            <div class="b-accordion-order-item__info-order">3 товара (20 кг)
            </div>
        </div>
        <div class="b-accordion-order-item__adress">
            <div class="b-accordion-order-item__date b-accordion-order-item__date--new">Новый <span>с 2 октября 2017</span>
            </div>
            <div class="b-accordion-order-item__date b-accordion-order-item__date--pickup">Самовывоз <span>25 сентября 2017</span>
            </div>
            <div class="b-adress-info b-adress-info--order"><span class="b-adress-info__label b-adress-info__label--green"></span>м. Братиславская, ул. Братиславская, д. 13/1, Москва
                <p class="b-adress-info__mode-operation">пн–вс: 10:00–22:00
                </p>
            </div>
        </div>
        <div class="b-accordion-order-item__pay">
            <div class="b-accordion-order-item__not-pay">Неоплачено онлайн
            </div>
        </div>
        <div class="b-accordion-order-item__button js-button-default">
            <div class="b-accordion-order-item__subscribe-link"><a class="b-accordion-order-item__edit js-open-popup js-subscribe-delivery-edit" href="javascript:void(0);" title="Редактировать подписку" data-popup-id="subscribe-delivery"><span class="b-icon b-icon--account-block">
                              <svg class="b-icon__svg" viewBox="0 0 23 20 " width="23px" height="20px">
                                <use class="b-icon__use" xlink:href="icons.svg#icon-edit" href="icons.svg#icon-edit">
                                </use>
                              </svg></span><span>Редактировать</span></a><a class="b-accordion-order-item__del-subscribe js-delete" href="javascript:void(0);" title="Удалить подписку" data-url="json/subscribe-delivery-del.json"><span class="b-icon b-icon--account-block">
                              <svg class="b-icon__svg" viewBox="0 0 23 20 " width="23px" height="20px">
                                <use class="b-icon__use" xlink:href="icons.svg#icon-trash" href="icons.svg#icon-trash">
                                </use>
                              </svg></span><span>Удалить</span></a>
            </div>
            <div class="b-accordion-order-item__sum b-accordion-order-item__sum--full">3 000<span class="b-ruble b-ruble--account-accordion">&nbsp;₽</span>
            </div>
        </div>
    </div>
    <div class="b-accordion-order-item__hidden js-hidden-order">
        <ul class="b-list-order">
            <li class="b-list-order__item">
                <div class="b-list-order__image-wrapper"><img class="b-list-order__image js-image-wrapper" src="images/content/pro-plan.jpg" alt="" role="presentation"/>
                </div>
                <div class="b-list-order__wrapper">
                    <div class="b-list-order__info">
                        <div class="b-list-order__action">Сейчас участвует в акции
                        </div>
                        <div class="b-clipped-text b-clipped-text--account"><span><strong>Проплан  </strong>корм для собак крупных пород с атлетическим телосложением</span>
                        </div>
                        <div class="b-list-order__option">
                            <div class="b-list-order__option-text">Вкус: <span>Ягненок/Яблоко</span>
                            </div>
                            <div class="b-list-order__option-text">Вес: <span>3 кг</span>
                            </div>
                            <div class="b-list-order__option-text">Артикул: <span>1020157</span>
                            </div>
                        </div>
                    </div>
                    <div class="b-list-order__price">
                        <div class="b-list-order__sum b-list-order__sum--item">10 169<span class="b-ruble b-ruble--account-accordion">&nbsp;₽</span>
                        </div>
                        <div class="b-list-order__calculation">1 169 ₽ × 10 шт
                        </div>
                        <div class="b-list-order__bonus">+ 100 бонусов
                        </div>
                    </div>
                </div>
            </li>
            <li class="b-list-order__item">
                <div class="b-list-order__image-wrapper"><img class="b-list-order__image js-image-wrapper" src="images/content/clean-cat.jpg" alt="" role="presentation"/>
                </div>
                <div class="b-list-order__wrapper">
                    <div class="b-list-order__info">
                        <div class="b-clipped-text b-clipped-text--account"><span><strong>Мозер   </strong> комплект насадок для профессиональной машинки для стрижки 5, 9, 13 мм</span>
                        </div>
                        <div class="b-list-order__option">
                            <div class="b-list-order__option-text">Артикул: <span>1003573</span>
                            </div>
                        </div>
                    </div>
                    <div class="b-list-order__price">
                        <div class="b-list-order__sum b-list-order__sum--item">11 305<span class="b-ruble b-ruble--account-accordion">&nbsp;₽</span>
                        </div>
                    </div>
                </div>
            </li>
            <li class="b-list-order__item">
                <div class="b-list-order__image-wrapper"><img class="b-list-order__image js-image-wrapper" src="images/content/brit.png" alt="" role="presentation"/>
                </div>
                <div class="b-list-order__wrapper">
                    <div class="b-list-order__info">
                        <div class="b-clipped-text b-clipped-text--account"><span><strong>Фронтлайн НексгарД Спектра   </strong> таблетки жевательные от блох, клещей и гельминтов для собак 15-30 кг</span>
                        </div>
                        <div class="b-list-order__option">
                            <div class="b-list-order__option-text">Вес: <span>0.1 кг</span>
                            </div>
                            <div class="b-list-order__option-text">Вес: <span>3 кг</span>
                            </div>
                            <div class="b-list-order__option-text">Артикул: <span>1020157</span>
                            </div>
                        </div>
                    </div>
                    <div class="b-list-order__price">
                        <div class="b-list-order__sum b-list-order__sum--item">1 476<span class="b-ruble b-ruble--account-accordion">&nbsp;₽</span>
                        </div>
                        <div class="b-list-order__bonus">+ 44 бонуса
                        </div>
                    </div>
                </div>
            </li>
            <li class="b-list-order__item">
                <div class="b-list-order__image-wrapper"><img class="b-list-order__image js-image-wrapper" src="images/content/abba.png" alt="" role="presentation"/>
                </div>
                <div class="b-list-order__wrapper">
                    <div class="b-list-order__info">
                        <div class="b-clipped-text b-clipped-text--account"><span><strong>Petmax   </strong>миска для щенков блюдце</span>
                        </div>
                        <div class="b-list-order__option">
                            <div class="b-list-order__option-text">Вес: <span>0.22 кг</span>
                            </div>
                            <div class="b-list-order__option-text">Артикул: <span>1018330</span>
                            </div>
                        </div>
                    </div>
                    <div class="b-list-order__price">
                        <div class="b-list-order__sum b-list-order__sum--item">1 047<span class="b-ruble b-ruble--account-accordion">&nbsp;₽</span>
                        </div>
                        <div class="b-list-order__calculation">349 ₽ × 3 шт
                        </div>
                        <div class="b-list-order__bonus">+ 6 бонусов
                        </div>
                    </div>
                </div>
            </li>
        </ul>
        <div class="b-accordion-order-item__calculation-full">
            <ul class="b-characteristics-tab__list">
                <li class="b-characteristics-tab__item b-characteristics-tab__item--account">
                    <div class="b-characteristics-tab__characteristics-text b-characteristics-tab__characteristics-text--account"><span>Товары</span>
                        <div class="b-characteristics-tab__dots">
                        </div>
                    </div>
                    <div class="b-characteristics-tab__characteristics-value b-characteristics-tab__characteristics-value--account">13 269<span class="b-ruble b-ruble--calculation-account">&nbsp;₽</span>
                    </div>
                </li>
                <li class="b-characteristics-tab__item b-characteristics-tab__item--account">
                    <div class="b-characteristics-tab__characteristics-text b-characteristics-tab__characteristics-text--account"><span>Доставка</span>
                        <div class="b-characteristics-tab__dots">
                        </div>
                    </div>
                    <div class="b-characteristics-tab__characteristics-value b-characteristics-tab__characteristics-value--account">350<span class="b-ruble b-ruble--calculation-account">&nbsp;₽</span>
                    </div>
                </li>
                <li class="b-characteristics-tab__item b-characteristics-tab__item--account">
                    <div class="b-characteristics-tab__characteristics-text b-characteristics-tab__characteristics-text--account b-characteristics-tab__characteristics-text--last"><span>Итого к оплате</span>
                        <div class="b-characteristics-tab__dots">
                        </div>
                    </div>
                    <div class="b-characteristics-tab__characteristics-value b-characteristics-tab__characteristics-value--account b-characteristics-tab__characteristics-value--last">13 619<span class="b-ruble b-ruble--calculation-account">&nbsp;₽</span>
                    </div>
                </li>
            </ul>
        </div>
    </div>
    <div class="b-accordion-order-item__mobile-bottom js-button-permutation-mobile">
    </div>
</li>
<li class="b-accordion-order-item js-permutation-li js-item-content" data-first-subscribe="16.01.2018" data-interval="delivery-interval-1" data-frequency="frequency-delivery-4" data-id="46">
    <div class="b-accordion-order-item__visible js-premutation-accordion-content">
        <div class="b-accordion-order-item__info"><a class="b-accordion-order-item__open-accordion js-open-accordion" href="javascript:void(0);" title=""><span class="b-accordion-order-item__arrow"><span class="b-icon b-icon--account">
                            <svg class="b-icon__svg" viewBox="0 0 25 25 " width="25px" height="25px">
                              <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-account" href="icons.svg#icon-arrow-account">
                              </use>
                            </svg></span></span><span class="b-accordion-order-item__number-order">1 раз в месяц, воскресенье</span></a>
            <div class="b-accordion-order-item__info-order">10 товаров (310 кг)
            </div>
        </div>
        <div class="b-accordion-order-item__adress">
            <div class="b-accordion-order-item__date b-accordion-order-item__date--new">Новый <span>с 2 октября 2017</span>
            </div>
            <div class="b-accordion-order-item__date b-accordion-order-item__date--pickup">Доставка <span>25 сентября 2017</span>
            </div>
            <div class="b-adress-info b-adress-info--order">Карла Маркса ул., д. 158А, кв. 207, этаж 19,г. Красногорск, Московская обл.
            </div>
        </div>
        <div class="b-accordion-order-item__pay">
            <div class="b-accordion-order-item__not-pay">Неоплачено банковской картой
            </div>
        </div>
        <div class="b-accordion-order-item__button js-button-default">
            <div class="b-accordion-order-item__subscribe-link"><a class="b-accordion-order-item__edit js-open-popup js-subscribe-delivery-edit" href="javascript:void(0);" title="Редактировать подписку" data-popup-id="subscribe-delivery"><span class="b-icon b-icon--account-block">
                              <svg class="b-icon__svg" viewBox="0 0 23 20 " width="23px" height="20px">
                                <use class="b-icon__use" xlink:href="icons.svg#icon-edit" href="icons.svg#icon-edit">
                                </use>
                              </svg></span><span>Редактировать</span></a><a class="b-accordion-order-item__del-subscribe js-delete" href="javascript:void(0);" title="Удалить подписку" data-url="json/subscribe-delivery-del.json"><span class="b-icon b-icon--account-block">
                              <svg class="b-icon__svg" viewBox="0 0 23 20 " width="23px" height="20px">
                                <use class="b-icon__use" xlink:href="icons.svg#icon-trash" href="icons.svg#icon-trash">
                                </use>
                              </svg></span><span>Удалить</span></a>
            </div>
            <div class="b-accordion-order-item__sum b-accordion-order-item__sum--full">17 560<span class="b-ruble b-ruble--account-accordion">&nbsp;₽</span>
            </div>
        </div>
    </div>
    <div class="b-accordion-order-item__hidden js-hidden-order">
        <ul class="b-list-order">
            <li class="b-list-order__item">
                <div class="b-list-order__image-wrapper"><img class="b-list-order__image js-image-wrapper" src="images/content/pro-plan.jpg" alt="" role="presentation"/>
                </div>
                <div class="b-list-order__wrapper">
                    <div class="b-list-order__info">
                        <div class="b-list-order__action">Сейчас участвует в акции
                        </div>
                        <div class="b-clipped-text b-clipped-text--account"><span><strong>Проплан  </strong>корм для собак крупных пород с атлетическим телосложением</span>
                        </div>
                        <div class="b-list-order__option">
                            <div class="b-list-order__option-text">Вкус: <span>Ягненок/Яблоко</span>
                            </div>
                            <div class="b-list-order__option-text">Вес: <span>3 кг</span>
                            </div>
                            <div class="b-list-order__option-text">Артикул: <span>1020157</span>
                            </div>
                        </div>
                    </div>
                    <div class="b-list-order__price">
                        <div class="b-list-order__sum b-list-order__sum--item">10 169<span class="b-ruble b-ruble--account-accordion">&nbsp;₽</span>
                        </div>
                        <div class="b-list-order__calculation">1 169 ₽ × 10 шт
                        </div>
                        <div class="b-list-order__bonus">+ 100 бонусов
                        </div>
                    </div>
                </div>
            </li>
            <li class="b-list-order__item">
                <div class="b-list-order__image-wrapper"><img class="b-list-order__image js-image-wrapper" src="images/content/clean-cat.jpg" alt="" role="presentation"/>
                </div>
                <div class="b-list-order__wrapper">
                    <div class="b-list-order__info">
                        <div class="b-clipped-text b-clipped-text--account"><span><strong>Мозер   </strong> комплект насадок для профессиональной машинки для стрижки 5, 9, 13 мм</span>
                        </div>
                        <div class="b-list-order__option">
                            <div class="b-list-order__option-text">Артикул: <span>1003573</span>
                            </div>
                        </div>
                    </div>
                    <div class="b-list-order__price">
                        <div class="b-list-order__sum b-list-order__sum--item">11 305<span class="b-ruble b-ruble--account-accordion">&nbsp;₽</span>
                        </div>
                    </div>
                </div>
            </li>
            <li class="b-list-order__item">
                <div class="b-list-order__image-wrapper"><img class="b-list-order__image js-image-wrapper" src="images/content/brit.png" alt="" role="presentation"/>
                </div>
                <div class="b-list-order__wrapper">
                    <div class="b-list-order__info">
                        <div class="b-clipped-text b-clipped-text--account"><span><strong>Фронтлайн НексгарД Спектра   </strong> таблетки жевательные от блох, клещей и гельминтов для собак 15-30 кг</span>
                        </div>
                        <div class="b-list-order__option">
                            <div class="b-list-order__option-text">Вес: <span>0.1 кг</span>
                            </div>
                            <div class="b-list-order__option-text">Вес: <span>3 кг</span>
                            </div>
                            <div class="b-list-order__option-text">Артикул: <span>1020157</span>
                            </div>
                        </div>
                    </div>
                    <div class="b-list-order__price">
                        <div class="b-list-order__sum b-list-order__sum--item">1 476<span class="b-ruble b-ruble--account-accordion">&nbsp;₽</span>
                        </div>
                        <div class="b-list-order__bonus">+ 44 бонуса
                        </div>
                    </div>
                </div>
            </li>
            <li class="b-list-order__item">
                <div class="b-list-order__image-wrapper"><img class="b-list-order__image js-image-wrapper" src="images/content/abba.png" alt="" role="presentation"/>
                </div>
                <div class="b-list-order__wrapper">
                    <div class="b-list-order__info">
                        <div class="b-clipped-text b-clipped-text--account"><span><strong>Petmax   </strong>миска для щенков блюдце</span>
                        </div>
                        <div class="b-list-order__option">
                            <div class="b-list-order__option-text">Вес: <span>0.22 кг</span>
                            </div>
                            <div class="b-list-order__option-text">Артикул: <span>1018330</span>
                            </div>
                        </div>
                    </div>
                    <div class="b-list-order__price">
                        <div class="b-list-order__sum b-list-order__sum--item">1 047<span class="b-ruble b-ruble--account-accordion">&nbsp;₽</span>
                        </div>
                        <div class="b-list-order__calculation">349 ₽ × 3 шт
                        </div>
                        <div class="b-list-order__bonus">+ 6 бонусов
                        </div>
                    </div>
                </div>
            </li>
        </ul>
        <div class="b-accordion-order-item__calculation-full">
            <ul class="b-characteristics-tab__list">
                <li class="b-characteristics-tab__item b-characteristics-tab__item--account">
                    <div class="b-characteristics-tab__characteristics-text b-characteristics-tab__characteristics-text--account"><span>Товары</span>
                        <div class="b-characteristics-tab__dots">
                        </div>
                    </div>
                    <div class="b-characteristics-tab__characteristics-value b-characteristics-tab__characteristics-value--account">13 269<span class="b-ruble b-ruble--calculation-account">&nbsp;₽</span>
                    </div>
                </li>
                <li class="b-characteristics-tab__item b-characteristics-tab__item--account">
                    <div class="b-characteristics-tab__characteristics-text b-characteristics-tab__characteristics-text--account"><span>Доставка</span>
                        <div class="b-characteristics-tab__dots">
                        </div>
                    </div>
                    <div class="b-characteristics-tab__characteristics-value b-characteristics-tab__characteristics-value--account">350<span class="b-ruble b-ruble--calculation-account">&nbsp;₽</span>
                    </div>
                </li>
                <li class="b-characteristics-tab__item b-characteristics-tab__item--account">
                    <div class="b-characteristics-tab__characteristics-text b-characteristics-tab__characteristics-text--account b-characteristics-tab__characteristics-text--last"><span>Итого к оплате</span>
                        <div class="b-characteristics-tab__dots">
                        </div>
                    </div>
                    <div class="b-characteristics-tab__characteristics-value b-characteristics-tab__characteristics-value--account b-characteristics-tab__characteristics-value--last">13 619<span class="b-ruble b-ruble--calculation-account">&nbsp;₽</span>
                    </div>
                </li>
            </ul>
        </div>
    </div>
    <div class="b-accordion-order-item__mobile-bottom js-button-permutation-mobile">
    </div>
</li>
<?php
*/