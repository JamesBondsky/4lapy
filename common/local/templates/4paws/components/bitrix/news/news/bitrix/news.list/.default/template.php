<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
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

use Bitrix\Main\Application;
use FourPaws\BitrixOrm\Model\CropImageDecorator;

$this->setFrameMode(true);
?>
<?php if (!is_array($arResult['ITEMS']) || empty($arResult['ITEMS'])) {
    return;
} ?>
<!--suppress Annotator -->
<div class="b-container b-container--news">
    <div class="b-news">
        <h1 class="b-title b-title--h1"><?php $APPLICATION->ShowTitle() ?></h1>
        <div class="b-info-blocks">
            <?php foreach ($arResult['ITEMS'] as $item) {
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
                <a class="b-info-blocks__item"
                   href="<?= $item['DETAIL_PAGE_URL'] ?>"
                   id="<?= $this->GetEditAreaId($item['ID']); ?>">
                    <div class="b-info-blocks__item-img">
                        <?php if (!empty($item['PREVIEW_PICTURE']['SRC'])
                                  && file_exists(Application::getDocumentRoot() . $item['PREVIEW_PICTURE']['SRC'])) {
                            ?>
                            <?php $image = new CropImageDecorator($item['PREVIEW_PICTURE']);
                            $image->setCropWidth(305)->setCropHeight(120); ?>
                            <img src="<?= $image ?>"
                                 alt="<?= $item['PREVIEW_PICTURE']['ALT'] ?>"
                                 title="<?= $item['PREVIEW_PICTURE']['TITLE'] ?>">
                        <?php } ?>
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
                    <?php if (!empty($item['DESCRIPTION'])) { ?>
                        <div class="b-info-blocks__item-description"><?= $item['DESCRIPTION'] ?></div>
                    <?php } ?>
                    <?php if (!empty($item['DISPLAY_ACTIVE_FROM'])) { ?>
                        <div class="b-info-blocks__item-date"><?= $item['DISPLAY_ACTIVE_FROM'] ?></div>
                    <?php } ?>
                </a>
            <?php } ?>
        </div>
        <?php if ($arParams['DISPLAY_BOTTOM_PAGER']) { ?>
            <?= $arResult['NAV_STRING'] ?>
        <?php } ?>
    </div>
</div>