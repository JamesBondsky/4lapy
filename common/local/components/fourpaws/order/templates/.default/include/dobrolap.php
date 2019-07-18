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
use FourPaws\SaleBundle\Entity\OrderStorage;
use FourPaws\SaleBundle\Enum\OrderPayment;
use FourPaws\SaleBundle\Service\OrderService;
use FourPaws\StoreBundle\Entity\Store;

/**
 * @var array $arResult
 * @var array $arParams
 * @var CalculationResultInterface $deliveryDobrolap
 * @var
 */

/** @var DeliveryService $deliveryService */
$deliveryService = Application::getInstance()->getContainer()->get('delivery.service');
/** @var OrderStorage $storage */
$storage = $arResult['STORAGE'];
/** @var Store $selectedShop */
$selectedShop = $arResult['DOBROLAP_SELECTED_SHOP'];
/** @var StockResultCollection $available */
$available = $arResult['DOBROLAP_STOCKS_AVAILABLE'];
/** @var StockResultCollection $delayed */
$delayed = $arResult['DOBROLAP_STOCKS_DELAYED'];


$canGetPartial = $arResult['DOBROLAP_PARTIAL_AVAILABLE'];
$canSplit = $arResult['DOBROLAP_SPLIT_AVAILABLE'];
$partialGet = $canGetPartial || $canSplit;
$partialPickup = $arResult['DOBROLAP_PARTIAL'] ?? $deliveryDobrolap;
?>

<div class="b-input-line b-input-line--address b-input-line--myself">
    <div class="b-input-line__label-wrapper">
        <span class="b-input-line__label">Адрес доставки</span>
    </div>

    <div class="b-input">
        <select name="" class="b-input__input-field b-input__input-field--with-border" required>
            <option value="">не выбрано</option>
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
        </select>
    </div>
</div>

<?/*<a class="b-link b-link--another-point js-open-popup"
   href="javascript:void(0);"
   data-popup-id="popup-order-stores"
   title="">
    Выбрать другой питомник
</a>*/?>
