<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var array $arParams */
/** @var array $arResult */
/** @noinspection PhpUndefinedClassInspection */
/** @global CMain $APPLICATION */
/** @noinspection PhpUndefinedClassInspection */
/** @global CUser $USER */
/** @noinspection PhpUndefinedClassInspection */
/** @global CDatabase $DB */
/** @noinspection PhpUndefinedClassInspection */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @noinspection PhpUndefinedClassInspection */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);
?>
<!--suppress Annotator -->
<div class="b-container b-container--news">
    <div class="b-news">
        <h1 class="b-title b-title--h1"><?php $APPLICATION->ShowTitle() ?></h1>
        <?php if (is_array($arResult['ITEMS']) && !empty($arResult['ITEMS'])) { ?>
        <div class="b-info-blocks">
            <?php foreach ($arResult['ITEMS'] as $arItem) {
                /** @noinspection PhpUndefinedClassInspection */
                $this->AddEditAction($arItem['ID'],
                                     $arItem['EDIT_LINK'],
                                     CIBlock::GetArrayByID($arItem['IBLOCK_ID'], 'ELEMENT_EDIT'));
                /** @noinspection PhpUndefinedClassInspection */
                $this->AddDeleteAction($arItem['ID'],
                                       $arItem['DELETE_LINK'],
                                       CIBlock::GetArrayByID($arItem['IBLOCK_ID'], 'ELEMENT_DELETE'),
                                       ['CONFIRM' => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')]); ?>
                <a class="b-info-blocks__item"
                   href="<?= $arItem['DETAIL_PAGE_URL'] ?>"
                   id="<?= $this->GetEditAreaId($arItem['ID']); ?>">
                    <div class="b-info-blocks__item-img">
                        <?php if (!empty($arItem['PREVIEW_PICTURE']['SRC'])) { ?>
                            <img src="<?= $arItem['PREVIEW_PICTURE']['SRC'] ?>"
                                 alt="<?= $arItem['PREVIEW_PICTURE']['ALT'] ?>"
                                 title="<?= $arItem['PREVIEW_PICTURE']['TITLE'] ?>">
                        <?php } ?>
                    </div>
                    <?php if (is_array($arItem['DISPLAY_PROPERTIES']['PUBLICATION_TYPE']['DISPLAY_VALUE'])
                              && !empty($arItem['DISPLAY_PROPERTIES']['PUBLICATION_TYPE']['DISPLAY_VALUE'])) {
                        foreach ($arItem['DISPLAY_PROPERTIES']['PUBLICATION_TYPE']['DISPLAY_VALUE'] as $val) {
                            ?>
                            <div class="b-info-blocks__item-snippet"><?= $val ?></div>
                            <?php
                        }
                    } elseif(!empty($arItem['DISPLAY_PROPERTIES']['PUBLICATION_TYPE']['DISPLAY_VALUE'])) {
                        ?>
                        <div class="b-info-blocks__item-snippet"><?= $arItem['DISPLAY_PROPERTIES']['PUBLICATION_TYPE']['DISPLAY_VALUE'] ?></div>
                    <?php } ?>
                    <div class="b-info-blocks__item-title"><?= $arItem['NAME'] ?></div>
                    <div class="b-info-blocks__item-description"><?= $arItem['DESCRIPTION'] ?></div>
                    <div class="b-info-blocks__item-date"><?= $arItem['DISPLAY_ACTIVE_FROM'] ?></div>
                </a>
            <?php } ?>
        </div>
        <?php if ($arParams['DISPLAY_BOTTOM_PAGER']): ?>
            <br /><?= $arResult['NAV_STRING'] ?>
        <? endif; ?>
    </div>
    <?php } ?>
</div>