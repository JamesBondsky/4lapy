<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * Список элементов в разделах: Акции, Новости, Статьи
 *
 * @updated: 01.01.2018
 */

$this->setFrameMode(true);
if (empty($arResult['ITEMS'])) {
    return;
}

?><div class="b-info-blocks"><?php
    foreach ($arResult['ITEMS'] as $arItem) {
        $this->AddEditAction(
            $arItem['ID'],
            $arItem['EDIT_LINK'],
            \CIBlock::GetArrayByID($arItem['IBLOCK_ID'], 'ELEMENT_EDIT')
        );
        $this->AddDeleteAction(
            $arItem['ID'],
            $arItem['DELETE_LINK'],
            \CIBlock::GetArrayByID($arItem['IBLOCK_ID'], 'ELEMENT_DELETE'),
            [
                'CONFIRM' => \Bitrix\Main\Localization\Loc::getMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')
            ]
        );

        ?><article class="b-info-blocks__item"><a class="b-info-blocks__item-link" href="<?=$arItem['DETAIL_PAGE_URL']?>" id="<?=$this->GetEditAreaId($arItem['ID'])?>">
            <div class="b-info-blocks__item-img"><?php
                if (!empty($arItem['PRINT_PICTURE']['SRC'])) {
                    ?><img src="<?=$arItem['PRINT_PICTURE']['SRC']?>" alt="<?=$arItem['PRINT_PICTURE']['ALT']?>" title="<?=$arItem['PRINT_PICTURE']['TITLE']?>"><?php
                }
            ?></div><?php

            if ($arItem['PRINT_PUBLICATION_TYPES']) {
                foreach ($arItem['PRINT_PUBLICATION_TYPES'] as $sVal) {
                    ?><div class="b-info-blocks__item-snippet"><?=$sVal?></div><?php
                }
            }

            ?><h2 class="b-info-blocks__item-title"><?=$arItem['NAME']?></h2>
            <div class="b-info-blocks__item-description"><?php
                if (isset($arParams['DISPLAY_PREVIEW_TEXT']) && $arParams['DISPLAY_PREVIEW_TEXT'] === 'Y') {
                    echo htmlspecialcharsback($arItem['PREVIEW_TEXT']);
                }
            ?></div><?php
            if (!empty($arItem['DISPLAY_ACTIVE_FROM']) || !empty($arItem['DISPLAY_ACTIVE_TO'])) {
                $dateStr = '';
                if (!empty($arItem['DISPLAY_ACTIVE_FROM'])){
                    $dateStr = 'c ' . $arItem['DISPLAY_ACTIVE_FROM'];
                }
                if (!empty($arItem['DISPLAY_ACTIVE_TO'])){
                    $dateStr .= ' по ' . $arItem['DISPLAY_ACTIVE_TO'];
                }
                ?>
                <time datetime="<?=trim(ToLower($dateStr))?>" class="b-info-blocks__item-date"><?=trim(ToLower($dateStr))?></time><?php
            }
        ?></a></article><?php
    }
?></div><?php

if ($arParams['DISPLAY_BOTTOM_PAGER']) {
    echo $arResult['NAV_STRING'];
}
