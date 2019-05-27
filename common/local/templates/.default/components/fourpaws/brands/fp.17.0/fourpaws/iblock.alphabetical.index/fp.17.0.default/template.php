<?
use FourPaws\Decorators\SvgDecorator;

if (!defined('B_PROLOG_INCLUDED')||B_PROLOG_INCLUDED!==true) {
    die();
}
/**
 * Алфавитный указатель
 * !шаблон не должен кэшироваться!
 *
 * @updated: 21.12.2017
 */
$this->setFrameMode(true);

?><h2 class="b-title b-title--h1 b-title--catalog b-title--alphabet-brands"><?=\Bitrix\Main\Localization\Loc::getMessage('BRANDS_AI.TITLE')?></h2>
<div class="b-link-list__wrap b-link-list__wrap-scroll-arrows js-wrap-arrows-scroll-x">
    <span class="b-icon b-icon--orange b-icon--brand-scroll-prev js-prev-brand-scroll">
        <?= new SvgDecorator('icon-arrow-down', 10, 10) ?>
    </span>
    <span class="b-icon b-icon--orange b-icon--brand-scroll-next js-next-brand-scroll">
        <?= new SvgDecorator('icon-arrow-down', 10, 10) ?>
    </span>

    <div class="b-link-list js-scroll-x js-arrows-scroll-x">
        <div class="b-link-list__wrapper js-content-arrows-scroll-x"><?php
            foreach ($arResult['PRINT'] as $arItem) {
                $sTmpAddClass = '';
                //if($arItem['SELECTED'] === 'Y') {
                //    $sTmpAddClass .= ' active';
                //}
                if ($arItem['EXISTS'] === 'Y') {
                    $sTmpAddClass .= ' active';
                }
                if ($arItem['EXISTS'] === 'Y') {
                    ?><a class="b-link-list__link js-scroll-x<?=$sTmpAddClass?>"   href="javascript:void(0);" onclick="document.location.hash='<?=$arItem['ANCHOR']?>';" title="<?=$arItem['ANCHOR']?>"><?php
                        echo $arItem['CAPTION'];
                    ?></a><?php
                } else {
                    ?><span class="b-link-list__link js-scroll-x<?=$sTmpAddClass?>" title=""><?php
                        echo $arItem['CAPTION'];
                    ?></span><?php
                }
            }
        ?></div>
    </div>
</div><?php
