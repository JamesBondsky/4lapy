<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Sale\Delivery\CalculationResult;
use FourPaws\App\Application;
use FourPaws\Decorators\SvgDecorator;
use FourPaws\Helpers\CurrencyHelper;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\DeliveryBundle\Collection\StockResultCollection;
use FourPaws\DeliveryBundle\Entity\StockResult;
use FourPaws\DeliveryBundle\Helpers\DeliveryTimeHelper;
use FourPaws\SaleBundle\Entity\OrderStorage;
use FourPaws\StoreBundle\Entity\Store;

/**
 * @var array $arResult
 * @var array $arParams
 * @var CalculationResult $pickup
 * @var
 */

/** @var CalculationResult $partialPickup */
$partialPickup = $arResult['PARTIAL_PICKUP'];

/** @var DeliveryService $deliveryService */
$deliveryService = Application::getInstance()->getContainer()->get('delivery.service');
/** @var StockResultCollection $stockResult */
$stockResult = $pickup->getData()['STOCK_RESULT'];
/** @var OrderStorage $storage */
$storage = $arResult['STORAGE'];

/** @var Store $selectedShop */
$selectedShop = $arResult['SELECTED_SHOP'];
$stockResultByShop = $stockResult->filterByStore($selectedShop);
$available = $stockResultByShop->getAvailable();
$delayed = $stockResultByShop->getDelayed();

$metro = $arResult['METRO'][$selectedShop->getMetro()];

$canGetPartial = !$available->isEmpty();
$partialGet = ($canGetPartial && $storage->isPartialGet()) || $delayed->isEmpty();

?>

<li class="b-radio-tab__tab js-email-recovery">
    <div class="b-input-line b-input-line--address b-input-line--myself">
        <div class="b-input-line__label-wrapper">
            <span class="b-input-line__label">Адрес доставки</span>
        </div>
        <ul class="b-delivery-list">
            <li class="b-delivery-list__item b-delivery-list__item--myself">
                <span class="b-delivery-list__link b-delivery-list__link--myself">
                    <span class="b-delivery-list__col b-delivery-list__col--color b-delivery-list__col--grey"></span>
                    <?= $selectedShop->getAddress() ?>
                </span>
            </li>
        </ul>
    </div>
    <div class="b-input-line b-input-line--myself">
        <div class="b-input-line__label-wrapper">
            <span class="b-input-line__label">Время работы</span>
        </div>
        <div class="b-input-line__text-line b-input-line__text-line--myself">
            <?= $selectedShop->getSchedule() ?>
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
            <span class="b-input-line__label js-parts-info">
                <?php if (!$delayed->isEmpty() && !$available->isEmpty()) { ?>
                    Заказ в наличии частично
                <?php } elseif ($available->isEmpty()) { ?>
                    Требуется ждать поставки со склада
                <?php } else { ?>
                    Заказ доступен в полном составе
                <?php } ?>
            </span>
        </div>
        <div class="b-radio b-radio--tablet-big" <?= $canGetPartial ? '' : 'style="display:none"' ?>>
            <input class="b-radio__input"
                   type="radio"
                   name="order-pick-time"
                   id="order-pick-time-now"
                <?= $partialGet ? 'checked="checked"' : '' ?>
                   value="1"/>
            <label class="b-radio__label b-radio__label--tablet-big" for="order-pick-time-now">
            </label>
            <div class="b-order-list b-order-list--myself js-parts-price">
                <ul class="b-order-list__list">
                    <li class="b-order-list__item b-order-list__item--myself js-parts-price">
                        <div class="b-order-list__order-text b-order-list__order-text--myself js-parts-price">
                            <div class="b-order-list__clipped-text">
                                <div class="b-order-list__text-backed">
                                    Забрать <?= DeliveryTimeHelper::showTime(
                                        $partialPickup,
                                        $available->getDeliveryDate()
                                    ) ?>
                                </div>
                            </div>
                        </div>
                        <div class="b-order-list__order-value b-order-list__order-value--myself js-parts-price">
                            <?= CurrencyHelper::formatPrice($available->getPrice()) ?>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="b-radio__addition-text js-excluded-parts" <?= $delayed->isEmpty(
            ) ? 'style="display:none"' : '' ?>>
                <p>За исключением:</p>
                <ol class="js-delay-items">
                    <?php /** @var StockResult $item */ ?>
                    <?php foreach ($delayed as $item) { ?>
                        <li>
                            <?= $item->getOffer()->getName() ?> <?= ($item->getAmount() > 1) ? ('(' . $item->getAmount(
                                ) . ' шт)') : '' ?>
                        </li>
                    <?php } ?>
                </ol>
            </div>
        </div>
        <div class="b-radio b-radio--tablet-big" <?= $delayed->isEmpty() ? 'style="display:none"' : '' ?>>
            <input class="b-radio__input"
                   type="radio"
                   name="order-pick-time"
                   id="order-pick-time-then"
                <?= !$partialGet ? 'checked="checked"' : '' ?>
                   value="0"/>
            <label class="b-radio__label b-radio__label--tablet-big" for="order-pick-time-then">
            </label>
            <div class="b-order-list b-order-list--myself js-full-price">
                <ul class="b-order-list__list">
                    <li class="b-order-list__item b-order-list__item--myself js-full-price">
                        <div class="b-order-list__order-text b-order-list__order-text--myself js-full-price">
                            <div class="b-order-list__clipped-text">
                                <div class="b-order-list__text-backed">Забрать полный заказ</div>
                            </div>
                        </div>
                        <div class="b-order-list__order-value b-order-list__order-value--myself js-full-price">
                            <?= CurrencyHelper::formatPrice($stockResultByShop->getPrice()) ?>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="b-radio__addition-text">
                <p class="js-pickup_full"><?= DeliveryTimeHelper::showTime(
                        $pickup,
                        $stockResultByShop->getDeliveryDate()
                    ) ?></p>
            </div>
        </div>
    </div>
    <a class="b-link b-link--another-point js-open-popup"
       href="javascript:void(0);"
       data-popup-id="popup-order-stores"
       title="">
        Выбрать другой пункт самовывоза
    </a>
</li>
