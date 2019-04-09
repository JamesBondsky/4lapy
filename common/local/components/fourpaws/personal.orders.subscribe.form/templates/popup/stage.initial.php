<?php

use FourPaws\Decorators\SvgDecorator;
use FourPaws\Helpers\DateHelper;
use FourPaws\PersonalBundle\Entity\Order;
use FourPaws\PersonalBundle\Entity\OrderSubscribe;

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
 */

if (!$arResult['isCorrect']) {
    return;
}

$arParams['OUTPUT_VIA_BUFFER_CONTENT'] = $arParams['OUTPUT_VIA_BUFFER_CONTENT'] ?? 'N';
$arParams['BUFFER_CONTENT_VIEW_NAME'] = $arParams['BUFFER_CONTENT_VIEW_NAME'] ?? 'footer_popup_cont';
$arParams['SHOW_SUBSCRIBE_ACTION'] = $arParams['SHOW_SUBSCRIBE_ACTION'] ?? 'N';
$arParams['SHOW_SUBSCRIBE_EDIT_ACTION'] = $arParams['SHOW_SUBSCRIBE_EDIT_ACTION'] ?? 'N';
$arParams['ORDER_SUBSCRIBE_LIST_URL'] = $arParams['ORDER_SUBSCRIBE_LIST_URL'] ?? '/personal/subscribe/';

$attrSuffix = '-'.$arParams['ORDER_ID'].'-'.randString(3);
$attrPopupId = $arParams['ATTR_POPUP_ID'] ?? 'subscribe-delivery'.$attrSuffix;

/** @var Order $order */
$order = $arResult['ORDER'];
/** @var OrderSubscribe $orderSubscribe */
$orderSubscribe = $arResult['ORDER_SUBSCRIBE'];

/**
 * Элементы управления, выводимые на странице списка заказов
 */
ob_start();
if ($arResult['isActualSubscription']) {
    ?>
    <a href="<?=$arParams['ORDER_SUBSCRIBE_LIST_URL']?>" class="b-accordion-order-item__subscribe">
        Оформлена подписка на&nbsp;доставку
    </a>
    <?php
} elseif ($arResult['canBeSubscribed']) {
    ?>
    <a href="javascript:void(0)" class="b-accordion-order-item__subscribe js-open-popup js-open-subscribe-delivery-popup"
       data-order-id="<?= $arParams['ORDER_ID'] ?>"
       data-popup-id="change-subscribe-delivery">
        Подписаться на&nbsp;доставку
    </a>
    <?php
}
$arResult['CONTROLS_HTML']['ADD'] = ob_get_clean();

if ($arParams['SHOW_SUBSCRIBE_ACTION'] === 'Y') {
    echo $arResult['CONTROLS_HTML']['ADD'];
}

/**
 * Элементы управления, выводимые на странице списка подписанных заказов
 */
ob_start();
if ($arResult['isActualSubscription']) {
    ?>
    <div class="b-accordion-order-item__subscribe-link">
        <?php
        if ($arResult['canBeSubscribed']) {
            ?>
            <a class="b-accordion-order-item__edit js-open-popup js-subscribe-delivery-edit"
               href="javascript:void(0);"
               title="Редактировать подписку"
               data-popup-id="<?= $attrPopupId ?>">
            <span class="b-icon b-icon--account-block">
                <?= new SvgDecorator('icon-edit', 23, 20) ?>
            </span>
                <span>Редактировать</span>
            </a>
            <?php
        }
        ?>
        <a class="b-accordion-order-item__del-subscribe js-delete"
           href="javascript:void(0);"
           title="Удалить подписку"
           data-id="<?=$order->getId()?>"
           data-url="/ajax/personal/orderSubscribe/delete/?orderId=<?=$order->getId()?>">
            <span class="b-icon b-icon--account-block">
                <?= new SvgDecorator('icon-trash', 23, 20) ?>
            </span>
            <span>Удалить</span>
        </a>
    </div>
    <?php
}
$arResult['CONTROLS_HTML']['EDIT'] = ob_get_clean();

if ($arParams['SHOW_SUBSCRIBE_EDIT_ACTION'] === 'Y') {
    echo $arResult['CONTROLS_HTML']['EDIT'];
}

/**
 * Кнопка удалить, выводимая на странице списка подписанных заказов
 */
ob_start();
if ($arResult['isActualSubscription']) {
    ?>
    <div class="b-accordion-order-item__subscribe-link">
        <a class="b-accordion-order-item__del-subscribe js-delete"
           href="javascript:void(0);"
           title="Удалить подписку"
           data-id="<?=$order->getId()?>"
           data-url="/ajax/personal/orderSubscribe/delete/?orderId=<?=$order->getId()?>">
            <span class="b-icon b-icon--account-block">
                <?= new SvgDecorator('icon-trash', 23, 20) ?>
            </span>
            <span>Удалить</span>
        </a>
    </div>
    <?php
}
$arResult['CONTROLS_HTML']['DELETE'] = ob_get_clean();

if ($arParams['SHOW_SUBSCRIBE_DELETE_ACTION'] === 'Y') {
    echo $arResult['CONTROLS_HTML']['DELETE'];
}

/**
 * Попап с формой подписки
 */
$viewTemplate = $this;
if ($arParams['OUTPUT_VIA_BUFFER'] === 'Y') {
    // так надо, когда компоненты вложены друг в друга и кешируются
    $parent = $component;
    while ($parent = $parent->getParent()) {
        if ($parent->getCachePath()) {
            $viewTemplate = $parent->getTemplate();
        }
    }
    $viewTemplate->SetViewTarget($arParams['BUFFER_CONTENT_VIEW_NAME']);
}

if ($order) {
    $errorBlock = '<div class="b-error"><span class="js-message"></span></div>';
    // по субботам, раз в неделю, с 10 до 20.
    $subscribeParamsText = '&mdash;';
    // суббота 20.07.2017 с 10 до 20.
    $subscribeStartDateText = '&mdash;';
    if ($orderSubscribe) {
        $formattedTime = $orderSubscribe->getDeliveryTimeFormattedRu(true);
        $subscribeParamsText = '';
        $subscribeParamsText .= 'по '.$orderSubscribe->getDateStartWeekdayRu(true, DateHelper::DATIVE_PLURAL);
        $subscribeParamsText .= ', '.ToLower($orderSubscribe->getDeliveryFrequencyEntity()->getValue());
        $subscribeParamsText .= $formattedTime === '' ? '.' : ', '.$formattedTime.'.';

        $subscribeStartDateText = '';
        $subscribeStartDateText .= $orderSubscribe->getDateStartWeekdayRu(true);
        $subscribeStartDateText .= ', '.$orderSubscribe->getDateStartFormatted();
        $subscribeStartDateText .= $formattedTime === '' ? '.' : ', '.$formattedTime.'.';
    }

    // даты, на которые можно оформить первую доставку
//    $possibleDeliveryDateMin = $component->getOrderPossibleDeliveryDate($order);
//    $possibleDeliveryDateMax = null;
//    if ($possibleDeliveryDateMin !== null) {
//        $possibleDeliveryDateMax = clone $possibleDeliveryDateMin;
//        $possibleDeliveryDateMax->add((new \DateInterval('P3M')));
//        // выбранная дата при подписке, либо дата по умолчанию
//        $curDeliveryDateValue = $orderSubscribe ? $orderSubscribe->getDateCreate() : $possibleDeliveryDateMin->format('d.m.Y');
//    } else {
//        $curDeliveryDateValue = $orderSubscribe ? $orderSubscribe->getDateCreate() : '';
//    }

    // LP03-465
    //$paymentName = $order->getPayment()->getName();
    $paymentName = 'наличными или картой при получении';

    ?><?/*
    <section class="b-popup-pick-city b-popup-pick-city--subscribe-delivery js-popup-section"
             data-popup="<?= $attrPopupId ?>">
        <a class="b-popup-pick-city__close b-popup-pick-city__close--subscribe-delivery js-close-popup"
           href="javascript:void(0);" title="Закрыть"></a>
        <div class="b-registration b-registration--subscribe-delivery">
            <header class="b-registration__header">
                <div class="b-title b-title--h1 b-title--registration">
                    <?=$orderSubscribe ? 'Изменение подписки' : 'Подписка на доставку'?>
                </div>
            </header>
            <form class="b-registration__form js-form-validation js-subscribe-query"
                  method="post"
                  data-url="/ajax/personal/orderSubscribe/edit/">
                <input type="hidden" name="orderId" value="<?= $order->getId() ?>">
                <input type="hidden" name="action" value="deliveryOrderSubscribe">

                <div class="b-input-line b-input-line--popup-authorization">
                    <div class="b-input-line__label-wrapper">
                        <label class="b-input-line__label" for="<?= 'first-delivery' . $attrSuffix ?>">
                            День первой доставки
                        </label>
                    </div>
                    <div class="b-input b-input--registration-form b-input--datepicker">
                        <input name="dateStart"
                               value="<?=$curDeliveryDateValue?>"
                               class="b-input__input-field b-input__input-field--registration-form js-date-subscribe"
                               id="<?= 'first-delivery' . $attrSuffix ?>"
                               type="text"
                               readonly="readonly"
                               data-minDate="<?=$possibleDeliveryDateMin !== null ? $possibleDeliveryDateMin->format('d.m.Y') : ''?>"
                               data-maxDate="<?=$possibleDeliveryDateMax !== null ? $possibleDeliveryDateMax->format('d.m.Y') : ''?>"
                               onfocus="blur();">
                        <?= $errorBlock ?>
                    </div>
                </div>
                <?php
                if ($arResult['TIME_VARIANTS']) {
                    $curValue = $orderSubscribe ? $orderSubscribe->getDeliveryTime() : '';
                    ?>
                    <label class="b-registration__label b-registration__label--subscribe-delivery">
                        Интервал
                    </label>
                    <div class="b-select b-select--subscribe-delivery js-delivery-interval">
                        <select name="deliveryInterval"
                                class="b-select__block b-select__block--subscribe-delivery js-delivery-interval js-change-date"
                                title="">
                            <option value=""<?=(!$curValue ? ' selected="selected"' : '')?> disabled="disabled">
                                выберите
                            </option>
                            <?php
                            foreach ($arResult['TIME_VARIANTS'] as $variant) {
                                $selected = $variant['VALUE'] === $curValue ? ' selected="selected"' : '';
                                ?>
                                <option<?= $selected ?> value="<?= $variant['VALUE'] ?>">
                                    <?=$variant['TEXT']?>
                                </option>
                                <?php
                            }
                            ?>
                        </select>
                        <?= $errorBlock ?>
                    </div>
                    <?php
                }

                if ($arResult['FREQUENCY_VARIANTS']) {
                    $curValue = $orderSubscribe ? $orderSubscribe->getFrequency() : '';
                    ?>
                    <label class="b-registration__label b-registration__label--subscribe-delivery">
                        Как часто
                    </label>
                    <div class="b-select b-select--subscribe-delivery js-frequency-delivery">
                        <select name="deliveryFrequency"
                                class="b-select__block b-select__block--subscribe-delivery js-frequency-delivery js-change-date"
                                title="">
                            <option value=""<?=(!$curValue ? ' selected="selected"' : '')?> disabled="disabled">
                                выберите
                            </option>
                            <?php
                            foreach ($arResult['FREQUENCY_VARIANTS'] as $variant) {
                                $selected = $variant['VALUE'] === $curValue ? ' selected="selected"' : '';
                                ?>
                                <option<?= $selected ?> value="<?= $variant['VALUE'] ?>">
                                    <?= $variant['TEXT'] ?>
                                </option>
                                <?php
                            }
                            ?>
                        </select>
                        <?= $errorBlock ?>
                    </div>
                    <?php
                }
                ?>
                <div class="b-registration__text b-registration__text--subscribe-delivery">
                    Периодичность, день и время доставки вы сможете поменять в личном кабинете в любой момент.<br>
                    Стоимость заказа по подписке будет уточнена оператором с учетом действующих акций.
                </div>
                <ul class="b-registration__info-delivery">
                    <li class="b-registration__item-delivery">
                        <span class="b-icon b-icon--delivery-calendar">
                            <?= (new SvgDecorator('icon-delivery-calendar', 16, 17)) ?>
                        </span>
                        <div class="b-registration__text b-registration__text--info-delivery">
                            <p>Параметры подписки: <span class="js-subscribe-query-parameters"><?=$subscribeParamsText?></span></p>
                            <p>Первая доставка: <span class="js-subscribe-query-first"><?=$subscribeStartDateText?></span></p>
                        </div>
                    </li>
                    <li class="b-registration__item-delivery">
                        <span class="b-icon b-icon--delivery-calendar">
                            <?= (new SvgDecorator('icon-delivery-car', 18, 12)) ?>
                        </span>
                        <div class="b-registration__text b-registration__text--info-delivery">
                            <p><?= $order->getDelivery()->getDeliveryName() . ($arResult['deliveryAddress'] ? ', по адресу:' : '') ?></p>
                            <?php
                            if ($arResult['deliveryAddress']) {
                                echo '<p>'.$arResult['deliveryAddress'].'</p>';
                            }
                            ?>
                        </div>
                    </li>
                    <li class="b-registration__item-delivery">
                        <span class="b-icon b-icon--delivery-calendar">
                            <?= (new SvgDecorator('icon-delivery-dollar', 18, 14)) ?>
                        </span>
                        <div class="b-registration__text b-registration__text--info-delivery">
                            <p><?= 'Оплата: ' . $paymentName . '.' ?></p>
                        </div>
                    </li>
                </ul>
                <button class="b-button b-button--subscribe-delivery">
                    Сохранить
                </button>
            </form>
        </div>
    </section>
    */?><?php
}
if ($arParams['OUTPUT_VIA_BUFFER'] === 'Y') {
    $viewTemplate->EndViewTarget();
}
