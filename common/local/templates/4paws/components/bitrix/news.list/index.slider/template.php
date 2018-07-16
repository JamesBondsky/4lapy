<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
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

if (!\is_array($arResult['ITEMS']) || empty($arResult['ITEMS'])) {
    return;
}

if ($arResult['ECOMMERCE_VIEW_SCRIPT']) {
    echo $arResult['ECOMMERCE_VIEW_SCRIPT'];
} ?>
<div class="b-main-slider js-main-slider">
    <?php foreach ($arResult['ITEMS'] as $item) { ?>
        <div class="b-main-item b-main-item--background js-image-wrapper"
            <?= !empty($item['BACKGROUND']) ? ' style=\'background: url("'
                . $item['BACKGROUND']
                . '")\'' : '' ?>>
            <a class="b-main-item__link-main"
                <?php if ($item['ECOMMERCE_CLICK_SCRIPT']) {
                    echo \sprintf('onclick="%s;"', \str_replace('"', '\'', $item['ECOMMERCE_CLICK_SCRIPT']));
                } ?>
               href="<?= $item['PROPERTIES']['LINK']['VALUE'] ?>"
               title="<?= $item['NAME'] ?>"><?php
                if (!empty($item['DESKTOP_PICTURE'])) {
                    ?>
                    <img class="b-main-item__slider-background b-main-item__slider-background--desktop js-image-wrapper"
                         src="<?= $item['DESKTOP_PICTURE'] ?>"
                         alt="<?= $item['NAME'] ?>"
                         role="presentation"><?php
                }

                if (!empty($item['TABLET_PICTURE'])) {
                    ?>
                    <img class="b-main-item__slider-background b-main-item__slider-background--tablet js-image-wrapper"
                         src="<?= $item['TABLET_PICTURE'] ?>"
                         alt="<?= $item['NAME'] ?>"
                         role="presentation"><?php
                }

                if (!empty($item['MOBILE_PICTURE'])) {
                    ?>
                    <img class="b-main-item__slider-background b-main-item__slider-background--mobile js-image-wrapper"
                         src="<?= $item['MOBILE_PICTURE'] ?>"
                         alt="<?= $item['NAME'] ?>"
                         role="presentation"><?php
                }
                ?></a>
        </div>
    <?php } ?>
</div>
