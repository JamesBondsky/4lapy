<?if (!defined('B_PROLOG_INCLUDED')||B_PROLOG_INCLUDED!==true) {
    die();
}
/**
 * Алфавитный указатель
 * !шаблон не должен кэшироваться!
 *
 * @updated: 21.12.2017
 */
$this->setFrameMode(true);

?><h1 class="b-title b-title--h1 b-title--catalog"><?=\Bitrix\Main\Localization\Loc::getMessage('BRANDS_AI.TITLE')?></h1>
<div class="b-link-list js-scroll-x">
    <div class="b-link-list__wrapper"><?php
        foreach ($arResult['PRINT'] as $arItem) {
            $sTmpAddClass = '';
            //if($arItem['SELECTED'] === 'Y') {
            //    $sTmpAddClass .= ' active';
            //}
            if ($arItem['EXISTS'] === 'Y') {
                $sTmpAddClass .= ' active';
            }
            if ($arItem['EXISTS'] === 'Y') {
                ?><a class="b-link-list__link js-scroll-x<?=$sTmpAddClass?>" href="<?='#'.$arItem['ANCHOR']?>" title=""><?php
                    echo $arItem['CAPTION'];
                ?></a><?php
            } else {
                ?><span class="b-link-list__link js-scroll-x<?=$sTmpAddClass?>" title=""><?php
                    echo $arItem['CAPTION'];
                ?></span><?php
            }
        }
    ?></div>
</div><?php
