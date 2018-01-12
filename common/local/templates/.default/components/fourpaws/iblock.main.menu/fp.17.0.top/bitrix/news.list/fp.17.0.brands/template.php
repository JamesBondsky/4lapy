<?if (!defined('B_PROLOG_INCLUDED')||B_PROLOG_INCLUDED!==true) {
    die();
}
/**
 * Бренды в меню (алфавитный указатель, сгруппированный список, популярные бренды)
 *
 * @updated: 11.01.2017
 */
$this->setFrameMode(true);

if (!$arResult['ITEMS']) {
    return;
}

$arParams['BRANDS_POPULAR_LIMIT'] = isset($arParams['BRANDS_POPULAR_LIMIT']) ? intval($arParams['BRANDS_POPULAR_LIMIT']) : 6;
$arParams['BRANDS_POPULAR_LIMIT'] = $arParams['BRANDS_POPULAR_LIMIT'] > 0 ? $arParams['BRANDS_POPULAR_LIMIT'] : 6;

$arCharsList = explode(',', \Bitrix\Main\Localization\Loc::getMessage('MENU_BRANDS.ALPHABET'));
$sPopBrandsText = \Bitrix\Main\Localization\Loc::getMessage('MENU_BRANDS.POP_BRANDS_TEXT');
$sPopBrandsTitle = \Bitrix\Main\Localization\Loc::getMessage('MENU_BRANDS.POP_BRANDS_TITLE');

?><div class="b-container"><?php
    //
    // Алфавитный указатель
    //
    ?><div class="b-menu-brands__nav"><?php
        ?><div class="b-link-list b-link-list--menu js-scroll-x-menu">
            <div class="b-link-list__wrapper"><?php
                $bWasActive = false;
                foreach ($arCharsList as $sChar) {
                    if (!empty($arResult['GROUPING'][$sChar])) {
                        $sAddClass = !$bWasActive ? ' active' : '';
                        $bWasActive = true;
                        ?><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu<?=$sAddClass?>" href="javascript:void(0);" title=""><?=$sChar?></a><?php
                    } else {
                        ?><span class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" title=""><?=$sChar?></span><?php
                    }
                }
            ?></div>
        </div>
    </div><?php

    ?><div class="b-menu-brands__brand-block js-scroll-x-brands">
        <ul class="b-menu-brands__group-list"><?php
            //
            // Популярные бренды (mob)
            //
            if($arResult['POPULAR_ITEMS_ARRAY_KEYS']) {
                ?><li class="b-menu-brands__group b-menu-brands__group--mobile-show">
                    <ul class="b-menu-brands__name-list b-menu-brands__name-list--no-top">
                        <li class="b-menu-brands__name">
                            <a class="b-menu-brands__name-link js-active-submenu js-open-step-mobile" href="javascript:void(0)" title="<?=$sPopBrandsTitle?>"><?=$sPopBrandsText?></a>
                            <div class="b-menu-main__submenu js-submenu js-step-mobile">
                                <div class="b-back-link">
                                    <a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="<?=$sPopBrandsTitle?>">
                                        <span class="b-icon b-icon--back-mobile b-icon--orange"><?php
                                            echo (new \FourPaws\Decorators\SvgDecorator('icon-arrow-down', 10, 10))->__toString();
                                        ?></span><?php
                                        echo $sPopBrandsText;
                                    ?></a>
                                </div>
                                <ul class="b-menu-brands__name-list b-menu-brands__name-list--no-top"><?php
                                    $iCnt = 0;
                                    foreach ($arResult['POPULAR_ITEMS_ARRAY_KEYS'] as $mKey) {
                                        $arItem = isset($arResult['ITEMS'][$mKey]) ? $arResult['ITEMS'][$mKey] : array();
                                        if (!$arItem) {
                                            continue;
                                        }
                                        ?><li class="b-menu-brands__name">
                                            <a class="b-menu-brands__name-link" href="<?=$arItem['DETAIL_PAGE_URL']?>" title="<?=$arItem['NAME']?>"><?=$arItem['NAME']?></a>
                                        </li><?php
                                        if (++$iCnt >= $arParams['BRANDS_POPULAR_LIMIT']) {
                                            break;
                                        }
                                    }
                                ?></ul>
                            </div>
                        </li>
                    </ul>
                </li><?php
            }

            //
            // Сгруппированный список брендов
            //
            foreach ($arResult['GROUPING'] as $arGroup) {
                if (empty($arGroup['ITEMS_ARRAY_KEYS'])) {
                    continue;
                }
                ?><li class="b-menu-brands__group">
                    <span class="b-menu-brands__litter js-brands-filter"><?=$arGroup['TITLE']?></span>
                    <ul class="b-menu-brands__name-list"><?php
                        foreach ($arGroup['ITEMS_ARRAY_KEYS'] as $mKey) {
                            $arItem = isset($arResult['ITEMS'][$mKey]) ? $arResult['ITEMS'][$mKey] : array();
                            if (!$arItem) {
                                continue;
                            }
                            ?><li class="b-menu-brands__name">
                                <a class="b-menu-brands__name-link" href="<?=$arItem['DETAIL_PAGE_URL']?>" title="<?=$arItem['NAME']?>"><?=$arItem['NAME']?></a>
                            </li><?php
                        }
                    ?></ul>
                </li><?php
            }
        ?></ul>
    </div><?php

    //
    // Популярные бренды (desk)
    //
    if($arResult['POPULAR_ITEMS_ARRAY_KEYS']) {
        ?><div class="b-menu-brands__popular-brand">
            <div class="b-menu-brands__title"><?=$sPopBrandsText?></div>
            <div class="b-popular-brand b-popular-brand--brands"><?php
                $iCnt = 0;
                foreach ($arResult['POPULAR_ITEMS_ARRAY_KEYS'] as $mKey) {
                    $arItem = isset($arResult['ITEMS'][$mKey]) ? $arResult['ITEMS'][$mKey] : array();
                    if (!$arItem) {
                        continue;
                    }
                    ?><div class="b-popular-brand-item b-popular-brand-item--brands-menu">
                        <a class="b-popular-brand-item__link b-popular-brand-item__link--brands-menu" title="<?=$arItem['NAME']?>" href="<?=$arItem['DETAIL_PAGE_URL']?>"><?php
                            if($arItem['PRINT_PICTURE']) {
                                $arImg = $arItem['PRINT_PICTURE'];
                                ?><img class="b-popular-brand-item__image js-image-wrapper" src="<?=$arImg['SRC']?>" alt="<?=$arImg['ALT']?>" title="<?=$arImg['TITLE']?>"><?php
                            }
                        ?></a>
                    </div><?php
                    if (++$iCnt >= $arParams['BRANDS_POPULAR_LIMIT']) {
                        break;
                    }
                }
            ?></div>
        </div><?php
    }
?></div><?php
