<?php

use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * Карточка бренда
 *
 * @updated: 22.12.2017
 */
$this->setFrameMode(true);
//TODO Заменить на использование стандартной цепочки навигации и убрать этот дублирующий фрагмент кода
?>
    <nav class="b-breadcrumbs">
        <ul class="b-breadcrumbs__list" itemscope itemtype="http://schema.org/BreadcrumbList">
            <li class="b-breadcrumbs__item"
                itemprop="itemListElement"
                itemscope
                itemtype="http://schema.org/ListItem">
                <a class="b-breadcrumbs__link"
                   href="/brand/"
                   title="<?= Loc::getMessage('BRAND_DETAIL.ALL_LINK_TITLE') ?>"
                   itemtype="http://schema.org/Thing"
                   itemprop="item"><span itemprop="name"><?= Loc::getMessage('BRAND_DETAIL.ALL_LINK'); ?></span></a>
                <meta itemprop="position" content="1"/>
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

<? foreach ($arResult['SHOW_BLOCKS'] as $key => $value) {
    if ($value) {
        switch ($key) {
            case 'BANNER_IMAGES_DESKTOP':
                ?>

                <div class="b-brand-banner">
                    <?if (!empty($arResult['BANNER']['LINK'])) {?>
                    <a href="<?= $arResult['BANNER']['LINK'] ?>" class="b-brand-banner__link">
                        <? } ?>
                        <img class="b-brand-banner__background b-brand-banner__background--desktop"
                             src="<?= $arResult['BANNER']['IMAGES']['BANNER_IMAGES_DESKTOP'] ?>" alt="">
                        <img class="b-brand-banner__background b-brand-banner__background--tablet"
                             src="<?= $arResult['BANNER']['IMAGES']['BANNER_IMAGES_NOTEBOOK'] ?>" alt="">
                        <img class="b-brand-banner__background b-brand-banner__background--mobile"
                             src="<?= $arResult['BANNER']['IMAGES']['BANNER_IMAGES_MOBILE'] ?>" alt="">
                        <?if (!empty($arResult['BANNER']['LINK'])) {?>
                    </a>
                <? } ?>
                </div>
                <? break;
            case 'VIDEO_MP4': ?>
                <div class="b-brand-video">
                    <div class="b-brand-video__info">
                        <?if (!empty($arResult['VIDEO']['TITLE'])) { ?>
                            <div class="b-brand-video__title"><?=$arResult['VIDEO']['TITLE']?></div>
                        <? } ?>
                        <?if (!empty($arResult['VIDEO']['DESCRIPTION'])) { ?>
                            <div class="b-brand-video__descr"><?=$arResult['VIDEO']['DESCRIPTION']?></div>
                        <? } ?>
                    </div>
                    <div class="b-brand-video__video-wrap">
                        <div class="b-brand-video__video">
                            <video data-brand-video="true" width="100%" height="100%" <?if (!empty($arResult['VIDEO']['PREVIEW_PICTURE'])) {?>poster="<?=$arResult['VIDEO']['PREVIEW_PICTURE']?>"<? } ?> controls="controls" preload="none" muted>
                                <source type="video/mp4" src="<?=$arResult['VIDEO']['VIDEOS']['VIDEO_MP4']?>"/>
                                <source type="video/webm" src="<?=$arResult['VIDEO']['VIDEOS']['VIDEO_WEBM']?>"/>
                                <source type="video/ogg" src="<?=$arResult['VIDEO']['VIDEOS']['VIDEO_OGG']?>"/>
                            </video>
                        </div>
                    </div>
                </div>
                <? break;
            case 'PRODUCT_CATEGORIES':
                ?>
                <div class="b-brand-products">
                    <div class="b-brand-products__list js-brand-products-slider">
                        <?php
                        foreach ($arResult['PRODUCT_CATEGORIES'] as $arItem) { ?>
                            <? if (!empty($arItem['filters']) && !empty($arItem['image']) && !empty($arItem['title']) && !empty($arItem['subtitle'])) { ?>
                                <div class="b-brand-products__item">
                                    <div data-brand-product-item="<?= $arItem['filters'] ?>" class="b-brand-products__link js-brand-product-item">
                                        <div class="b-brand-products__img">
                                            <img src="<?= $arItem['image'] ?>" alt="<?= $arItem['alt'] ?>">
                                        </div>
                                        <div class="b-brand-products__title">
                                            <div class="b-brand-products__title-product b-clipped-text">
                                                <?= $arItem['title'] ?>
                                            </div>
                                            <div class="b-brand-products__title-type">
                                                <?= $arItem['subtitle'] ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <? } ?>
                        <? } ?>
                    </div>
                </div>
                <? break;
        }
    }
} ?>