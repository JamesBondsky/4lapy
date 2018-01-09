<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var CBitrixComponentTemplate $this
 * @var array $arParams
 * @var array $arResult
 */

$banner = reset($arResult['ITEMS']);
?>

<div class="b-main-item b-main-item--catalog">
    <a class="b-main-item__link b-main-item__link--catalog"
       href="<?= $banner['DISPLAY_PROPERTIES']['LINK']['VALUE'] ?>"
       title="<?= $banner['NAME'] ?>">
        <?php if ($banner['DESKTOP_PICTURE']) { ?>
            <img class="b-main-item__slider-background b-main-item__slider-background--desktop"
                 src="<?= $banner['DESKTOP_PICTURE']->getSrc() ?>" alt="<?= $banner['NAME'] ?>" role="presentation"/>
            <img class="b-main-item__slider-background b-main-item__slider-background--min-desktop"
                 src="<?= $banner['DESKTOP_PICTURE']->getSrc() ?>" alt="<?= $banner['NAME'] ?>" role="presentation"/>
        <?php } ?>
        <?php if ($banner['TABLET_PICTURE']) { ?>
            <img class="b-main-item__slider-background b-main-item__slider-background--tablet"
                 src="<?= $banner['TABLET_PICTURE']->getSrc() ?>" alt="<?= $banner['NAME'] ?>" role="presentation"/>
        <?php } ?>
        <?php if ($banner['MOBILE_PICTURE']) { ?>
            <img class="b-main-item__slider-background b-main-item__slider-background--mobile"
                 src="<?= $banner['MOBILE_PICTURE']->getSrc() ?>" alt="<?= $banner['NAME'] ?>" role="presentation"/>
        <?php } ?>
    </a>
</div>
