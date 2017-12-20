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

$this->setFrameMode(true);
?>
<div class="b-container b-container--delivery">
    <div class="b-delivery">
        <div class="b-delivery__delivery-type">
            <p class="b-title b-title--h2">Способы доставки</p>
            <?php
            $frame = $this->createFrame()->begin();
            if (!empty($arResult['CURRENT']['DELIVERY'])) {
                $delivery = $arResult['CURRENT']['DELIVERY'];
                include __DIR__ . '/include/delivery-info.php';
            }
            if (!empty($arResult['CURRENT']['PICKUP'])) {
                $pickup = $arResult['CURRENT']['PICKUP'];
                include __DIR__ . '/include/pickup-info.php';
            }
            $frame->beginStub();
            if (!empty($arResult['DEFAULT']['DELIVERY'])) {
                $delivery = $arResult['DEFAULT']['DELIVERY'];
                include __DIR__ . '/include/delivery-info.php';
            }
            if (!empty($arResult['DEFAULT']['PICKUP'])) {
                $pickup = $arResult['DEFAULT']['PICKUP'];
                include __DIR__ . '/include/pickup-info.php';
            }
            $frame->end()
            ?>
        </div>
    </div>
</div>
<div class="b-container b-container--delivery">
    <div class="b-delivery">
        <div class="b-delivery__payment-type">
            <p class="b-title b-title--h2">Способы оплаты</p>
            <?php
                $frame = $this->createFrame()->begin();
                $payments = $arResult['CURRENT']['PAYMENTS'];
                include __DIR__ . '/include/payment-info.php';
                $frame->beginStub();
                $payments = $arResult['DEFAULT']['PAYMENTS'];
                include __DIR__ . '/include/payment-info.php';
                $frame->end()
            ?>
        </div>
    </div>
</div>
