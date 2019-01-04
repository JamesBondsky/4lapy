<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var \CBitrixComponentTemplate $this
 *
 * @var array $arParams
 * @var array $arResult
 * @var array $templateData
 *
 * @var string $componentPath
 * @var string $templateName
 * @var string $templateFile
 * @var string $templateFolder
 *
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CDatabase $DB
 */

/** @var \FourPaws\Catalog\Model\Offer $currentOffer */
$currentOffer = $arParams['OFFER'];

?>
<li class="b-product-information__item">
    <div class="b-product-information__title-info">Наличие</div>
    <div class="b-product-information__value"><?=$currentOffer->getAvailabilityText()?></div>
</li>
<?php
if ($currentOffer->isAvailable()) {
    if ($pickup = $arResult['PICKUP']) {
        include __DIR__ . '/include/pickup-info.php';
    }
    if ($delivery = $arResult['DELIVERY']) {
        include __DIR__ . '/include/delivery-info.php';
    }
}
?>
