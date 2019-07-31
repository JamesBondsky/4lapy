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
 * @var array                      $arResult
 * @var array                      $arParams
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

$chosen_shelter = null;

if (count($arResult['SHELTERS'])) {
    $chosen_shelter = $arResult['SHELTERS'][0];

    foreach ($arResult['SHELTERS'] as $shelter) {
        if ($shelter['checked']) {
            $chosen_shelter = $shelter;
        }
    }
}
?>

<?php if ($chosen_shelter): ?>
    <div class="b-input-line b-input-line--address b-input-line--myself">
        <input type="hidden" name="shelter" class="js-shelter-delivery-id" <?= $deliveryService->isDobrolapDelivery($selectedDelivery) ? 'required' : '' ?> value="<?=$chosen_shelter['barcode']?>" />

        <div class="b-input-line__label-wrapper">
            <span class="b-input-line__label">Приют для доставки</span>
        </div>

        <div class="b-input-line__text-line b-input-line__text-line--myself js-shelter-delivery-title">
            <?=$chosen_shelter['name']?>, <?=$chosen_shelter['city']?>
        </div>
    </div>
<?php endif ?>

<?
$this->SetViewTarget('shelter_popup');
$APPLICATION->IncludeComponent(
    'fourpaws:order.shelter.list',
    'popup',
    [
        'SHELTERS' => $arResult['SHELTERS']
    ],
    null,
    [
        'HIDE_ICONS' => 'Y'
    ]
);
$this->EndViewTarget();
?>
