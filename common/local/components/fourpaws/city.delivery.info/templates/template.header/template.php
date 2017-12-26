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

use FourPaws\Decorators\SvgDecorator;
use FourPaws\Helpers\CurrencyHelper;

$this->setFrameMode(true);

if (empty($arResult)) {
    return;
}

?>
<?php $frame = $this->createFrame()->begin() ?>
<div class="b-header__wrapper-for-popover">
    <a class="b-combobox b-combobox--delivery b-combobox--header js-open-popover"
       href="javascript:void(0);"
       title="<?= $arResult['CURRENT']['LOCATION']['NAME'] ?>">
        <span class="b-icon b-icon--delivery-header">
            <?= new SvgDecorator('icon-delivery', 20, 16) ?>
        </span>
        <?php if ($arResult['CURRENT']['DELIVERY']['FREE_FROM']) { ?>
            Бесплатная доставка
        <?php } else { ?>
            Доставка <?= CurrencyHelper::formatPrice($arResult['CURRENT']['DELIVERY']['PRICE']) ?>
        <?php } ?>
        <span class="b-icon b-icon--delivery-arrow">
            <?= new SvgDecorator('icon-arrow-down', 20, 16) ?>
        </span>
    </a>
    <div class="b-popover b-popover--blue-arrow js-popover">
        <p class="b-popover__text">Доставка <?= CurrencyHelper::formatPrice(
                $arResult['CURRENT']['DELIVERY']['PRICE']
            ) ?></p>
        <?php if ($arResult['CURRENT']['DELIVERY']['FREE_FROM']) { ?>
            <p class="b-popover__text b-popover__text--last">Бесплатно при заказе
                от <?= CurrencyHelper::formatPrice($arResult['CURRENT']['DELIVERY']['FREE_FROM']) ?></p>
        <?php } ?>
    </div>
</div>
<?php $frame->beginStub() ?>
<div class="b-header__wrapper-for-popover">
    <a class="b-combobox b-combobox--delivery b-combobox--header js-open-popover"
       href="javascript:void(0);"
       title="<?= $arResult['DEFAULT']['LOCATION']['NAME'] ?>">
        <span class="b-icon b-icon--delivery-header">
            <?= new SvgDecorator('icon-delivery', 20, 16) ?>
        </span>
        <?php if ($arResult['DEFAULT']['DELIVERY']['FREE_FROM']) { ?>
            Бесплатная доставка
        <?php } else { ?>
            Доставка <?= CurrencyHelper::formatPrice($arResult['DEFAULT']['DELIVERY']['PRICE']) ?>
        <?php } ?>
        <span class="b-icon b-icon--delivery-arrow">
            <?= new SvgDecorator('icon-arrow-down', 20, 16) ?>
        </span>
    </a>
    <div class="b-popover b-popover--blue-arrow js-popover">
        <p class="b-popover__text">Доставка <?= CurrencyHelper::formatPrice(
                $arResult['DEFAULT']['DELIVERY']['PRICE']
            ) ?></p>
        <?php if ($arResult['DEFAULT']['DELIVERY']['FREE_FROM']) { ?>
            <p class="b-popover__text b-popover__text--last">Бесплатно при заказе
                от <?= CurrencyHelper::formatPrice($arResult['DEFAULT']['DELIVERY']['FREE_FROM']) ?></p>
        <?php } ?>
    </div>
</div>
<?php $frame->end() ?>
