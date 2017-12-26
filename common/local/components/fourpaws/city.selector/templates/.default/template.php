<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var \CBitrixComponentTemplate $this
 *
 * @var array                     $arParams
 * @var array                     $arResult
 * @var array                     $templateData
 *
 * @var string                    $componentPath
 * @var string                    $templateName
 * @var string                    $templateFile
 * @var string                    $templateFolder
 *
 * @global CUser                  $USER
 * @global CMain                  $APPLICATION
 * @global CDatabase              $DB
 */

use FourPaws\Decorators\SvgDecorator;

$this->setFrameMode(true);
?>
<?php $frame = $this->createFrame()->begin() ?>
    <div class="b-header__wrapper-for-popover js-city-permutation">
        <a class="b-combobox b-combobox--header js-open-popover" href="javascript:void(0);" title="<?= $arResult['SELECTED_CITY']['NAME'] ?>">
            <span class="b-icon b-icon--location">
                <?= new SvgDecorator('icon-delivery-header', 14, 16) ?>
            </span>
            <span class="b-combobox__name-city"><?= $arResult['SELECTED_CITY']['NAME'] ?></span>
            <span class="b-icon b-icon--delivery-arrow">
                <?= new SvgDecorator('icon-arrow-down', 13, 13) ?>
            </span>
        </a>
        <div
        class="b-popover b-popover--blue-arrow b-popover--city js-your-city js-popover">
            <p class="b-popover__text">Ваш город&nbsp;&mdash; 
                <span><?= $arResult['SELECTED_CITY']['NAME'] ?></span>?
            </p>
            <a
                class="b-popover__link"
                href="javascript:void(0)"
                title=""
                data-url="<?= $arResult['CITY_SET_URL'] ?>"
                data-code="<?= $arResult['SELECTED_CITY']['CODE'] ?>">
                Да
            </a>
            <a class="b-popover__link b-popover__link--last js-open-popup" href="javascript:void(0)" title="" data-popup-id="pick-city">Нет, выбрать другой</a>
    </div>
<?php $frame->beginStub() ?>
    <div class="b-header__wrapper-for-popover js-city-permutation">
        <a class="b-combobox b-combobox--header js-open-popover" href="javascript:void(0);" title="<?= $arResult['DEFAULT_CITY']['NAME'] ?>">
            <span class="b-icon b-icon--location">
                <?= new SvgDecorator('icon-delivery-header', 14, 16) ?>
            </span>
            <span class="b-combobox__name-city"><?= $arResult['DEFAULT_CITY']['NAME'] ?></span>
            <span class="b-icon b-icon--delivery-arrow">
                <?= new SvgDecorator('icon-arrow-down', 13, 13) ?>
            </span>
        </a>
        <div
        class="b-popover b-popover--blue-arrow b-popover--city js-your-city js-popover">
            <p class="b-popover__text">Ваш город&nbsp;&mdash; 
                <span><?= $arResult['DEFAULT_CITY']['NAME'] ?></span>?
            </p>
            <a
                class="b-popover__link"
                href="javascript:void(0)"
                title=""
                data-url="<?= $arResult['CITY_SET_URL'] ?>"
                data-code="<?= $arResult['SELECTED_CITY']['CODE'] ?>">
                Да
            </a>
            <a class="b-popover__link b-popover__link--last js-open-popup" href="javascript:void(0)" title="" data-popup-id="pick-city">Нет, выбрать другой</a>
    </div>
<?php $frame->end() ?>
