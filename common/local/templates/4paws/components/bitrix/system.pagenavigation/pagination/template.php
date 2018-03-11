<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * @var \CBitrixComponentTemplate $this
 *
 * @var array                     $arResult
 */
$this->setFrameMode(true);

if (!(bool)$arResult['NavShowAlways'] && ((int)$arResult['NavRecordCount'] === 0
        || ((int)$arResult['NavPageCount'] === 1
            && (bool)$arResult['NavShowAll'] === false))) {
    return;
}

$class = '';
if ($arParams['AJAX_MODE'] === 'Y') {
    $class = ' js-pagination';
} ?>

<div class="b-pagination">
    <ul class="b-pagination__list">
        <?php $disabled = ((int)$arResult['NavPageNomer'] > 1) ? '' : ' b-pagination__item--disabled'; ?>
        <li class="b-pagination__item b-pagination__item--prev<?= $disabled ?>">
            <?php if ((int)$arResult['NavPageNomer'] > 1) { ?>
                <a class="b-pagination__link<?= $class ?>" title="<?=(int)$arResult['NavPageNomer']-1?>" href="<?= $arResult['PREV_URL'] ?>">Назад</a>
            <?php } else { ?>
                <span class="b-pagination__link">Назад</span>
            <?php } ?>
        </li>


        <?php $navRecordGroup = 1;
        while ($navRecordGroup <= $arResult['NavPageCount']) { ?>
            <li class="b-pagination__item <?= $navRecordGroup === (int)$arResult['NavPageNomer'] ? '' : $arResult['HIDDEN'][$navRecordGroup] ?? '' ?>">
                <a class="b-pagination__link<?= $class ?> <?= $navRecordGroup === (int)$arResult['NavPageNomer'] ? 'active' : '' ?>"
                   href="<?= $navRecordGroup === (int)$arResult['NavPageNomer'] ? '' : $arResult['URLS'][$navRecordGroup] ?>"
                   title="<?= $navRecordGroup ?>">
                    <?= $navRecordGroup ?>
                </a>
            </li>
            <?php /** установка точек */
            if (($arResult['START_BETWEEN_BEGIN'] > 0 && $navRecordGroup === $arResult['START_BETWEEN_BEGIN'])
                || ($arResult['END_BETWEEN_BEGIN'] > 0 && $navRecordGroup === $arResult['END_BETWEEN_BEGIN'])) { ?>
                <li class="b-pagination__item">
                    <span class="b-pagination__dot">&hellip;</span>
                </li>
                <?php $navRecordGroup = $arResult['START_BETWEEN_BEGIN'] === $navRecordGroup ? $arResult['START_BETWEEN_END'] : $arResult['END_BETWEEN_END'];
            }
            $navRecordGroup++;
        } ?>

        <?php $disabled = ((int)$arResult['NavPageNomer'] < $arResult['NavPageCount']) ? '' : ' b-pagination__item--disabled'; ?>
        <li class="b-pagination__item b-pagination__item--next<?= $disabled ?>">
            <?php if ((int)$arResult['NavPageNomer'] < $arResult['NavPageCount']) { ?>
                <a class="b-pagination__link<?= $class ?>" title="<?=(int)$arResult['NavPageNomer']+1?>" href="<?= $arResult['NEXT_URL'] ?>">
                    Вперед
                </a>
            <?php } else { ?>
                <span class="b-pagination__link">Вперед</span>
            <?php } ?>
        </li>
    </ul>
</div>
