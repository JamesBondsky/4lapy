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

?><div class="b-container">
    <div class="b-detail-page b-detail-page--bordered"><?php
        if (!empty($arResult['DETAIL_PICTURE']) && is_array($arResult['DETAIL_PICTURE'])) {
            ?><img src="<?= $arResult['DETAIL_PICTURE']['SRC']?>" alt=""><?php
        }
        echo htmlspecialcharsback($arResult['DETAIL_TEXT']);
        if (!$arResult['NO_SHOW_VIDEO'] && !empty($arResult['DISPLAY_PROPERTIES']['VIDEO']['DISPLAY_VALUE'])) {
            echo $arResult['DISPLAY_PROPERTIES']['VIDEO']['DISPLAY_VALUE'];
        }
        if (!$arResult['NO_SHOW_SLIDER'] && !empty($arResult['DISPLAY_PROPERTIES']['MORE_PHOTO']['DISPLAY_VALUE'])) {
            if (\is_array($arResult['DISPLAY_PROPERTIES']['MORE_PHOTO']['DISPLAY_VALUE'])) {
                ?><div class="b-detail-page-slider js-detail-slider"><?php
                    /** @noinspection ForeachSourceInspection - условие есть выше */
                    foreach ($arResult['DISPLAY_PROPERTIES']['MORE_PHOTO']['DISPLAY_VALUE'] as $photo) {
                        if (is_array($photo)) {
                            ?><div class="b-detail-page-slider__item">
                                <img src="<?= $photo['SRC'] ?>" alt="">
                            </div><?php
                        }
                    }
                ?></div><?php
            }
        }
    ?></div>
</div><?php
