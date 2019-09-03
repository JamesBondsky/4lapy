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
<div class="fashion-main-slider">
    <div class="b-container">
        <div class="fashion-main-slider__slides slick-slide" data-fashion-main-slider style="display: none;">
            <? foreach ($arResult['ITEMS'] as $key => $item) { ?>
                <? //if (!empty($item['DESKTOP_PICTURE'])) {?>
                <div class="fashion-main-slider__item">
                    <a href="<? //TODO: ссылка ?>">
                    <picture class="fashion-main-slider__image">
                        <source srcset="<?= CFile::GetPath($item['PROPERTIES']['IMG_TABLET']['VALUE']) ?>" media="(min-width: 768px)" />
                        <source srcset="<?= $item['PREVIEW_PICTURE']['SRC'] ?>" media="(max-width: 767px)" />

                        <img src="<?= $item['DETAIL_PICTURE']['SRC']?>" alt="<?= $item['NAME'] ?>"/>
                    </picture>
                    </a>
                </div>
                <? //} ?>
            <? } ?>
        </div>
    </div>
</div>