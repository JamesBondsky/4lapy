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

$this->setFrameMode(true);
?>
<?php $frame = $this->createFrame()->begin() ?>
<div class="b-header__wrapper-for-popover">
    <a class="b-combobox b-combobox--delivery b-combobox--header js-open-popover"
       href="javascript:void(0);"
       title="<?= $arResult['CURRENT']['CITY_NAME'] ?>">
        <span class="b-icon b-icon--delivery-header">
            <?= new SvgDecorator('icon-delivery', 20, 16) ?>
        </span>
        <?php if ($arResult['CURRENT']['FREE_FROM']) { ?>
            Бесплатная доставка
        <?php } else { ?>
            Доставка <?= $arResult['CURRENT']['PRICE'] ?> ₽
        <?php } ?>
        <span class="b-icon b-icon--delivery-arrow">
            <?= new SvgDecorator('icon-arrow-down', 20, 16) ?>
        </span>
    </a>
    <div class="b-popover b-popover--blue-arrow js-popover">
        <p class="b-popover__text"><?= $arResult['CURRENT']['PRICE'] ?> ₽</p>
        <?php if ($arResult['CURRENT']['FREE_FROM']) { ?>
            <p class="b-popover__text b-popover__text--last">Бесплатно при заказе от <?= $arResult['CURRENT']['FREE_FROM'] ?> ₽</p>
        <?php } ?>
    </div>
</div>
<?php $frame->beginStub() ?>
<div class="b-header__wrapper-for-popover">
    <a class="b-combobox b-combobox--delivery b-combobox--header js-open-popover"
       href="javascript:void(0);"
       title="<?= $arResult['DEFAULT']['CITY_NAME'] ?>">
        <span class="b-icon b-icon--delivery-header">
            <?= new SvgDecorator('icon-delivery', 20, 16) ?>
        </span>
        <?php if ($arResult['DEFAULT']['FREE_FROM']) { ?>
            Бесплатная доставка
        <?php } else { ?>
            Доставка <?= $arResult['DEFAULT']['PRICE'] ?> ₽
        <?php } ?>
        <span class="b-icon b-icon--delivery-arrow">
            <?= new SvgDecorator('icon-arrow-down', 20, 16) ?>
        </span>
    </a>
    <div class="b-popover b-popover--blue-arrow js-popover">
        <p class="b-popover__text"><?= $arResult['DEFAULT']['PRICE'] ?> ₽</p>
        <?php if ($arResult['DEFAULT']['FREE_FROM']) { ?>
            <p class="b-popover__text b-popover__text--last">Бесплатно при заказе от <?= $arResult['DEFAULT']['FREE_FROM'] ?> ₽</p>
        <?php } ?>
    </div>
</div>
<?php $frame->end() ?>
