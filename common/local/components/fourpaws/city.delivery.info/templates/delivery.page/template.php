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

?>
<div class="b-delivery">
    <div class="b-delivery__delivery-type">
        <p class="b-title b-title--h2">Способы доставки</p>
        <?php
        if ($delivery = $arResult['DELIVERY']) {
            include __DIR__ . '/include/delivery-info.php';
        }
        if ($pickup = $arResult['PICKUP']) {
            include __DIR__ . '/include/pickup-info.php';
        }
        ?>
    </div>
</div>
