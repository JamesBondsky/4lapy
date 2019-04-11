<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\App\Application;
use FourPaws\Decorators\SvgDecorator;
use FourPaws\DeliveryBundle\Collection\StockResultCollection;
use FourPaws\DeliveryBundle\Entity\CalculationResult\CalculationResultInterface;
use FourPaws\DeliveryBundle\Entity\CalculationResult\PickupResult;
use FourPaws\Helpers\CurrencyHelper;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\DeliveryBundle\Entity\CalculationResult\BaseResult;
use FourPaws\DeliveryBundle\Entity\StockResult;
use FourPaws\DeliveryBundle\Helpers\DeliveryTimeHelper;
use FourPaws\PersonalBundle\Entity\OrderSubscribe;
use FourPaws\SaleBundle\Entity\OrderStorage;
use FourPaws\SaleBundle\Enum\OrderPayment;
use FourPaws\SaleBundle\Service\OrderService;
use FourPaws\StoreBundle\Entity\Store;

/**
 * @var array $arResult
 * @var array $arParams
 * @var CalculationResultInterface $pickup
 * @var
 */

/** @var DeliveryService $deliveryService */
$deliveryService = Application::getInstance()->getContainer()->get('delivery.service');
/** @var OrderSubscribe $subscribe */
$subscribe = $arResult['SUBSCRIBE'];

/** @var Store $selectedShop */
$selectedShop = $arResult['SELECTED_SHOP'];

/** @var StockResultCollection $available */
$available = $arResult['PICKUP_STOCKS_AVAILABLE'];
/** @var StockResultCollection $delayed */
$delayed = $arResult['PICKUP_STOCKS_DELAYED'];

$metro = $arResult['METRO'][$selectedShop->getMetro()];
?>

<li class="b-radio-tab__tab js-email-recovery">
    <div class="b-input-line b-input-line--address b-input-line--myself">
        <div class="b-input-line__label-wrapper">
            <span class="b-input-line__label">Адрес доставки</span>
        </div>
        <ul class="b-delivery-list">
            <li class="b-delivery-list__item b-delivery-list__item--myself">
                <span class="b-delivery-list__link b-delivery-list__link--myself">
                    <?php if ($metro) { ?>
                        <span class="b-delivery-list__col b-delivery-list__col--color b-delivery-list__col--<?= $metro['BRANCH']['UF_COLOR'] ?>"></span>
                        <?= 'м. ' . $metro['UF_NAME'] . ', ' . $selectedShop->getAddress() ?>
                    <?php } else { ?>
                        <?= $selectedShop->getAddress() ?>
                    <?php } ?>
                </span>
            </li>
        </ul>
    </div>
    <div class="b-input-line b-input-line--myself">
        <div class="b-input-line__label-wrapper">
            <span class="b-input-line__label">Время работы</span>
        </div>
        <div class="b-input-line__text-line b-input-line__text-line--myself">
            <?= $selectedShop->getScheduleString() ?>
        </div>
    </div>
    <div class="b-input-line b-input-line--myself">
        <div class="b-input-line__label-wrapper">
            <span class="b-input-line__label">Оплата</span>
        </div>
        <div class="b-input-line__text-line">
            <?php foreach ($arResult['PICKUP_AVAILABLE_PAYMENTS'] as $payment) {
                $icon = $payment['CODE'] === OrderPayment::PAYMENT_CASH ? 'icon-cash' : 'icon-bank-card'
                ?>
                <span class="b-input-line__pay-type">
                    <span class="b-icon b-icon--icon-cash">
                        <?= new SvgDecorator($icon, 16, 12) ?>
                    </span>
                    <span class="b-input-line_pay-type--name">
                        <?= $payment['NAME'] ?>
                    </span>
                </span>
            <?php } ?>
        </div>
    </div>
    <? if(!$subscribe) { ?>
        <div class="b-input-line b-input-line--desired-date" data-url="<?= $arResult['URL']['DELIVERY_INTERVALS'] ?>">
            <div class="b-input-line__label-wrapper">
                <span class="b-input-line__label">Желаемая дата первой доставки</span>
            </div>
            <div class="b-select b-select--recall b-select--feedback-page">
                <?php
                $selectorDelivery = $delivery;
                $selectorName = 'deliveryDate';
                include 'delivery_date_select.php'
                ?>
            </div>
        </div>
    <? } ?>
    <a class="b-link b-link--another-point js-open-popup"
       href="javascript:void(0);"
       data-popup-id="popup-order-stores"
       title="">
        Выбрать другой пункт самовывоза
    </a>
</li>
