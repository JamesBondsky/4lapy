<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * Список акций в разделе Акции
 *
 * @updated: 29.12.2017
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

        ?><a class="b-info-blocks__item" href="<?=$arItem['DETAIL_PAGE_URL']?>" id="<?=$this->GetEditAreaId($arItem['ID'])?>">
            <div class="b-info-blocks__item-img"><?php
                if (!empty($arItem['PRINT_PICTURE']['SRC'])) {
                    ?><img src="<?=$arItem['PRINT_PICTURE']['SRC']?>" alt="<?=$arItem['NAME']?>" title="<?=$arItem['NAME']?>"><?php
                }
            ?></div>
            <div class="b-info-blocks__item-snippet"><?=\Bitrix\Main\Localization\Loc::getMessage('SHARES_LIST.SNIPPET_TEXT')?></div>
            <div class="b-info-blocks__item-title"><?=$arItem['NAME']?></div>
            <div class="b-info-blocks__item-description"><?php
                if (isset($arParams['DISPLAY_PREVIEW_TEXT']) && $arParams['DISPLAY_PREVIEW_TEXT'] === 'Y') {
                    echo $arItem['PREVIEW_TEXT'];
                }
            ?></div><?php
            if (!empty($arItem['DISPLAY_ACTIVE_FROM'])) {
                ?><div class="b-info-blocks__item-date"><?=ToLower($arItem['DISPLAY_ACTIVE_FROM'])?></div><?php
            }
        ?></a><?php
    }
?></div><?php

if ($arParams['DISPLAY_BOTTOM_PAGER']) {
    echo $arResult['NAV_STRING'];
}
