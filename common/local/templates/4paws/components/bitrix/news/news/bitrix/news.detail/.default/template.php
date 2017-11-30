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
<div class="b-container b-container--news-detail">
    <div class="b-detail-page">
        <?php $APPLICATION->IncludeComponent('bitrix:breadcrumb',
                                             'breadcrumb',
                                             [
                                                 'PATH'       => '',
                                                 'SITE_ID'    => SITE_ID,
                                                 'START_FROM' => '0',
                                             ]); ?>
        <h1 class="b-title b-title--h1">
            <?= (!empty($arResult['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE'])) ? $arResult['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE'] : $arResult['NAME'] ?>
        </h1>
        <?php if (!empty($arResult['DISPLAY_ACTIVE_FROM'])) { ?>
            <div class="b-detail-page__date"><?= $arResult['DISPLAY_ACTIVE_FROM'] ?></div>
        <?php } ?>
    </div>
</div>
<div class="b-container">
    <div class="b-detail-page b-detail-page--bordered">
        <?php if (is_array($arResult['DETAIL_PICTURE'])) { ?>
            <img src="<?= $arResult['DETAIL_PICTURE']['SRC'] ?>" />
        <?php } ?>
        <?= $arResult['DETAIL_TEXT'] ?>
        <?php if (!$arResult['NO_SHOW_VIDEO'] && !empty($arResult['DISPLAY_PROPERTIES']['VIDEO']['DISPLAY_VALUE'])) {
            echo $arResult['DISPLAY_PROPERTIES']['VIDEO']['DISPLAY_VALUE'];
        } ?>
        <?php if (!$arResult['NO_SHOW_SLIDER']
                  && is_array($arResult['DISPLAY_PROPERTIES']['MORE_PHOTO']['DISPLAY_VALUE'])
                  && !empty($arResult['DISPLAY_PROPERTIES']['MORE_PHOTO']['DISPLAY_VALUE'])) { ?>
            <div class="b-detail-page-slider js-detail-slider">
                <?php foreach ($arResult['DISPLAY_PROPERTIES']['MORE_PHOTO']['DISPLAY_VALUE'] as $photo) {
                    if (is_numeric($photo)) {
                        $photo = ['SRC' => \CFile::GetPath($photo)];
                    }
                    if (!empty($photo['SRC'])) {
                        ?>
                        <div class="b-detail-page-slider__item">
                            <img src="<?= $photo['SRC'] ?>" />
                        </div>
                    <?php }
                } ?>
            </div>
        <?php } ?>
    </div>
</div>