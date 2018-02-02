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
$available = $stockResult->getAvailable();
$delayed = $stockResultByShop->getDelayed();

$metro = $arResult['METRO'][$selectedShop->getMetro()];

$canGetPartial = !$available->isEmpty() && !$delayed->isEmpty() && $deliveryService->isInnerPickup($pickup);
$partialGet = $canGetPartial && $storage->isPartialGet();

?>
<div class="b-input-line b-input-line--address b-input-line--myself">
    <div class="b-input-line__label-wrapper">
        <span class="b-input-line__label">Адрес доставки</span>
    </div>
    <ul class="b-delivery-list">
        <li class="b-delivery-list__item b-delivery-list__item--myself">
            <span class="b-delivery-list__link b-delivery-list__link--myself">
                <?php if ($metro) { ?>
                    <span class="b-delivery-list__col b-delivery-list__col--color b-delivery-list__col--<?= $metro['BRANCH']['UF_COLOR'] ?>"></span>
                <?php } ?>
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
    <?php if ($canGetPartial) { ?>
        <div class="b-input-line__label-wrapper b-input-line__label-wrapper--order-full">
            <span class="b-input-line__label">Заказ в наличии частично</span>
        </div>
        <div class="b-radio b-radio--tablet-big">
            <input class="b-radio__input"
                   type="radio"
                   name="isPartialGet"
                   id="order-pick-time-now"
                <?= $partialGet ? 'checked="checked"' : '' ?>
                   value="1"/>
            <label class="b-radio__label b-radio__label--tablet-big"
                   for="order-pick-time-now">
            </label>
            <div class="b-order-list b-order-list--myself">
                <ul class="b-order-list__list">
                    <li class="b-order-list__item b-order-list__item--myself">
                        <div class="b-order-list__order-text b-order-list__order-text--myself">
                            <div class="b-order-list__clipped-text">
                                <div class="b-order-list__text-backed">
                                    <?= DeliveryTimeHelper::showTime(
                                        $partialPickup,
                                        $available->getDeliveryDate()
                                    ) ?>
                                </div>
                            </div>
                        </div>
                        <div class="b-order-list__order-value b-order-list__order-value--myself">
                            <?= CurrencyHelper::formatPrice($available->getPrice()) ?>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="b-radio__addition-text">
                <p>За исключением:</p>
                <ol>
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
    <?php } ?>
    <div class="b-radio b-radio--tablet-big">
        <input class="b-radio__input"
               type="radio"
               name="isPartialGet"
               id="order-pick-time-then"
            <?= !$partialGet ? 'checked="checked"' : '' ?>
               value="0"/>
        <label class="b-radio__label b-radio__label--tablet-big"
               for="order-pick-time-then">
        </label>
        <div class="b-order-list b-order-list--myself">
            <ul class="b-order-list__list">
                <li class="b-order-list__item b-order-list__item--myself">
                    <div class="b-order-list__order-text b-order-list__order-text--myself">
                        <div class="b-order-list__clipped-text">
                            <div class="b-order-list__text-backed">
                                Забрать полный заказ
                            </div>
                        </div>
                    </div>
                    <div class="b-order-list__order-value b-order-list__order-value--myself">
                        <?= CurrencyHelper::formatPrice($stockResultByShop->getPrice()) ?>
                    </div>
                </li>
            </ul>
        </div>
        <div class="b-radio__addition-text">
            <p><?= DeliveryTimeHelper::showTime($pickup, $stockResultByShop->getDeliveryDate()) ?></p>
        </div>
    </div>
</div>
<a class="b-link b-link--another-point" href="javascript:void(0);" title="">
    Выбрать другой пункт самовывоза
</a>
