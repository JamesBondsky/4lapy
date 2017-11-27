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
        <h1 class="b-title b-title--h1"><?php $APPLICATION->ShowTitle() ?></h1>
        <div class="b-detail-page__date"><?= $arResult['DISPLAY_ACTIVE_FROM'] ?></div>
    </div>
</div>
<div class="b-container">
    <div class="b-detail-page b-detail-page--bordered">
        <?php if (is_array($arResult['DETAIL_PICTURE'])){ ?>
            <img src="<?= $arResult['DETAIL_PICTURE']['SRC'] ?>"
            />
        <?php } ?>
        <?= $arResult['DETAIL_TEXT'] ?>
        <?/*php if (is_array($arResult['DISPLAY_PROPERTIES']['MORE_PHOTO']) && !empty($arResult['DISPLAY_PROPERTIES']['MORE_PHOTO'])) { ?>
            <div class="b-detail-page-slider js-detail-slider">
                <?php foreach ($arResult['DISPLAY_PROPERTIES']['MORE_PHOTO'] as $photo) {
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
        <?php }*/ ?>
    </div>
</div>