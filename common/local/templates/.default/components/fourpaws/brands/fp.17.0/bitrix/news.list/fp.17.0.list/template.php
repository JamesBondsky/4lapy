<?if (!defined('B_PROLOG_INCLUDED')||B_PROLOG_INCLUDED!==true) {
    die();
}
/**
 * Сгруппированный список брендов (в разделе брендов)
 *
 * @updated: 25.12.2017
 */
$this->setFrameMode(true);

if (!$arResult['ITEMS']) {
    return;
}

foreach ($arResult['GROUPING'] as $arGroup) {
    if (empty($arGroup['ITEMS_ARRAY_KEYS'])) {
        continue;
    }
    ?><a name="<?=$arGroup['ANCHOR']?>"></a><?php
    ?><h1 class="b-title b-title--h1"><?=$arGroup['TITLE']?></h1><?php
    ?><div class="b-popular-brand b-popular-brand--brands"><?php
        foreach ($arGroup['ITEMS_ARRAY_KEYS'] as $mKey) {
            $arItem = isset($arResult['ITEMS'][$mKey]) ? $arResult['ITEMS'][$mKey] : array();
            if (!$arItem) {
                continue;
            } ?><a class="b-popular-brand-item b-popular-brand-item--brands" href="<?=$arItem['DETAIL_PAGE_URL']?>" title="<?=$arItem['NAME']?>">
                <span class="b-popular-brand-item__link b-popular-brand-item__link--brands"><?php
                    if ($arItem['PRINT_PICTURE']) {
                        ?><img class="b-popular-brand-item__image js-image-wrapper" src="<?=$arItem['PRINT_PICTURE']['SRC']?>" alt="<?=$arItem['NAME']?>" title="<?=$arItem['NAME']?>"><?php
                    }
                ?></span>
                <p class="b-popular-brand-item__name"><?=$arItem['NAME']?></p>
            </a><?php
        }
    ?></div><?php
}
