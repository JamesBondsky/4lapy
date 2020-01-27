<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * Элемент детально в разделах: Акции, Новости, Статьи
 *
 * @updated: 01.01.2018
 */

/**
 * @var \CBitrixComponentTemplate $this
 *
 * @var array                     $arResult
 * @var array                     $templateData
 */
$this->setFrameMode(true);
if (!$arResult['ERROR']) {
    ?>
    <div class="b-container">
    <?php
    if (!empty($arResult['BANNER_DESKTOP'])) : ?>
        <div class="b-brand-banner">
            <img class="b-brand-banner__background b-brand-banner__background--desktop"
                 src="<?= $arResult['BANNER_DESKTOP']['RESIZED_IMAGE'] ?>" alt="">
            <img class="b-brand-banner__background b-brand-banner__background--tablet"
                 src="<?= $arResult['BANNER_TABLET']['RESIZED_IMAGE'] ?>" alt="">
            <img class="b-brand-banner__background b-brand-banner__background--mobile"
                 src="<?= $arResult['BANNER_MOBILE']['RESIZED_IMAGE'] ?>" alt="">
        </div>
    <?php endif; ?>
    <div class="b-detail-page b-detail-page--bordered"><?php
        if (!empty($arResult['DETAIL_PICTURE']) && is_array($arResult['DETAIL_PICTURE']) && empty($arResult['BANNER_DESKTOP'])) {
            ?><img src="<?= $arResult['DETAIL_PICTURE']['SRC'] ?>" alt=""><?php
        }
        echo htmlspecialcharsback($arResult['DETAIL_TEXT']);
        if (!$arResult['NO_SHOW_VIDEO'] && !empty($arResult['DISPLAY_PROPERTIES']['VIDEO']['DISPLAY_VALUE'])) {
            echo $arResult['DISPLAY_PROPERTIES']['VIDEO']['DISPLAY_VALUE'];
        }
        if (!$arResult['NO_SHOW_SLIDER'] && !empty($arResult['DISPLAY_PROPERTIES']['MORE_PHOTO']['DISPLAY_VALUE'])) {
            if (\is_array($arResult['DISPLAY_PROPERTIES']['MORE_PHOTO']['DISPLAY_VALUE'])) {
                ?>
                <div class="b-detail-page-slider js-detail-slider"><?php
                /** @noinspection ForeachSourceInspection - условие есть выше */
                foreach ($arResult['DISPLAY_PROPERTIES']['MORE_PHOTO']['DISPLAY_VALUE'] as $photo) {
                    if (is_array($photo)) {
                        ?>
                        <div class="b-detail-page-slider__item">
                        <img src="<?= $photo['SRC'] ?>" alt="">
                        </div><?php
                    }
                }
                ?></div><?php
            }
        }
        ?></div>
    </div><?php
} else {
?>

<main class="b-wrapper" role="main">
    <div class="b-container b-container--error">
        <div class="b-error-page">
            <?php /* @todo image resize helper */ ?>
            <img src="/static/build/images/content/404.png">
            <p class="b-title b-title--h1">Такой страницы нет</p>
            <p>Проверьте правильность адреса, воспользуйтесь поиском или начните с главной страницы</p>
            <a href="/">Перейти на главную страницу</a>
        </div>
    </div>
</main>
<?
}
