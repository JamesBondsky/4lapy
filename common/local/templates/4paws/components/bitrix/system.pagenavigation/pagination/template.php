<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * @var \CBitrixComponentTemplate $this
 *
 * @var array $arResult
 */
$this->setFrameMode(true);

if (!(bool)$arResult['NavShowAlways']) {
    if ((int)$arResult['NavRecordCount'] === 0
        || ((int)$arResult['NavPageCount'] === 1
            && (bool)$arResult['NavShowAll'] === false)) {
        return;
    }
}

/**
 * на основе visual
 */
?>

<div class="b-pagination">
    <ul class="b-pagination__list">
        <li class="b-pagination__item b-pagination__item--prev <?= ((int)$arResult['NavPageNomer']
            > 1) ? '' : 'b-pagination__item--disabled' ?>">
            <?php if ((int)$arResult['NavPageNomer'] > 1) {
                $title = GetMessage(
                    'nav_page_num_title',
                    ['#NUM#' => 1]
                );
                ?>
                <a class="b-pagination__link  js-pagination" title="<?= $title ?>" href="<?= $arResult['PREV_URL'] ?>">Назад</a>
                <?php
            } else {
                ?>
                <span class="b-pagination__link">Назад</span>
                <?php
            } ?>
        </li>


        <?php $navRecordGroup = 1;
        while ($navRecordGroup <= $arResult['NavPageCount']) {
            $title = GetMessage(
                'nav_page_num_title',
                ['#NUM#' => $navRecordGroup]
            );
            if ($navRecordGroup === (int)$arResult['NavPageNomer']) {
                ?>
                <li class="b-pagination__item">
                <a class="b-pagination__link js-pagination active"
                   href="javascript:void(0);"
                   title="<?= $title ?>"><?= $navRecordGroup ?></a>
                </li><?php
            } elseif ($navRecordGroup === 1 && (bool)$arResult['bSavePage'] === false) {
                ?>
            <li class="b-pagination__item <?= $arResult['HIDDEN'][$navRecordGroup] ?? '' ?>">
                <a class="b-pagination__link js-pagination"
                   href="<?= $arResult['BASE_URI'] ?>"
                   title="<?= $title ?>"><?= $navRecordGroup ?></a>
                </li><?php
            } else {
                ?>
            <li class="b-pagination__item <?= $arResult['HIDDEN'][$navRecordGroup] ?? '' ?>">
                <a class="b-pagination__link js-pagination"
                   href="<?= $arResult['URLS'][$navRecordGroup] ?>"
                   title="<?= $title ?>"><?= $navRecordGroup ?></a>
                </li><?php
            }
            if ($navRecordGroup === 1 && (int)$arResult['nStartPage'] > 1
                && (int)$arResult['nStartPage'] - $navRecordGroup >= 0) {
                ?>
                <li class="b-pagination__item">
                    <span class="b-pagination__dot">&hellip;</span>
                </li><?php
                $navRecordGroup = (int)$arResult['nStartPage'];
            } elseif ($navRecordGroup === (int)$arResult['nEndPage']
                && (int)$arResult['nEndPage'] < ($arResult['NavPageCount'] - 1)) {
                ?>
                <li class="b-pagination__item">
                    <span class="b-pagination__dot">&hellip;</span>
                </li><?php
                $navRecordGroup = $arResult['NavPageCount'];
            } else {
                $navRecordGroup++;
            }
        } ?>

        <li class="b-pagination__item b-pagination__item--next <?= ((int)$arResult['NavPageNomer']
            < $arResult['NavPageCount']) ? '' : 'b-pagination__item--disabled' ?>">
            <?php if ((int)$arResult['NavPageNomer'] < $arResult['NavPageCount']) {
                $title = GetMessage(
                    'nav_page_num_title',
                    ['#NUM#' => $arResult['NavPageCount']]
                );
                ?>
                <a class="b-pagination__link js-pagination" title="<?= $title ?>" href="<?= $arResult['NEXT_URL'] ?>">
                    Вперед
                </a>
                <?php
            } else {
                ?>
                <span class="b-pagination__link">Вперед</span>
                <?php
            } ?>
        </li>
    </ul>
</div>
