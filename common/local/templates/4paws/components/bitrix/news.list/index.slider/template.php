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

?>
<div class="b-main-slider js-main-slider">
    <?php foreach ($arResult['ITEMS'] as $item) { ?>
        <div class="b-main-item js-image-wrapper">
            <a class="b-main-item__link-main"
               href="<?= $item['PROPERTIES']['LINK']['VALUE'] ?>"
               title="<?= $item['NAME'] ?>">
                <img class="b-main-item__slider-background js-image-wrapper"
                     src="<?= $item['PICTURE'] ?>"
                     alt="<?= $item['NAME'] ?>"
                     role="presentation" />
            </a>
        </div>
    <?php } ?>
</div>
