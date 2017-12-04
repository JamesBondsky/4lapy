<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);

if (!$arResult['NavShowAlways']) {
    if ($arResult['NavRecordCount'] === 0 || ($arResult['NavPageCount'] === 1 && $arResult['NavShowAll'] === false)) {
        return;
    }
}

use Bitrix\Main\Web\Uri;

/**
 * на основе visual
 */
?>

<div class="b-pagination">
    <ul class="b-pagination__list">
        <li class="b-pagination__item b-pagination__item--prev<?= ($arResult['NavPageNomer']
                                                                   > 1) ? '' : 'b-pagination__item--disabled' ?>">
            <?php if ($arResult['NavPageNomer'] > 1) {?>
                <a class="b-pagination__link" href="<?= $arResult['PREV_URL'] ?>">Назад</a>
            <?php } else { ?>
                <span class="b-pagination__link">Назад</span>
            <?php } ?>
        </li>
        
        
        <?php $NavRecordGroup = 1;
        while ($NavRecordGroup <= $arResult['NavPageCount']) {
            $strTitle    = GetMessage('nav_page_num_title',
                                      ['#NUM#' => $NavRecordGroup]);
            $hiddenClass = ($i > 3 && $i <= 5) ? ' hidden' : '';
            $i           = ($i === 5) ? 0 : $i;
            if ($NavRecordGroup === $arResult['NavPageNomer']) {
                ?>
                <li class="b-pagination__item">
                <a class="b-pagination__link active"
                   href="javascript:void(0);"
                   title="<?= $strTitle ?>"><?= $NavRecordGroup ?></a>
                </li><?php
            } elseif ($NavRecordGroup === 1 && $arResult['bSavePage'] === false) {
                ?>
            <li class="b-pagination__item<?= $hiddenClass ?>">
                <a class="b-pagination__link"
                   href="<?= $arResult['BASE_URI'] ?>"
                   title="<?= $strTitle ?>"><?= $NavRecordGroup ?></a>
                </li><?php
            } else {
                ?>
                <li class="b-pagination__item<?= $hiddenClass ?>">
                    <?$uri = new Uri($arResult['BASE_URI']);
                    $uri->addParams(['PAGEN_' . $arResult['NavNum'] => $NavRecordGroup]); ?>
                    <a class="b-pagination__link"
                       href="<?= $arResult['URLS'][$NavRecordGroup] ?>"
                       title="<?= $strTitle ?>"><?= $NavRecordGroup ?></a>
                </li>><?php
            }
            if ($NavRecordGroup === 2 && $arResult['nStartPage'] > 3
                && $arResult['nStartPage'] - $NavRecordGroup > 1) {
                ?>
                <li class="b-pagination__item">
                    <span class="b-pagination__dot">&hellip;</span>
                </li><?php
                $NavRecordGroup = $arResult['nStartPage'];
            } elseif ($NavRecordGroup === $arResult['nEndPage']
                      && $arResult['nEndPage'] < ($arResult['NavPageCount'] - 2)) {
                ?>
                <li class="b-pagination__item">
                    <span class="b-pagination__dot">&hellip;</span>
                </li><?php
                $NavRecordGroup = $arResult['NavPageCount'] - 1;
            } else {
                $NavRecordGroup++;
            }
        } ?>
        
        <li class="b-pagination__item b-pagination__item--next<?= ($arResult['NavPageNomer']
                                                                   < $arResult['NavPageCount']) ? '' : 'b-pagination__item--disabled' ?>">
            <?php if ($arResult['NavPageNomer'] < $arResult['NavPageCount']) {?>
                <a class="b-pagination__link" href="<?= $arResult['NEXT_URL'] ?>">
                    Вперед
                </a>
            <?php } else { ?>
                <span class="b-pagination__link">Вперед</span>
            <?php } ?>
        </li>
    </ul>
</div>
