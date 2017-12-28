<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @noinspection PhpUndefinedClassInspection */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);
?>
<div class="b-container" id="<?= $id ?>">
    <div class="b-detail-page b-detail-page--bordered">
        <?php if (is_array($arResult['DETAIL_PICTURE'])) { ?>
            <img src="<?= $arResult['DETAIL_PICTURE']['SRC']; ?>" />
        <?php } ?>
        <?= htmlspecialcharsback($arResult['DETAIL_TEXT']) ?>
        <?php if (!$arResult['NO_SHOW_VIDEO'] && !empty($arResult['DISPLAY_PROPERTIES']['VIDEO']['DISPLAY_VALUE'])) {
            echo $arResult['DISPLAY_PROPERTIES']['VIDEO']['DISPLAY_VALUE'];
        } ?>
        <?php if (!$arResult['NO_SHOW_SLIDER']
                  && \is_array($arResult['DISPLAY_PROPERTIES']['MORE_PHOTO']['DISPLAY_VALUE'])
                  && !empty($arResult['DISPLAY_PROPERTIES']['MORE_PHOTO']['DISPLAY_VALUE'])) { ?>
            <div class="b-detail-page-slider js-detail-slider">
                <?php /** @noinspection ForeachSourceInspection - условие есть выше */
                foreach ($arResult['DISPLAY_PROPERTIES']['MORE_PHOTO']['DISPLAY_VALUE'] as $photo) {
                    if (is_array($photo)) { ?>
                        <div class="b-detail-page-slider__item">
                            <img src="<?= $photo['SRC'] ?>">
                        </div>
                    <?php }
                } ?>
            </div>
        <?php } ?>
    </div>
</div>