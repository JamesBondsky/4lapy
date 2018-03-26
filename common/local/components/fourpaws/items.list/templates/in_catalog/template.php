<?php
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

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

if (!\is_array($arResult['IBLOCKS']) || empty($arResult['IBLOCKS'])) {
    return;
}

if (!\is_array($arResult['ITEMS']) || empty($arResult['ITEMS'])) {
    return;
}

$frame = $this->createFrame(); ?>
<div class="b-container b-container--news fleas-protection-block">
    <div class="b-news">
        <h1 class="b-title b-title--h1">Полезная информация</h1>
        <div class="b-info-blocks">
            <?php foreach ($arResult['ITEMS'] as $key => $item) {
                $this->AddEditAction(
                    $item['ID'],
                    $item['EDIT_LINK'],
                    CIBlock::GetArrayByID($item['IBLOCK_ID'], 'ELEMENT_EDIT')
                );
                $this->AddDeleteAction(
                    $item['ID'],
                    $item['DELETE_LINK'],
                    CIBlock::GetArrayByID($item['IBLOCK_ID'], 'ELEMENT_DELETE'),
                    ['CONFIRM' => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')]
                ); ?>
                <a class="b-info-blocks__item" href="<?= $item['DETAIL_PAGE_URL'] ?>"
                   id="<?= $this->GetEditAreaId($item['ID']); ?>">
                    <div class="b-info-blocks__item-img">
                        <img src="<?= $item['PREVIEW_PICTURE']['SRC'] ?>" alt="<?= $item['PREVIEW_PICTURE']['ALT'] ?>">
                    </div>
                    <?php if (is_array($item['DISPLAY_PROPERTIES']['PUBLICATION_TYPE']['DISPLAY_VALUE'])
                        && !empty($item['DISPLAY_PROPERTIES']['PUBLICATION_TYPE']['DISPLAY_VALUE'])) {
                        foreach ($item['DISPLAY_PROPERTIES']['PUBLICATION_TYPE']['DISPLAY_VALUE'] as $val) {
                            ?>
                            <div class="b-info-blocks__item-snippet"><?= $val ?></div>
                            <?php
                        }
                    } elseif (!empty($item['DISPLAY_PROPERTIES']['PUBLICATION_TYPE']['DISPLAY_VALUE'])) {
                        ?>
                        <div class="b-info-blocks__item-snippet"><?= $item['DISPLAY_PROPERTIES']['PUBLICATION_TYPE']['DISPLAY_VALUE'] ?></div>
                    <?php } ?>
                    <div class="b-info-blocks__item-title"><?= $item['NAME'] ?></div>
                    <?php if (!empty($item['PREVIEW_TEXT'])) { ?>
                        <div class="b-info-blocks__item-description">
                            <?= htmlspecialcharsback(
                                $item['PREVIEW_TEXT']
                            ) ?>
                        </div>
                    <?php } ?>
                    <?php if (!empty($item['DISPLAY_ACTIVE_FROM'])) { ?>
                        <div class="b-info-blocks__item-date"><?= $item['DISPLAY_ACTIVE_FROM'] ?></div>
                    <?php } ?>
                </a>
            <?php } ?>
        </div>
    </div>
</div>