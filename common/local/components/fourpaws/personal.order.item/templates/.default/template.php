<?php

use Bitrix\Main\Application;
use Bitrix\Main\Web\Uri;
use Bitrix\Sale\Order as BitrixOrder;
use FourPaws\App\Application as SymfoniApplication;
use FourPaws\Decorators\SvgDecorator;
use FourPaws\Helpers\DateHelper;
use FourPaws\Helpers\WordHelper;
use FourPaws\PersonalBundle\Entity\Order;
use FourPaws\PersonalBundle\Entity\OrderItem;
use FourPaws\PersonalBundle\Entity\OrderSubscribe;
use FourPaws\SaleBundle\Service\OrderPropertyService;
use FourPaws\SaleBundle\Service\OrderService;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * @global CMain                                  $APPLICATION
 * @var array                                     $arParams
 * @var array                                     $arResult
 * @var FourPawsPersonalCabinetOrderItemComponent $component
 * @var CBitrixComponentTemplate                  $this
 * @var string                                    $templateName
 * @var string                                    $componentPath
 */

/** @var Order $order */
$order = $arResult['ORDER'];

/** @var OrderSubscribe $orderSubscribe */
// ORDER_SUBSCRIBE приходит только если нужно вывести форму редактирования подписки
$orderSubscribe = $arParams['ORDER_SUBSCRIBE'] ?? null;

/**
 * Подписка на доставку заказа
 * (элементы управления подпиской и попап c формой)
 * @todo Сделать вызов попапа через ajax
 */
$subscribeOrderAddControls = '';
$subscribeOrderEditControls = '';

$genSubscribeControls = false;
if (!$genSubscribeControls && $component->getOrderSubscribeService()->canBeSubscribed($order)) {
    $genSubscribeControls = true;
}
if (!$genSubscribeControls && $orderSubscribe) {
    $genSubscribeControls = true;
}
$tmpOrderSubscribe = null;
if (!$genSubscribeControls && !$orderSubscribe) {
    // здесь проверяем, нет ли уже оформленной подписки на заказ,
    // на который по новым условиям уже подписаться нельзя
    $tmpOrderSubscribe = $component->getOrderSubscribeService()->getSubscribeByOrderId($order->getId());
    $genSubscribeControls = $tmpOrderSubscribe ? true : false;
}

if ($genSubscribeControls) {
    /** @var \FourPawsPersonalCabinetOrdersSubscribeFormComponent $subscribeFormComponent */
    $subscribeFormComponent = $APPLICATION->IncludeComponent(
        'fourpaws:personal.orders.subscribe.form',
        'popup',
        [
            'ORDER_ID'          => $order->getId(),
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
    $attr .= ' data-first-subscribe="' . $orderSubscribe->getDateStart() . '"';
    $attr .= ' data-interval="' . $orderSubscribe->getDeliveryTime() . '"';
    $attr .= ' data-frequency="' . $orderSubscribe->getDeliveryFrequency() . '"';
    //$attr .= ' data-id="'.$orderSubscribe->getOrderId().'"';
}

?>
    <li<?= $attr ?> class="b-accordion-order-item js-permutation-li js-item-content">
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
                            <?php
                            echo $orderSubscribe->getDeliveryFrequencyEntity()->getValue();
                            echo ', ';
                            echo $orderSubscribe->getDateStartWeekdayRu();
                            ?>
                        </span>
                        <?php
                    } else {
                        ?>
                        <span class="b-accordion-order-item__number-order">
                            <?= ('№ ' . $order->getId() . ' от ' . $order->getFormatedDateInsert()) ?>
                        </span>
                        <?php
                    }
                    ?>
                </a>
                <?php
                $orderItems = $order->getItems();
                $countItems = $orderItems !== null ? $order->getItems()->count() : 0;

                if ($countItems > 0) { ?>
                    <div class="b-accordion-order-item__info-order"><?= $countItems ?> <?= WordHelper::declension($countItems,
                            [
                                'товар',
                                'товара',
                                'товаров',
                            ]) ?> <?= $order->getAllWeight() > 0 ? '(' . WordHelper::showWeight($order->getAllWeight(),
                                true) . ')' : ''; ?>
                    </div>
                <?php } ?>
            </div>
            <div class="b-accordion-order-item__adress">
                <div class="b-accordion-order-item__date b-accordion-order-item__date--new">
                    <?php
                    if ($orderSubscribe) {
                        echo '<span>';
                        echo 'Следующая доставка ';
                        echo DateHelper::replaceRuMonth(
                            $orderSubscribe->getNextDeliveryDate()->format('d #n# Y'),
                            DateHelper::GENITIVE,
                            true
                        );
                        echo '</span>';
                    } else {
                        echo $order->getStatus();
                        echo ' ';
                        echo '<span>';
                        echo ' ';
                        /** предлог "с" только для статусов "В пунке выдачи" и "В сборке" */
                        $checkStatuses = [
                            OrderService::STATUS_IN_ASSEMBLY_1,
                            OrderService::STATUS_IN_ASSEMBLY_2,
                            OrderService::STATUS_ISSUING_POINT,
                        ];
                        echo \in_array($order->getStatus(), $checkStatuses, true) ? 'с&nbsp;' : '';
                        echo $order->getFormatedDateStatus();
                        echo ' ';
                        echo '</span>';
                    }
                    ?>
                </div>
                <?php if (!$order->isFastOrder()) { ?>
                    <div class="b-accordion-order-item__date b-accordion-order-item__date--pickup">
                        <?= $order->getDelivery()->getDeliveryName() ?>
                        <span><?= $order->getDateDelivery() ?></span>
                    </div>
                <?php }
                $store = $order->getStore();
                if ($store !== null && $store->getId() > 0 && !empty($store->getAddress())) { ?>
                    <div class="b-adress-info b-adress-info--order">
                        <?php if ($store->getMetro() > 0 && $arResult['METRO'] !== null) { ?>
                            <span class="b-adress-info__label b-adress-info__label--<?= $arResult['METRO']->get($store->getMetro())['BRANCH']['UF_CLASS'] ?>"></span>
                            м. <?= $arResult['METRO']->get($order->getStore()->getMetro())['UF_NAME'] ?>,
                        <?php }
                        echo $order->getStore()->getAddress();
                        if (!empty($order->getStore()->getScheduleString())) { ?>
                            <p class="b-adress-info__mode-operation"><?= $order->getStore()->getScheduleString() ?></p>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
            <div class="b-accordion-order-item__pay">
                <div class="b-accordion-order-item__not-pay">
                    <?php
                    $paymentName = '';
                    if ($orderSubscribe) {
                        $paymentName = 'Оплата наличными или картой при получении';
                    } else {
                        $payment = $order->getPayment();
                        $paymentCode = $payment->getCode();
                        switch ($paymentCode) {
                            case OrderService::PAYMENT_CASH_OR_CARD:
                                $paymentName = 'наличными или картой';
                                break;
                            case OrderService::PAYMENT_ONLINE:
                                $paymentName = 'онлайн';
                                break;
                            case OrderService::PAYMENT_CASH:
                                $paymentName = 'наличными';
                                break;
                        }
                        if ($paymentCode === 'cash' && !$order->isManzana() && !$order->isPayed()) {
                            /** т.к. неоплаченных заказов будет не очень много у пользователя - оставим расчет здесь */
                            /** @var OrderService $orderService */
                            $orderService = SymfoniApplication::getInstance()->getContainer()->get(OrderService::class);
                            $bitrixOrder = BitrixOrder::load($order->getId());
                            if ($bitrixOrder !== null && $bitrixOrder->getId() > 0) {
                                $commWay = $orderService->getOrderPropertyByCode($bitrixOrder, 'COM_WAY');
                                if ($commWay->getValue() === OrderPropertyService::COMMUNICATION_PAYMENT_ANALYSIS) {
                                    $paymentName = 'Постоплата';
                                }
                            }
                        }
                        if ($order->isFastOrder() && \in_array($order->getStatusId(), ['N', 'Q'], true)) {
                            $paymentName = 'Постоплата';
                        }
                        if (!empty($paymentName)) {
                            if($paymentName !== 'Постоплата') {
                                $paymentName = $order->getPayPrefixText() . ' ' . $paymentName;
                            }
                        }
                    }
                    echo $paymentName;
                    ?>
                </div>
            </div>
            <div class="b-accordion-order-item__button js-button-default">
                <?php
                if (!$orderSubscribe && (!$order->isManzana() || $order->isNewManzana())) {
                    $uri = new Uri(Application::getInstance()->getContext()->getRequest()->getRequestUri());
                    $uri->addParams(['reply_order' => 'Y', 'id' => $order->getId()]);
                    if ($order->isNewManzana()) {

                        $uri->addParams([
                            'is_manzana' => true,
                            'item_ids'   => json_encode($order->getItemIdsQuantity()),
                        ]);
                    }
                    ?>
                    <div class="b-accordion-order-item__subscribe-link b-accordion-order-item__subscribe-link--full">
                        <a class="b-link b-link--repeat-order b-link--repeat-order" href="<?= $uri->getUri() ?>"
                           title="Повторить заказ">
                            <span class="b-link__text b-link__text--repeat-order">Повторить заказ</span>
                        </a>
                    </div>
                    <?php
                }
                /*
                if (!$orderSubscribe && !$order->isClosed() && !$order->isPayed() && !$order->isManzana() && $order->getPayment()->getCode() === 'card-online') {
                    ?>
                    <div class="b-accordion-order-item__subscribe-link b-accordion-order-item__subscribe-link--full">
                        <a class="b-link b-link--pay-account b-link--pay-account"
                           href="<?= '/sale/payment/?ORDER_ID='.$order->getId() ?>"
                           title="Оплатить">
                            <span class="b-link__text b-link__text--pay-account">Оплатить</span>
                        </a>
                    </div>
                    <?php
                }
                */

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
                foreach ($order->getItems() as $item) {
                    if(!$order->isManzana() && !empty($item->getDetailPageUrl())){ ?>
                        <a href="<?=$item->getDetailPageUrl()?>">
                    <?php } ?>
                    <li class="b-list-order__item">
                        <div class="b-list-order__image-wrapper">
                            <img class="b-list-order__image js-image-wrapper"
                                 src="<?= $item->getImagePath() ?>" alt="<?= $item->getName() ?>"
                                 title="<?= $item->getName() ?>"
                                 role="presentation"/>
                        </div>
                        <div class="b-list-order__wrapper">
                            <div class="b-list-order__info">
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
                                    <?php if (!empty($item->getFlavour())) { ?>
                                        <div class="b-list-order__option-text">
                                            Вкус:
                                            <span><?= $item->getFlavour() ?></span>
                                        </div>
                                    <?php } ?>
                                    <?php if (!empty($item->getOfferSelectedProp())) { ?>
                                        <div class="b-list-order__option-text">
                                            <?= $item->getOfferSelectedPropName() ?>:
                                            <span><?= $item->getOfferSelectedProp() ?></span>
                                        </div>
                                    <?php } ?>
                                    <?php if ($item->getWeight() > 0) { ?>
                                        <div class="b-list-order__option-text">Вес:
                                            <span><?= WordHelper::showWeight($item->getWeight(), true) ?></span>
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
                                    <span class="b-ruble b-ruble--account-accordion">&nbsp;₽</span>
                                </div>
                                <?php if ($item->getQuantity() > 1) { ?>
                                    <div class="b-list-order__calculation"><?= $item->getFormatedPrice() ?> ₽
                                        × <?= $item->getQuantity() ?> шт
                                    </div>
                                <?php } ?>
                                <div class="b-list-order__bonus js-order-item-bonus-<?= $order->isManzana() ? 'manzana-' : '' ?><?= $item->getId() ?>"></div>
                            </div>
                        </div>
                    </li>
                    <?php if(!$order->isManzana() && !empty($item->getDetailPageUrl())){ ?>
                        </a>
                    <?php } ?>
                <?php } ?>
            </ul>
            <div class="b-accordion-order-item__calculation-full">
                <ul class="b-characteristics-tab__list">
                    <li class="b-characteristics-tab__item b-characteristics-tab__item--account">
                        <div class="b-characteristics-tab__characteristics-text b-characteristics-tab__characteristics-text--account">
                            <span>Товары</span>
                            <div class="b-characteristics-tab__dots"></div>
                        </div>
                        <div class="b-characteristics-tab__characteristics-value b-characteristics-tab__characteristics-value--account">
                            <?= $order->getFormattedItemsSum() ?>
                            <span class="b-ruble b-ruble--calculation-account">&nbsp;₽</span>
                        </div>
                    </li>
                    <?php if ($order->getDelivery()->getPriceDelivery() > 0) { ?>
                        <li class="b-characteristics-tab__item b-characteristics-tab__item--account">
                            <div class="b-characteristics-tab__characteristics-text b-characteristics-tab__characteristics-text--account">
                                <span>Доставка</span>
                                <div class="b-characteristics-tab__dots"></div>
                            </div>
                            <div class="b-characteristics-tab__characteristics-value b-characteristics-tab__characteristics-value--account">
                                <?= $order->getDelivery()->getFormatedPriceDelivery() ?>
                                <span class="b-ruble b-ruble--calculation-account">&nbsp;₽</span>
                            </div>
                        </li>
                    <?php }
                    if($order->getBonusPay() > 0){ ?>
                        <li class="b-characteristics-tab__item b-characteristics-tab__item--account">
                            <div class="b-characteristics-tab__characteristics-text b-characteristics-tab__characteristics-text--account">
                                <span>Оплата бонусами</span>
                                <div class="b-characteristics-tab__dots"></div>
                            </div>
                            <div class="b-characteristics-tab__characteristics-value b-characteristics-tab__characteristics-value--account">
                                -<?= $order->getBonusPayFormatted() ?>
                                <span class="b-ruble b-ruble--calculation-account">&nbsp;₽</span>
                            </div>
                        </li>
                    <?php } ?>
                    <li class="b-characteristics-tab__item b-characteristics-tab__item--account">
                        <div class="b-characteristics-tab__characteristics-text b-characteristics-tab__characteristics-text--account b-characteristics-tab__characteristics-text--last">
                            <span>Итого к оплате</span>
                            <div class="b-characteristics-tab__dots"></div>
                        </div>
                        <div class="b-characteristics-tab__characteristics-value b-characteristics-tab__characteristics-value--account b-characteristics-tab__characteristics-value--last">
                            <?= $order->getFormatedPrice() ?>
                            <span class="b-ruble b-ruble--calculation-account">&nbsp;₽</span>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
        <div class="b-accordion-order-item__mobile-bottom js-button-permutation-mobile">
        </div>
    </li>
<?php
