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
    <?php if ($currentOffer->isByRequest() && $currentOffer->isAvailable()) { ?>
        <div class="b-product-information__value">Только под заказ</div>
    <?php } elseif ($currentOffer->isAvailable()) { ?>
        <div class="b-product-information__value">Нет в наличии</div>
    <?php } else { ?>
        <div class="b-product-information__value">В наличии</div>
    <?php } ?>
</li>
<?php
if ($arResult['CURRENT']['PICKUP']) {
    $pickup = $arResult['CURRENT']['PICKUP'];
    include __DIR__ . '/include/pickup-info.php';
}
if ($arResult['CURRENT']['DELIVERY']) {
    $delivery = $arResult['CURRENT']['DELIVERY'];
    include __DIR__ . '/include/delivery-info.php';
}
?>
