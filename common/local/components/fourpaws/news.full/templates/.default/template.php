<?php
/**
 * Created by PhpStorm.
 * User: Vampi
 * Date: 23.11.2017
 * Time: 16:09
 */


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
 * @var User                      $user
 *
 * @global CUser                  $USER
 * @global CMain                  $APPLICATION
 * @global CDatabase              $DB
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
if(is_array($arResult['IBLOCKS']) && !empty($arResult['IBLOCKS'])):?>
<section class="b-common-section">
    <div class="b-common-section__title-box b-common-section__title-box--latest-event b-common-section__title-box--wrap">
        <h2 class="b-title b-title--latest-event b-title--wrap"><?= Loc::getMessage('LAST_EVENTS_TEXT') ?></h2>
        <div class="b-common-section__link-block">
            <?foreach($arResult['IBLOCKS'] as $iblock):?>
                <a class="b-link b-link--more" href="<?=$iblock['LIST_PAGE_URL_FORMATED']?>" title="Новости"><?=$iblock['NAME']?></a>
            <?endforeach;?>
        </div>
    </div>
    <?php if (is_array($arResult['ITEMS']) && !empty($arResult['ITEMS'])): ?>
        <div class="b-common-section__content b-common-section__content--latest-event b-common-section__content--wrap">
            <div class="b-news-wrapper">
                <?php $i = 0;
                foreach ($arResult['ITEMS'] as $item):
                    $i++; ?>
                    <article class="b-news-item<?= ($i === 1) ? ' b-news-item--big' : '' ?>">
                        <a class="b-news-item__link"
                           href="<?= $item['DETAIL_PAGE_URL'] ?>"
                           title="Мастер-классы – встречи друзей!">
                            <span class="b-news-item__image-wrapper js-image-cover">
                                <?if(!empty($item['PREVIEW_PICTURE']['SRC'])):?>
                                    <img class="b-news-item__image"
                                         src="<?= $item['PREVIEW_PICTURE']['SRC'] ?>"
                                         alt="<?= $item['PREVIEW_PICTURE']['ALT'] ?>"
                                         title="<?= $item['PREVIEW_PICTURE']['TITLE'] ?>" />
                                    <?if(!empty($item['DISPLAY_PROPERTIES']['VIDEO']['DISPLAY_VALUE'])):?>
                                        <span class="b-news-item__video">
                                            <span class="b-icon">
                                                <svg class="b-icon__svg" viewBox="0 0 60 60 " width="60px" height="60px">
                                                    <use class="b-icon__use" xlink:href="/static/build/icons.svg#icon-play-video"></use>
                                                </svg>
                                            </span>
                                        </span>
                                    <?endif;?>
                                <?endif;?>
                            </span>
                            <?php if (!empty($item['DISPLAY_PROPERTIES']['PUBLICATION_TYPE']['DISPLAY_VALUE'])): ?>
                                <span class="b-news-item__label"><?=$item['DISPLAY_PROPERTIES']['PUBLICATION_TYPE']['DISPLAY_VALUE']?></span>
                            <? endif; ?>
                            <h4 class="b-news-item__header"><?= $item['NAME'] ?></h4>
                            <p class="b-news-item__description"><?= $item['PREVIEW_TEXT'] ?></p>
                            <span class="b-news-item__date"><?= $item['DISPLAY_ACTIVE_FROM'] ?></span>
                        </a>
                    </article>
                <? endforeach; ?>
            </div>
        </div>
    <? endif; ?>
</section>
<div class="b-line b-line--news-main"></div>
<?endif;?>