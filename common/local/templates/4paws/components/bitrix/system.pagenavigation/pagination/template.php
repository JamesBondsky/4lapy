<?php use Bitrix\Main\Web\Uri;

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

$strNavQueryString     = ($arResult['NavQueryString'] !== '' ? $arResult['NavQueryString'] . '&amp;' : '');
$strNavQueryStringFull = ($arResult['NavQueryString'] !== '' ? '?' . $arResult['NavQueryString'] : '');
/**
 * на основе visual
 */
?>

<div class="b-pagination">
    <ul class="b-pagination__list">
        <?php if ($arResult['bDescPageNumbering'] === true): ?>
            <li class="b-pagination__item b-pagination__item--prev<?= ($arResult['NavPageNomer']
                                                                       < $arResult['NavPageCount']) ? '' : 'b-pagination__item--disabled' ?>">
                <?php if ($arResult['NavPageNomer'] < $arResult['NavPageCount']) {
                    $uri =
                        new Uri($arResult['sUrlPath'] . '?' . $strNavQueryString . 'PAGEN_' . $arResult['NavNum'] . '='
                                . ($arResult['NavPageNomer'] + 1)); ?>
                    <?php if ($arResult['bSavePage']) { ?>
                        <a class="b-pagination__link" href="<?= $uri->getUri() ?>">Назад</a>
                    <?php } else { ?>
                        <?php if ($arResult['NavPageCount'] === ($arResult['NavPageNomer'] + 1)) { ?>
                            <a href="<?= $arResult['sUrlPath'] ?><?= $strNavQueryStringFull ?>">Назад</a>
                        <?php } else { ?>
                            <a href="<?= $uri->getUri() ?>">Назад</a>
                        <?php } ?>
                    <?php } ?>
                <?php } else { ?>
                    <span class="b-pagination__link">Назад</span>
                <?php } ?>
            </li>
            
            
            <?php $i        = 0;
            $NavRecordGroup = $arResult['NavPageCount'];
            while ($NavRecordGroup >= 1) {
                $i++;
                $NavRecordGroupPrint = $arResult['NavPageCount'] - $NavRecordGroup + 1;
                $strTitle            = GetMessage('nav_page_num_title',
                                                  ['#NUM#' => $NavRecordGroupPrint]);
                $hiddenClass         = ($i > 3 && $i <= 5) ? ' hidden' : '';
                $i                   = ($i === 5) ? 0 : $i;
                if ($NavRecordGroup === $arResult['NavPageNomer']) {
                    ?>
                    <li class="b-pagination__item">
                        <a class="b-pagination__link active"
                           href="javascript:void(0);"
                           title="<?= $strTitle ?>"><?= $NavRecordGroupPrint ?></a>
                    </li>
                    <?php
                } elseif ($NavRecordGroup === $arResult['NavPageCount'] && $arResult['bSavePage'] === false) {
                    ?>
                    <li class="b-pagination__item<?= $hiddenClass ?>">
                        <a class="b-pagination__link"
                           href="<?= $arResult['sUrlPath'] ?><?= $strNavQueryStringFull ?>"
                           title="<?= $strTitle ?>"><?= $NavRecordGroupPrint ?></a>
                    </li>
                    <?php
                } else {
                    ?>
                    <li class="b-pagination__item<?= $hiddenClass ?>">
                        <a class="b-pagination__link"
                           href="<?= $arResult['sUrlPath'] ?>?<?= $strNavQueryString ?>PAGEN_<?= $arResult['NavNum'] ?>=<?= $arResult['NavPageSize'] ?>"
                           title="<?= $strTitle ?>"><?= $NavRecordGroupPrint ?></a>
                    </li>
                <?php }
                if (1 === ($arResult['NavPageCount'] - $NavRecordGroup)
                    && 2 < ($arResult['NavPageCount'] - $arResult['nStartPage'])) {
                    ?>
                    <li class="b-pagination__item">
                        <span class="b-pagination__dot">&hellip;</span>
                    </li>
                    <?php
                    $NavRecordGroup = $arResult['nStartPage'];
                } elseif ($NavRecordGroup == $arResult['nEndPage'] && 3 < $arResult['nEndPage']) {
                    ?>
                    <li class="b-pagination__item">
                        <span class="b-pagination__dot">&hellip;</span>
                    </li>
                    <?php
                    $NavRecordGroup = 2;
                } else {
                    $NavRecordGroup--;
                }
            }
            ?>
            
            <li class="b-pagination__item b-pagination__item--next<?= ($arResult['NavPageNomer']
                                                                       > 1) ? '' : 'b-pagination__item--disabled' ?>">
                <?php if ($arResult['NavPageNomer'] > 1) {
                    $uri =
                        new Uri($arResult['sUrlPath'] . '?' . $strNavQueryString . 'PAGEN_' . $arResult['NavNum'] . '='
                                . ($arResult['NavPageNomer'] - 1)) ?>
                    <a class="b-pagination__link" href="<?= $uri->getUri() ?>">
                        Вперед
                    </a>
                <?php } else { ?>
                    <span class="b-pagination__link">Вперед</span>
                <?php } ?>
            </li>
        
        
        <? else: ?>
            <li class="b-pagination__item b-pagination__item--prev<?= ($arResult['NavPageNomer']
                                                                       > 1) ? '' : 'b-pagination__item--disabled' ?>">
                <?php if ($arResult['NavPageNomer'] > 1) {
                    $uri =
                        new Uri($arResult['sUrlPath'] . '?' . $strNavQueryString . 'PAGEN_' . $arResult['NavNum'] . '='
                                . ($arResult['NavPageNomer'] - 1)); ?>
                    <?php if ($arResult['bSavePage']) { ?>
                        <a class="b-pagination__link" href="<?= $uri->getUri() ?>">Назад</a>
                    <?php } else { ?>
                        <?php if ($arResult['NavPageNomer'] > 2) { ?>
                            <a href="<?= $uri->getUri() ?>">Назад</a>
                        <?php } else { ?>
                            <a href="<?= $arResult['sUrlPath'] ?><?= $strNavQueryStringFull ?>">Назад</a>
                        <?php } ?>
                    <?php } ?>
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
                       href="<?= $arResult['sUrlPath'] ?><?= $strNavQueryStringFull ?>"
                       title="<?= $strTitle ?>"><?= $NavRecordGroup ?></a>
                    </li><?php
                } else {
                    ?>
                    <li class="b-pagination__item<?= $hiddenClass ?>">
                        <a class="b-pagination__link"
                           href="<?= $arResult['sUrlPath'] ?>?<?= $strNavQueryString ?>PAGEN_<?= $arResult['NavNum'] ?>=<?= $NavRecordGroup ?>"
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
                <?php if ($arResult['NavPageNomer'] < $arResult['NavPageCount']) {
                    $uri =
                        new Uri($arResult['sUrlPath'] . '?' . $strNavQueryString . 'PAGEN_' . $arResult['NavNum'] . '='
                                . ($arResult['NavPageNomer'] + 1)) ?>
                    <a class="b-pagination__link" href="<?= $uri->getUri() ?>">
                        Вперед
                    </a>
                <?php } else { ?>
                    <span class="b-pagination__link">Вперед</span>
                <?php } ?>
            </li>
        <? endif ?>
        
        <?php if ($arResult['bShowAll']): ?>
            <li>
                <noindex>
                    <?php if ($arResult['NavShowAll']): ?>
                        <a href="<?= $arResult['sUrlPath'] ?>?<?= $strNavQueryString ?>SHOWALL_<?= $arResult['NavNum'] ?>=0"
                           rel="nofollow"><?= GetMessage('nav_paged') ?></a>
                    <? else: ?>
                        <a href="<?= $arResult['sUrlPath'] ?>?<?= $strNavQueryString ?>SHOWALL_<?= $arResult['NavNum'] ?>=1"
                           rel="nofollow"><?= GetMessage('nav_all') ?></a>
                    <? endif ?>
                </noindex>
            </li>
        <? endif ?>
    </ul>
</div>
