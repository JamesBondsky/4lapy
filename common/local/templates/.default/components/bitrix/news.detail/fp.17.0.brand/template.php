<? use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * Карточка бренда
 *
 * @updated: 22.12.2017
 */
$this->setFrameMode(true);
?>
<nav class="b-breadcrumbs">
    <ul class="b-breadcrumbs__list">
        <li class="b-breadcrumbs__item">
            <a class="b-breadcrumbs__link"
               href="/brands/"
               title="<?= Loc::getMessage('BRAND_DETAIL.ALL_LINK_TITLE') ?>"><?php
                echo Loc::getMessage('BRAND_DETAIL.ALL_LINK');
                ?></a>
        </li>
    </ul>
</nav>
<h1 class="b-title b-title--h1 b-title--one-brand"><?= Loc::getMessage(
        'BRAND_DETAIL.TITLE',
        ['#NAME#' => $arResult['NAME']]
    ) ?></h1><?php

if ($arResult['DETAIL_TEXT'] || $arResult['PRINT_PICTURE']) { ?>
    <div class="b-brand-info">
        <?php if ($arResult['PRINT_PICTURE']) {
            ?>
            <div class="b-brand-info__image-wrapper">
                <img class="b-brand-info__image js-image-wrapper"
                     src="<?= $arResult['PRINT_PICTURE']['SRC'] ?>"
                     alt="<?= $arResult['NAME'] ?>">
            </div>
        <?php }
        if ($arResult['DETAIL_TEXT']) {
            ?>
            <div class="b-brand-info__info-wrapper">
                <?php
                echo $arResult['DETAIL_TEXT'];
                ?>
            </div>
            <?php
        } ?>
    </div>
<?php } ?>