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

/** @var OrderSubscribe $orderSubscribe */
// ORDER_SUBSCRIBE приходит только если нужно вывести форму редактирования подписки
$orderSubscribe = $arParams['ORDER_SUBSCRIBE'] ?? null;
$isOrderSubscribePage = $orderSubscribe ? true : false;

/**
 * Подписка на доставку заказа
 * (элементы управления подпиской и попап c формой)
 *
 * @todo Сделать вызов попапа через ajax
 */
$subscribeOrderDeleteControls = '';

$genSubscribeControls = false;
if (!$genSubscribeControls
    && $component->getOrderSubscribeService()
        ->canBeSubscribed($order)) {
    $genSubscribeControls = true;
}
if (!$genSubscribeControls && $orderSubscribe) {
    $genSubscribeControls = true;
}
$tmpOrderSubscribe = null;
if (!$genSubscribeControls && !$orderSubscribe) {
    // здесь проверяем, нет ли уже оформленной подписки на заказ,
    // на который по новым условиям уже подписаться нельзя
    $tmpOrderSubscribe = $component->getOrderSubscribeService()
        ->getSubscribeByOrderId($order->getId());
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
        // элементы управления подпиской
        $subscribeOrderDeleteControls = $subscribeFormComponent->arResult['CONTROLS_HTML']['DELETE'];

    }
}

$attr = '';
if ($orderSubscribe) {
    $attr .= ' data-first-subscribe="' . $orderSubscribe->getDateStart() . '"';
    $attr .= ' data-interval="' . $orderSubscribe->getDeliveryTime() . '"';
    $attr .= ' data-frequency="' . $orderSubscribe->getDeliveryFrequency() . '"';
}

?>
    <li<?= $attr ?> class="b-accordion-order-item b-accordion-order-item--subscribe js-permutation-li js-item-content">
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

                    <span class="b-accordion-order-item__img-wrap">
                        <img class="b-accordion-order-item__img"
                             src="<?= $order->getItems()->first()->getImagePath() ?>"
                             alt="<?= $order->getItems()->first()->getName() ?>"
                             title="<?= $order->getItems()->first()->getName() ?>"
                             role="presentation"/>
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

                if ($countItems > 1) { ?>
                    <div class="b-accordion-order-item__info-order">+ ещё <?= $countItems - 1 ?> <?= WordHelper::declension($countItems - 1,
                            [
                                'товар',
                                'товара',
                                'товаров',
                            ]) ?>
                    </div>
                <?php } ?>
            </div>
            <div class="b-accordion-order-item__adress">
                <div class="b-accordion-order-item__number-order">
                    <?php
                    echo $orderSubscribe->getDeliveryFrequencyEntity()
                        ->getValue();
                    echo ', ';
                    echo $orderSubscribe->getDateStartWeekdayRu();
                    ?>
                </div>
                <div class="b-accordion-order-item__date b-accordion-order-item__date--new">
                    <?php
                        echo 'Следующая доставка ';
                        echo DateHelper::replaceRuMonth(
                            $orderSubscribe->getNextDeliveryDate()
                                ->format('d #n# Y'),
                            DateHelper::GENITIVE,
                            true
                        );
                        echo '</span>';
                     ?>
                </div>
                <div class="b-adress-info b-adress-info--order">
                        <span class="b-adress-info__label b-adress-info__label--sokol"></span>
                        м. Кунцевская,
                        <p class="b-adress-info__mode-operation">Какой-то адрес</p>

                </div>
                <?php
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
            <div class="b-accordion-order-item__button">
                <a class="b-link b-link--repeat-order b-link--change-subscribe-delivery" href="javascript:void(0);" title="Редактировать подписку">
                    <span class="b-link__text b-link__text--change-subscribe-delivery">Редактировать подписку</span>
                </a>
                <a class="b-link b-link--repeat-order b-link--change-subscribe-delivery" href="javascript:void(0);" title="Остановить подписку">
                    <span class="b-link__text b-link__text--change-subscribe-delivery">Остановить подписку</span>
                </a>
                <?php /*
                <a class="b-link b-link--repeat-order b-link--repeat-order" href="" title="Возобновить подписку">
                    <span class="b-link__text b-link__text--repeat-order">Возобновить подписку</span>
                </a>
                */?>
            </div>
            <div class="b-accordion-order-item__operation">
                <div class="b-accordion-order-item__sum">
                    <?php
                    /**
                     * [LP03-908] В подписке на доставку не отображаем бонусы
                     */
                    echo $isOrderSubscribePage ? $order->getFormattedPriceReal() : $order->getFormattedPrice();
                    ?>
                    <span class="b-ruble b-ruble--account-accordion">&nbsp;₽</span>
                </div>
                <?php echo $subscribeOrderDeleteControls; ?>
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
                                        <spa class="span-strong"><?= $item->getBrand() ?>  </span>
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
                    if (!$isOrderSubscribePage && $order->getBonusPay() > 0) {
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
                            <?= ($isOrderSubscribePage ? $order->getFormattedPriceReal() : $order->getFormattedPrice()) ?>
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
