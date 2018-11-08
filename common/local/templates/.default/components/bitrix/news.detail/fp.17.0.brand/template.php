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
    if($value){
        switch($key){
            case 'SLIDER_IMAGES':
                ?>
                <div class="b-brand-banner">
                    <a href="#" class="b-brand-banner__link">
                        <img class="b-brand-banner__background b-brand-banner__background--desktop" src="/upload/static-brands/desktop-banner-brand-static.png" alt="">
                        <img class="b-brand-banner__background b-brand-banner__background--tablet" src="/upload/static-brands/tablet-banner-brand-static.png" alt="">
                        <img class="b-brand-banner__background b-brand-banner__background--mobile" src="/upload/static-brands/mobile-banner-brand-static.png" alt="">
                    </a>
                </div>
                <?/*<pre><?print_r($arResult['SLIDER_IMAGE']);?></pre>*/?>

                <? break;
            case 'VIDEO': ?>
                <div class="b-brand-video">
                    <div class="b-brand-video__info">
                        <div class="b-brand-video__title">
                            Название видео
                        </div>
                        <div class="b-brand-video__descr">
                            Рацион Хиллс был разработан ветеринарным врачом, и&nbsp;<nobr>ре-путация</nobr> этого бренда кормов для ветеринарных врачей неоспорима. Ветеринарные специалисты доверяют нам, поскольку знают, что все наши корма разрабатываются только профессионалами.

                            <?/*= $arResult['VIDEO']['description']['TEXT'] */?>
                        </div>
                    </div>
                    <div class="b-brand-video__right-col">
                        <div class="b-brand-video__logo-wrap">
                            <img class="b-brand-video__logo" src="<?= $arResult['PRINT_PICTURE']['SRC'] ?>" alt="<?= $arResult['NAME'] ?>">
                        </div>                            
                        <div class="b-brand-video__video">
                            <video data-brand-video="true" width="100%" height="100%" poster="/upload/static-brands/preview.jpg" controls="controls" preload="none" muted>
                                <!-- MP4 for Safari, IE9, iPhone, iPad, Android, and Windows Phone 7 -->
                                <source type="video/mp4" src="/upload/static-brands/grandin_lamb_video.mp4" />
                                <!-- WebM/VP8 for Firefox4, Opera, and Chrome -->
                                <source type="video/webm" src="/upload/static-brands/grandin_lamb_video.webm" />
                                <!-- Ogg/Vorbis for older Firefox and Opera versions -->
                                <source type="video/ogg" src="/upload/static-brands/grandin_lamb_video.ogv" />
                            </video>
                            <?/*<div class="b-brand-video__video-youtube">
                                <iframe src="https://www.youtube.com/embed/FNg4Sol7AaA" frameborder="0" allowfullscreen></iframe>
                            </div>*/?>
                        </div>
                    </div>
                </div>

                <?/*<pre><?print_r($arResult['VIDEO']);?></pre>*/?>

                <? break;
            case 'SECTIONS':?>
                <div class="b-brand-products js-brand-products-slider">
                    <?php  
                        foreach ($arResult['SECTIONS'] as $item) { ?>
                            <div class="b-brand-products__item">
                                <a href="<?= $item['link'] ?>" class="b-brand-products__link">
                                    <div class="b-brand-products__img">
                                        <img src="<?= $item[picture] ?>">
                                    </div>
                                    <div class="b-brand-products__title b-clipped-text">
                                        <?= $item['title'] ?>
                                    </div>
                                </a>
                            </div>
                        
                        <? }
                    ?>
                </div>
                <?/*<pre><?print_r($arResult['SECTIONS']);?></pre>*/?>
                <? break;
        }
    }
 } ?>