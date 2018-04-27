<?php
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
 *
 * @global CUser                  $USER
 * @global CMain                  $APPLICATION
 * @global CDatabase              $DB
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

if (!is_array($arResult['IBLOCKS']) || empty($arResult['IBLOCKS'])) {
    return;
}

use FourPaws\Decorators\SvgDecorator;

$frame = $this->createFrame(); ?>
<section class="b-common-section" data-url="/ajax/catalog/product-info/">
    <div class="b-common-section__title-box b-common-section__title-box--latest-event b-common-section__title-box--wrap">
        <h2 class="b-title b-title--latest-event b-title--wrap">Последние события</h2>
        <div class="b-common-section__link-block">
            <?php foreach ($arResult['IBLOCKS'] as $iblock) { ?>
                <a class="b-link b-link--more"
                   href="<?= $iblock['LIST_PAGE_URL_FORMATED'] ?>"
                   title="Новости"><?= $iblock['NAME'] ?></a>
            <?php } ?>
        </div>
    </div>
    <?php /** @noinspection PhpUnhandledExceptionInspection */
    $frame->begin(''); ?>
    <?php if (is_array($arResult['ITEMS']) && !empty($arResult['ITEMS'])) { ?>
        <div class="b-common-section__content b-common-section__content--latest-event b-common-section__content--wrap">
            <div class="b-news-wrapper">
                <?php foreach ($arResult['ITEMS'] as $key => $item) {
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
                    <article class="b-news-item<?= ($key === 0) ? ' b-news-item--big' : '' ?>"
                             id="<?= $this->GetEditAreaId($item['ID']); ?>">
                        <?php if (!empty($item['DETAIL_PAGE_URL'])){ ?>
                        <a class="b-news-item__link"
                           href="<?= $item['DETAIL_PAGE_URL'] ?>"
                           title="<?= $item['NAME'] ?>">
                            <?php } ?>
                            <?php if (!empty($item['PREVIEW_PICTURE']['SRC'])) { ?>
                                <span class="b-news-item__image-wrapper js-image-cover">
                                    <img class="b-news-item__image"
                                         src="<?= $item['PREVIEW_PICTURE']['SRC'] ?>"
                                         alt="<?= $item['PREVIEW_PICTURE']['ALT'] ?>"
                                         title="<?= $item['PREVIEW_PICTURE']['TITLE'] ?>" />
                                    <?php if (!empty($item['DISPLAY_PROPERTIES']['VIDEO']['DISPLAY_VALUE'])) { ?>
                                        <span class="b-news-item__video">
                                                <span class="b-icon">
                                                    <?= new SvgDecorator(
                                                        'icon-play-video', 60, 60
                                                    ); ?>
                                                </span>
                                            </span>
                                    <?php }
                                    ?>
                                </span>
                            <?php } ?>
                            <?php if (is_array($item['DISPLAY_PROPERTIES']['PUBLICATION_TYPE']['DISPLAY_VALUE'])
                                      && !empty($item['DISPLAY_PROPERTIES']['PUBLICATION_TYPE']['DISPLAY_VALUE'])) {
                                foreach ($item['DISPLAY_PROPERTIES']['PUBLICATION_TYPE']['DISPLAY_VALUE'] as $val) {
                                    ?>
                                    <span class="b-news-item__label"><?= $val ?></span>
                                    <?php
                                }
                            } elseif (!empty($item['DISPLAY_PROPERTIES']['PUBLICATION_TYPE']['DISPLAY_VALUE'])) {
                                ?>
                                <span class="b-news-item__label"><?= $item['DISPLAY_PROPERTIES']['PUBLICATION_TYPE']['DISPLAY_VALUE'] ?></span>
                            <?php } ?>
                            <h4 class="b-news-item__header"><?= $item['NAME'] ?></h4>
                            <?php if (!empty($item['PREVIEW_TEXT'])) { ?>
                                <p class="b-news-item__description"><?= htmlspecialcharsback(
                                        $item['PREVIEW_TEXT']
                                    ) ?></p>
                            <?php } ?>
                            <?php if (!empty($item['DISPLAY_ACTIVE_FROM'])) { ?>
                                <span class="b-news-item__date"><?= $item['DISPLAY_ACTIVE_FROM'] ?></span>
                            <?php } ?>
                            <?php if (!empty($item['DETAIL_PAGE_URL'])){ ?>
                        </a>
                    <?php } ?>
                    </article>
                <?php } ?>
            </div>
        </div>
    <?php } ?>
    <?php /** @noinspection PhpUnhandledExceptionInspection */
    $frame->end(); ?>
</section>
<div class="b-line b-line--news-main"></div>
