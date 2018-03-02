<?php

use Bitrix\Main\Application;
use Bitrix\Main\Web\Uri;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\Decorators\SvgDecorator;
use FourPaws\Helpers\WordHelper;
use FourPaws\PersonalBundle\Entity\Order;
use FourPaws\PersonalBundle\Entity\OrderItem;
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

if (!$arResult['ORDER']) {
    return;
}
/** @var Order $order */
$order = $arResult['ORDER'];

/** @var OrderSubscribe $orderSubscribe */
$orderSubscribe = $arParams['ORDER_SUBSCRIBE'] ?? null;

/**
 * Подписка на доставку заказа
 * (элементы управления подпиской и попап c формой)
 * @todo Сделать вызов попапа через ajax
 */
$subscribeOrderAddControls = '';
$subscribeOrderEditControls = '';
if ($order->canBeSubscribed()) {
    /** @var \FourPawsPersonalCabinetOrdersSubscribeFormComponent $subscribeFormComponent */
    $subscribeFormComponent = $APPLICATION->IncludeComponent(
        'fourpaws:personal.orders.subscribe.form',
        'popup',
        [
            'ORDER_ID' => $order->getId(),
            // Y - вставлять html через отложенные функции
            'OUTPUT_VIA_BUFFER' => 'Y',
        ],
        $component,
        [
            'HIDE_ICONS' => 'Y',
        ]
    );
    if ($subscribeFormComponent->arResult['CONTROLS_HTML']) {
        if ($orderSubscribe) {
            // элементы управления подпиской
            $subscribeOrderEditControls = $subscribeFormComponent->arResult['CONTROLS_HTML']['EDIT'];
        } else {
            // элементы добавления подписки
            $subscribeOrderAddControls = $subscribeFormComponent->arResult['CONTROLS_HTML']['ADD'];
        }
    }
}

$attr = '';
if ($orderSubscribe) {
    $attr .= ' data-first-subscribe="'.$orderSubscribe->getDateStart().'"';
    $attr .= ' data-interval="'.$orderSubscribe->getDeliveryTime().'"';
    $attr .= ' data-frequency="'.$orderSubscribe->getDeliveryFrequency().'"';
    //$attr .= ' data-id="'.$orderSubscribe->getOrderId().'"';
}
?>
<li<?=$attr?> class="b-accordion-order-item js-permutation-li js-item-content">
    <div class="b-accordion-order-item__visible js-premutation-accordion-content">
        <div class="b-accordion-order-item__info">
            <a class="b-accordion-order-item__open-accordion js-open-accordion"
               href="javascript:void(0);"
               title="">
                <span class="b-accordion-order-item__arrow">
                    <span class="b-icon b-icon--account">
                        <?= new SvgDecorator('icon-arrow-account', 25, 25) ?>
                    </span>
                </span>
                <?php
                if ($orderSubscribe) {
                    ?>
                    <span class="b-accordion-order-item__number-order">
                        <?=($orderSubscribe->getDeliveryFrequencyValue().', '.$orderSubscribe->getDateStartWeekdayRu())?>
                    </span>
                    <?php
                } else {
                    ?>
                    <span class="b-accordion-order-item__number-order">
                        <?=('№ '.$order->getId().' от '.$order->getFormatedDateInsert())?>
                    </span>
                    <?php
                }
                ?>
            </a>
            <?php $countItems = $order->getItems()->count(); ?>
            <div class="b-accordion-order-item__info-order"><?= $countItems ?> <?= WordHelper::declension($countItems,
                    ['товар', 'товара', 'товаров']) ?> (<?= $order->getFormatedAllWeight() ?> кг)
            </div>
        </div>
        <div class="b-accordion-order-item__adress">
            <div class="b-accordion-order-item__date b-accordion-order-item__date--new">
                <?= $order->getStatus() ?>
                <span>с <?= $order->getFormatedDateStatus() ?></span>
            </div>
            <div class="b-accordion-order-item__date b-accordion-order-item__date--pickup">
                <?= $order->getDelivery()->getDeliveryName() ?>
                <span><?= $order->getDateDelivery() ?></span>
            </div>
            <div class="b-adress-info b-adress-info--order">
                <?php
                $currentMetro = $order->getStore()->getMetro();
                if (!empty($currentMetro)) {
                    /** @var ArrayCollection $metroCollection */
                    $metroCollection = $arResult['METRO'];
                    $metroItem = $metroCollection->get($currentMetro);
                    if (!empty($metroItem)) {
                        ?>
                        <span class="b-adress-info__label b-adress-info__label--<?= $metroItem['BRANCH']['UF_CLASS'] ?>"></span>
                        м. <?= $metroItem['UF_NAME'] ?>,
                    <?php }
                }?>
                <?= $order->getStore()->getAddress() ?>
                <?php if (!empty($order->getStore()->getSchedule())) { ?>
                    <p class="b-adress-info__mode-operation"><?= $order->getStore()->getSchedule() ?></p>
                <?php } ?>
            </div>
        </div>
        <div class="b-accordion-order-item__pay">
            <div class="b-accordion-order-item__not-pay">
                <?= $order->getPayPrefixText() . ' ' . $order->getPayment()->getName() ?>
            </div>
        </div>
        <div class="b-accordion-order-item__button js-button-default">
            <?php
            if (!$orderSubscribe && $order->isClosed() && !$order->isManzana()) {
                $uri = new Uri(Application::getInstance()->getContext()->getRequest()->getRequestUri());
                $uri->addParams(['reply_order' => 'Y', 'id' => $order->getId()]);
                ?>
                <div class="b-accordion-order-item__subscribe-link b-accordion-order-item__subscribe-link--full">
                    <a class="b-link b-link--repeat-order b-link--repeat-order" href="<?= $uri->getUri() ?>"
                       title="Повторить заказ">
                        <span class="b-link__text b-link__text--repeat-order">Повторить заказ</span>
                    </a>
                </div>
                <?php
            }

            if (!$orderSubscribe && !$order->isClosed() && !$order->isPayed() && !$order->isManzana() && $order->getPayment()->getCode() === 'card-online') {
                ?>
                <div class="b-accordion-order-item__subscribe-link b-accordion-order-item__subscribe-link--full">
                    <a class="b-link b-link--pay-account b-link--pay-account"
                       href="<?='/sale/payment/?ORDER_ID='.$order->getId()?>"
                       title="Оплатить">
                        <span class="b-link__text b-link__text--pay-account">Оплатить</span>
                    </a>
                </div>
                <?php
            }

            // элементы управления подпиской
            echo $subscribeOrderEditControls;

            ?>
            <div class="b-accordion-order-item__sum b-accordion-order-item__sum--full">
                <?= $order->getFormatedPrice() ?>
                <span class="b-ruble b-ruble--account-accordion">&nbsp;₽</span>
            </div>
            <?php

            // элементы добавления подписки
            echo $subscribeOrderAddControls;

            ?>
        </div>
    </div>
    <div class="b-accordion-order-item__hidden js-hidden-order">
        <ul class="b-list-order">
            <?php /** @var OrderItem $item */
            foreach ($order->getItems() as $item) { ?>
                <li class="b-list-order__item">
                    <div class="b-list-order__image-wrapper">
                        <img class="b-list-order__image js-image-wrapper"
                             src="<?= $item->getImagePath() ?>" alt="<?= $item->getName() ?>"
                             title="<?= $item->getName() ?>"
                             role="presentation"/>
                    </div>
                    <div class="b-list-order__wrapper">
                        <div class="b-list-order__info">
                            <?php /** @todo акционный товар */ ?>
                            <?php if ($item->isHaveStock()) { ?>
                                <div class="b-list-order__action">Сейчас
                                    участвует в акции
                                </div>
                            <?php } ?>
                            <div class="b-clipped-text b-clipped-text--account">
                                <span>
                                    <?php if (!empty($item->getBrand())) { ?>
                                        <strong><?= $item->getBrand() ?>  </strong>
                                    <?php } ?><?= $item->getName() ?>
                                </span>
                            </div>
                            <div class="b-list-order__option">
                                <?php if (!empty($item->getOfferSelectedProp())) { ?>
                                    <div class="b-list-order__option-text">
                                        <?= $item->getOfferSelectedPropName() ?>:
                                        <span><?= $item->getOfferSelectedProp() ?></span>
                                    </div>
                                <?php } ?>
                                <?php if ($item->getWeight() > 0) { ?>
                                    <div class="b-list-order__option-text">Вес:
                                        <span><?= round($item->getWeight() / 1000, 2) ?> кг</span>
                                    </div>
                                <?php } ?>
                                <?php if (!empty($item->getArticle())) { ?>
                                    <div class="b-list-order__option-text">Артикул:
                                        <span><?= $item->getArticle() ?></span>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="b-list-order__price">
                            <div class="b-list-order__sum b-list-order__sum--item"><?= $item->getFormatedSum() ?>
                                <span
                                        class="b-ruble b-ruble--account-accordion">&nbsp;₽</span>
                            </div>
                            <?php if ($item->getQuantity() > 1) { ?>
                                <div class="b-list-order__calculation"><?= $item->getFormatedPrice() ?> ₽
                                    × <?= $item->getQuantity() ?> шт
                                </div>
                            <?php } ?>
                            <?php if ($item->getBonus() > 0) { ?>
                                <div class="b-list-order__bonus">
                                    + <?= $item->getBonus() . ' ' . WordHelper::declension($item->getBonus(),
                                        ['бонус', 'бонуса', 'бонусов']) ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </li>
            <?php } ?>
        </ul>
        <div class="b-accordion-order-item__calculation-full">
            <ul class="b-characteristics-tab__list">
                <li class="b-characteristics-tab__item b-characteristics-tab__item--account">
                    <div class="b-characteristics-tab__characteristics-text b-characteristics-tab__characteristics-text--account">
                        <span>Товары</span>
                        <div class="b-characteristics-tab__dots">
                        </div>
                    </div>
                    <div class="b-characteristics-tab__characteristics-value b-characteristics-tab__characteristics-value--account">
                        <?= $order->getFormatedItemsSum() ?><span
                                class="b-ruble b-ruble--calculation-account">&nbsp;₽</span>
                    </div>
                </li>
                <?php if ($order->getDelivery()->getPriceDelivery() > 0) { ?>
                    <li class="b-characteristics-tab__item b-characteristics-tab__item--account">
                        <div class="b-characteristics-tab__characteristics-text b-characteristics-tab__characteristics-text--account">
                            <span>Доставка</span>
                            <div class="b-characteristics-tab__dots">
                            </div>
                        </div>
                        <div class="b-characteristics-tab__characteristics-value b-characteristics-tab__characteristics-value--account">
                            <?= $order->getDelivery()->getFormatedPriceDelivery() ?><span
                                    class="b-ruble b-ruble--calculation-account">&nbsp;₽</span>
                        </div>
                    </li>
                <?php } ?>
                <li class="b-characteristics-tab__item b-characteristics-tab__item--account">
                    <div class="b-characteristics-tab__characteristics-text b-characteristics-tab__characteristics-text--account b-characteristics-tab__characteristics-text--last">
                        <span>Итого к оплате</span>
                        <div class="b-characteristics-tab__dots">
                        </div>
                    </div>
                    <div class="b-characteristics-tab__characteristics-value b-characteristics-tab__characteristics-value--account b-characteristics-tab__characteristics-value--last">
                        <?= $order->getFormatedPrice() ?><span
                                class="b-ruble b-ruble--calculation-account">&nbsp;₽</span>
                    </div>
                </li>
            </ul>
        </div>
    </div>
    <div class="b-accordion-order-item__mobile-bottom js-button-permutation-mobile">
    </div>
</li>
<?php
