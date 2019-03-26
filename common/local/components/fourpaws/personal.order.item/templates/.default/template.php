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
use FourPaws\SaleBundle\Enum\OrderPayment;
use FourPaws\SaleBundle\Enum\OrderStatus;
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
                        $manzanaId = $order->getManzanaId();
                        $accountNumber = $order->getAccountNumber();
                        if ($manzanaId)
                        {
                        	$orderNumber = $manzanaId === $accountNumber . 'NEW' ? $accountNumber : $manzanaId;
                        }
                        else
                        {
                            $orderNumber = $accountNumber;
                        }
                        ?>
                        <span class="b-accordion-order-item__number-order">
                            <?= ('№ ' . $orderNumber . ' от ' . $order->getFormattedDateInsert()) ?>
                        </span>
                </a>
                <?php

                $countItems = 0;
                /** @var OrderItem $orderItem */
                foreach ($order->getItems() as $orderItem) {
                    if ($orderItem->getParentItem()) {
                        continue;
                    }
                    $countItems++;
                }

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
                    <?
                        echo $order->getStatus();
                        echo ' ';
                        echo '<span>';
                        echo ' ';
                        /** предлог "с" только для статусов "В пунке выдачи" и "В сборке" */
                        $checkStatuses = [
                            OrderStatus::STATUS_IN_ASSEMBLY_1,
                            OrderStatus::STATUS_IN_ASSEMBLY_2,
                            OrderStatus::STATUS_ISSUING_POINT,
                        ];
                        echo \in_array($order->getStatus(), $checkStatuses, true) ? 'с&nbsp;' : '';
                        echo $order->getFormattedDateStatus();
                        echo ' ';
                        echo '</span>';
                    ?>
                </div>
                <?php if (!$order->isFastOrder()) { ?>
                    <div class="b-accordion-order-item__date b-accordion-order-item__date--pickup">
                        <?= $order->getDelivery()
                            ->getDeliveryName() ?>
                        <span><?= $order->getDateDelivery() ?></span>
                    </div>
                <?php }
                $store = $order->getStore();
                if ($store !== null && $store->isActive()) {
                    $address = trim($store->getAddress());
                    if (!empty($address)) { ?>
                        <div class="b-adress-info b-adress-info--order">
                            <?php if ($arResult['METRO'] !== null && $store->getMetro() > 0) { ?>
                                <span class="b-adress-info__label b-adress-info__label--<?= $arResult['METRO']->get($store->getMetro())['BRANCH']['UF_CLASS'] ?>"></span>
                                м. <?= $arResult['METRO']->get($store->getMetro())['UF_NAME'] ?>,
                            <?php }
                            echo $address;
                            if (!empty($store->getScheduleString())) { ?>
                                <p class="b-adress-info__mode-operation"><?= $store->getScheduleString() ?></p>
                            <?php } ?>
                        </div>
                    <?php }
                } ?>
            </div>
            <div class="b-accordion-order-item__pay">
                <div class="b-accordion-order-item__not-pay">
                    <?php
                    $paymentName = '';
                    $payment = $order->getPayment();
                    $paymentCode = $payment->getCode();
                    switch ($paymentCode) {
                        case OrderPayment::PAYMENT_CASH_OR_CARD:
                            $paymentName = 'наличными или картой';
                            break;
                        case OrderPayment::PAYMENT_ONLINE:
                            $paymentName = 'онлайн';
                            break;
                        case OrderPayment::PAYMENT_CASH:
                            $paymentName = 'наличными';
                            break;
                    }
                    if ($paymentCode === 'cash' && !$order->getManzanaId() && !$order->isPayed()) {
                        /** т.к. неоплаченных заказов будет не очень много у пользователя - оставим расчет здесь */
                        /** @var OrderService $orderService */
                        $orderService = SymfoniApplication::getInstance()
                            ->getContainer()
                            ->get(OrderService::class);
                        $bitrixOrder = BitrixOrder::load($order->getId());
                        if ($bitrixOrder !== null && $bitrixOrder->getId() > 0) {
                            $commWay = $orderService->getOrderPropertyByCode($bitrixOrder, 'COM_WAY');
                            if ($commWay->getValue() === OrderPropertyService::COMMUNICATION_PAYMENT_ANALYSIS) {
                                $paymentName = 'Постоплата';
                            }
                        }
                    }
                    if ($order->isFastOrder()
                        && \in_array($order->getStatusId(), [
                            'N',
                            'Q'
                        ], true)) {
                        $paymentName = 'Постоплата';
                    }
                    if ($paymentName && $paymentName !== 'Постоплата') {
                        $paymentName = $order->getPayPrefixText() . ' ' . $paymentName;
                    }

                    echo $paymentName; ?>
                </div>
            </div>
            <div class="b-accordion-order-item__button js-button-default">
                <?php
                if (!$order->getManzanaId()) {
                    $uri = new Uri(Application::getInstance()
                        ->getContext()
                        ->getRequest()
                        ->getRequestUri());
                    $uri->addParams([
                        'reply_order' => 'Y',
                        'id'          => $order->getId()
                    ]);?>
                    <div class="b-accordion-order-item__subscribe-link b-accordion-order-item__subscribe-link--full">
                        <a class="b-link b-link--repeat-order b-link--repeat-order" href="<?= $uri->getUri() ?>"
                           title="Повторить заказ">
                            <span class="b-link__text b-link__text--repeat-order">Повторить заказ</span>
                        </a>
                    </div>
                <?php }
                /*
                if (!$isOrderSubscribePage && !$order->isClosed() && !$order->isPayed() && !$order->getManzanaId() && $order->getPayment()->getCode() === 'card-online') {
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
                ?>
                <div class="b-accordion-order-item__sum b-accordion-order-item__sum--full">
                    <?php
                    /**
                     * [LP03-908] В подписке на доставку не отображаем бонусы
                     */
                    echo $order->getFormattedPrice();
                    ?>
                    <span class="b-ruble b-ruble--account-accordion">&nbsp;₽</span>
                </div>
            </div>
        </div>
        <div class="b-accordion-order-item__hidden js-hidden-order">
            <ul class="b-list-order">
                <?php /** @var OrderItem $item */
                foreach ($order->getItems() as $item) {
                    if ($item->getParentItem()) {
                        continue;
                    }
                    if (!$order->getManzanaId() && $item->hasDetailPageUrl() && !$item->isGift()) { ?>
                        <a href="<?= $item->getDetailPageUrl() ?>">
                    <?php } ?>
                    <li class="b-list-order__item">
                        <div class="b-list-order__image-wrapper">
                            <?php if ($item->getImagePath()) { ?>
                                <img class="b-list-order__image js-image-wrapper"
                                     src="<?= $item->getImagePath() ?>" alt="<?= $item->getName() ?>"
                                     title="<?= $item->getName() ?>"
                                     role="presentation"/>
                            <?php } ?>
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
                                        <span class="span-strong"><?= $item->getBrand() ?>  </span>
                                    <?php } ?><?= $item->getName() ?>
                                    </span>
                                </div>
                                <div class="b-list-order__option">
                                    <?php if (!empty($item->getFlavour())) { ?>
                                        <div class="b-list-order__option-text">
                                            Вкус:
                                            <span><?= $item->getFlavour() ?></span>
                                        </div>
                                    <?php }

                                    if (!empty($item->getOfferSelectedProp())) { ?>
                                        <div class="b-list-order__option-text">
                                            <?= $item->getOfferSelectedPropName() ?>:
                                            <span><?= $item->getOfferSelectedProp() ?></span>
                                        </div>
                                    <?php }

                                    if ($item->getWeight() > 0) { ?>
                                        <div class="b-list-order__option-text">Вес:
                                            <span><?= WordHelper::showWeight($item->getWeight(), true) ?></span>
                                        </div>
                                    <?php }

                                    if (!empty($item->getArticle())) { ?>
                                        <div class="b-list-order__option-text">Артикул:
                                            <span><?= $item->getArticle() ?></span>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="b-list-order__price">
                                <div class="b-list-order__sum b-list-order__sum--item"><?= $item->getFormattedSum() ?>
                                    <span class="b-ruble b-ruble--account-accordion">&nbsp;₽</span>
                                </div>
                                <?php if (($item->getQuantity() > 1)
                                          || !$item->getDetachedItems()
                                        ->isEmpty()) { ?>
                                    <div class="b-list-order__calculation"><?= $item->getFormattedPrice() ?> ₽
                                        × <?= $item->getQuantity() ?> шт
                                    </div>
                                <?php } ?>
                                <?php foreach ($item->getDetachedItems() as $childItem) { ?>
                                    <div class="b-list-order__calculation"><?= $childItem->getFormattedPrice() ?> ₽
                                        × <?= $childItem->getQuantity() ?> шт
                                    </div>
                                <?php } ?>
                                <div class="b-list-order__bonus js-order-item-bonus-<?= $order->getManzanaId() ? 'manzana-' : '' ?><?= $item->getId() ?>"></div>
                            </div>
                        </div>
                    </li>
                    <?php if (!$order->getManzanaId() && !empty($item->getDetailPageUrl())) { ?>
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
                    <?php if ($order->getDelivery()
                                  ->getPriceDelivery() > 0) { ?>
                        <li class="b-characteristics-tab__item b-characteristics-tab__item--account">
                            <div class="b-characteristics-tab__characteristics-text b-characteristics-tab__characteristics-text--account">
                                <span>Доставка</span>
                                <div class="b-characteristics-tab__dots"></div>
                            </div>
                            <div class="b-characteristics-tab__characteristics-value b-characteristics-tab__characteristics-value--account">
                                <?= $order->getDelivery()
                                    ->getFormatedPriceDelivery() ?>
                                <span class="b-ruble b-ruble--calculation-account">&nbsp;₽</span>
                            </div>
                        </li>
                        <?php
                    }

                    /**
                     * [LP03-908] В подписке на доставку не отображаем бонусы
                     */
                    if ($order->getBonusPay() > 0) {
                        ?>
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
                        <?php
                    }
                    ?>
                    <li class="b-characteristics-tab__item b-characteristics-tab__item--account">
                        <div class="b-characteristics-tab__characteristics-text b-characteristics-tab__characteristics-text--account b-characteristics-tab__characteristics-text--last">
                            <span>Итого к оплате</span>
                            <div class="b-characteristics-tab__dots"></div>
                        </div>
                        <div class="b-characteristics-tab__characteristics-value b-characteristics-tab__characteristics-value--account b-characteristics-tab__characteristics-value--last">
                            <?= ($order->getFormattedPrice()) ?>
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
