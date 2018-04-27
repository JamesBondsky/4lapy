<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var CatalogSectionComponent $component
 * @var CBitrixComponentTemplate $this
 */

if (!$arResult['PRINT_ITEMS']) {
    return;
}
$arParams['WRAP_CONTAINER_BLOCK'] = isset($arParams['WRAP_CONTAINER_BLOCK']) ? $arParams['WRAP_CONTAINER_BLOCK'] : 'N';
$arParams['WRAP_SECTION_BLOCK'] = isset($arParams['WRAP_SECTION_BLOCK']) ? $arParams['WRAP_SECTION_BLOCK'] : 'N';
$arParams['WRAP_SECTION_BLOCK'] =  $arParams['WRAP_CONTAINER_BLOCK'] === 'Y' ? 'Y' : $arParams['WRAP_SECTION_BLOCK'];
$arParams['SHOW_TOP_LINE'] = isset($arParams['SHOW_TOP_LINE']) ? $arParams['SHOW_TOP_LINE'] : 'N';
$arParams['SHOW_BOTTOM_LINE'] = isset($arParams['SHOW_BOTTOM_LINE']) ? $arParams['SHOW_BOTTOM_LINE'] : 'N';

if ($arParams['WRAP_CONTAINER_BLOCK'] === 'Y') {
    echo '<div class="b-container">';
}
if ($arParams['SHOW_TOP_LINE'] === 'Y') {
    echo '<div class="b-line b-line--pet"></div>';
}
if ($arParams['WRAP_SECTION_BLOCK'] === 'Y') {
    echo '<section class="b-common-section" data-url="/ajax/catalog/product-info/">';
}

?><div class="b-common-section__title-box b-common-section__title-box--viewed">
    <h2 class="b-title b-title--viewed"><?php
        echo \Bitrix\Main\Localization\Loc::getMessage('CVP_TPL_MESS_YOU_LOOKED');
    ?></h2>
</div>
<div class="b-common-section__content b-common-section__content--viewed js-scroll-viewed"><?php
    foreach ($arResult['PRINT_ITEMS'] as $item) {
        ?><div class="b-viewed-product">
            <a class="b-viewed-product__link" href="<?=$item['DETAIL_PAGE_URL']?>" title="">
                <span class="b-viewed-product__image-wrap"><?php
                    if ($item['IMG']) {
                        ?><img class="b-viewed-product__image" src="<?=$item['IMG']['SRC']?>" alt="<?=$item['IMG']['ALT']?>" title="<?=$item['IMG']['TITLE']?>"><?php
                    }
                ?></span>
                <span class="b-viewed-product__description-wrap">
                    <span class="b-viewed-product__label"><?=$item['BRAND_NAME']?></span>
                    <span class="b-viewed-product__description"><?=$item['NAME']?></span>
                </span>
            </a>
        </div><?php
    }
?></div><?php

if ($arParams['WRAP_SECTION_BLOCK'] === 'Y') {
    echo '</section>';
}
if ($arParams['SHOW_BOTTOM_LINE'] === 'Y') {
    echo '<div class="b-line b-line--viewed"></div>';
}
if ($arParams['WRAP_CONTAINER_BLOCK'] === 'Y') {
    echo '</div>';
}
