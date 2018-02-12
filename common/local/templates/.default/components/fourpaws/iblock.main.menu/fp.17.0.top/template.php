<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * Главное меню сайта
 *
 * @updated: 11.01.2018
 */
/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $componentPath
 * @var CBitrixComponent $component
 */

$this->setFrameMode(true);

if (!$arResult['MENU_TREE']) {
    return;
}

$sArrowDownSwg_10_10 = (new \FourPaws\Decorators\SvgDecorator('icon-arrow-down', 10, 10))->__toString();
$sArrowDownSwg_6_10 = (new \FourPaws\Decorators\SvgDecorator('icon-arrow-down', 6, 10))->__toString();
$sArrowDownIco = '<span class="b-icon b-icon--more b-icon--orange b-icon--left-5">'.$sArrowDownSwg_10_10.'</span>';
$sArrowDownOrangeIco = '<span class="b-icon b-icon--back-mobile b-icon--orange">'.$sArrowDownSwg_10_10.'</span>';
$sArrowDownIcoSecond = '<span class="b-icon b-icon--menu-main">'.$sArrowDownSwg_6_10.'</span>';
$sArrowDownIcoThird = '<span class="b-icon">'.$sArrowDownSwg_6_10.'</span>';
$sArrowDownIcoFourth = '<span class="b-icon b-icon--menu-main b-icon--none-desktop">'.$sArrowDownSwg_6_10.'</span>';
$sArrowDownIcoBrand = '<span class="b-icon b-icon--brand-menu">'.$sArrowDownSwg_6_10.'</span>';

// 
// Основной блок меню
//
?><nav class="b-menu js-nav-first-mobile">
    <ul class="b-menu__list"><?php
        foreach ($arResult['MENU_TREE'] as $arItem) {
            if ($arItem['NESTED'] || $arItem['IS_BRAND_MENU']) {
                $sAddClass1 = $arItem['IS_BRAND_MENU'] ? ' js-menu-brand-mobile' : ' js-menu-pet-mobile';
                $sAddClass2 = $arItem['IS_BRAND_MENU'] ? ' js-open-brand-mobile' : ' js-open-step-mobile';
                ?><li class="b-menu__item b-menu__item--more<?=$sAddClass1?>">
                    <a class="b-menu__link b-menu__link--more js-open-main-menu<?=$sAddClass2?>"<?=$arItem['_LINK_ATTR1_']?> href="<?=$arItem['_URL_']?>"><?php
                        echo $arItem['_TEXT_'];
                        echo $sArrowDownIco;
                    ?></a>
                </li><?php
            } else {
                ?><li class="b-menu__item">
                    <a class="b-menu__link"<?=$arItem['_LINK_ATTR1_']?> href="<?=$arItem['_URL_']?>"><?=$arItem['_TEXT_']?></a>
                </li><?php
            }
        }
    ?></ul>
</nav><?php

//
// Dropdown-меню
//
ob_start();
foreach ($arResult['MENU_TREE'] as $arFirstLevelItem) {
    if (!$arFirstLevelItem['IS_BRAND_MENU']) {
        if (!$arFirstLevelItem['NESTED']) {
            continue;
        }
        ?><div class="b-menu-dropdown js-menu-dropdown js-menu-pet-desktop">
            <div class="b-container">
                <ul class="b-menu-main js-dropdown-menu js-permutation-second-menu js-step-mobile">
                    <li class="b-back-link">
                        <a class="b-back-link__link js-back-submenu"<?=$arFirstLevelItem['_LINK_ATTR2_']?> href="<?=$arFirstLevelItem['_URL_']?>"><?php
                            echo $sArrowDownOrangeIco;
                            echo $arFirstLevelItem['_TEXT_'];
                        ?></a>
                    </li><?php

                    foreach ($arFirstLevelItem['NESTED'] as $arSecondLevelItem) {
                        ?><li class="b-menu-main__item">
                            <a class="b-menu-main__link js-active-submenu js-open-step-mobile"<?=$arSecondLevelItem['_LINK_ATTR2_']?> href="<?=$arSecondLevelItem['_URL_']?>"><?php
                                echo $arSecondLevelItem['_TEXT_'];
                                echo $sArrowDownIcoSecond;
                            ?></a>

                            <div class="b-menu-main__submenu js-submenu js-step-mobile">
                                <div class="b-back-link">
                                    <a class="b-back-link__link js-back-submenu"<?=$arSecondLevelItem['_LINK_ATTR2_']?> href="<?=$arSecondLevelItem['_URL_']?>"><?php
                                        echo $sArrowDownOrangeIco;
                                        echo $arSecondLevelItem['_TEXT_'];
                                    ?></a>
                                </div><?php

                                if ($arSecondLevelItem['NESTED']) {
                                    foreach ($arSecondLevelItem['NESTED'] as $arThirdLevelItem) {
                                        ?><div class="b-submenu-column">
                                            <a class="b-link b-link--submenu js-open-step-mobile js-open-step-mobile--submenu"<?=$arThirdLevelItem['_LINK_ATTR2_']?> href="<?=$arThirdLevelItem['_URL_']?>"><?php
                                                echo '<span class="b-link__text b-link__text--submenu">'.$arThirdLevelItem['_TEXT_'].'</span>';
                                                echo $sArrowDownIcoThird;
                                            ?></a>
                                            <ul class="b-submenu-column__list js-step-mobile">
                                                <li class="b-back-link">
                                                    <a class="b-back-link__link js-back-submenu"<?=$arThirdLevelItem['_LINK_ATTR2_']?> href="<?=$arThirdLevelItem['_URL_']?>"><?php
                                                        echo $sArrowDownOrangeIco;
                                                        echo $arThirdLevelItem['_TEXT_'];
                                                    ?></a>
                                                </li><?php
                                                if ($arThirdLevelItem['NESTED']) {
                                                    foreach ($arThirdLevelItem['NESTED'] as $arFourthLevelItem) {
                                                        ?><li class="b-submenu-column__item">
                                                            <a class="b-submenu-column__link"<?=$arFourthLevelItem['_LINK_ATTR1_']?> href="<?=$arFourthLevelItem['_URL_']?>"><?php
                                                                echo $arFourthLevelItem['_TEXT_'];
                                                                echo $sArrowDownIcoFourth;
                                                            ?></a>
                                                        </li><?php
                                                    }
                                                }
                                            ?></ul>
                                        </div><?php
                                    }
                                }
                                if ($arSecondLevelItem['SECTION_HREF'] && $arSecondLevelItem['SECTION_HREF']['ID']) {
                                    if ($arResult['SECTIONS_POPULAR_BRANDS'][$arSecondLevelItem['SECTION_HREF']['ID']]) {
                                        $sTmpUrl = 'javascript:void(0);';
                                        $sTmpText = 'Популярные бренды';
                                        $sTmpTitle = 'Популярные бренды';
                                        ?><div class="b-menu-main__popular-brand">
                                            <div class="b-menu-main__title js-open-step-mobile"><?php
                                                //*
                                                ?><a class="b-link b-link--brand-menu js-not-href js-not-href--brand-menu" href="<?=$sTmpUrl?>" title="<?=$sTmpTitle?>"><?php
                                                    echo '<span class="b-link__text b-link__text--brand-menu">'.$sTmpText.'</span>';
                                                    echo $sArrowDownIcoThird;
                                                ?></a><?php
                                                /*/
                                                ?><span class="b-link b-link--brand-menu js-not-href js-not-href--brand-menu"><?php
                                                    echo '<span class="b-link__text b-link__text--brand-menu">'.$sTmpText.'</span>';
                                                    echo $sArrowDownIcoThird;
                                                ?></span><?php
                                                //*/
                                            ?></div>
                                            <div class="b-popular-brand b-popular-brand--flex b-popular-brand--menu-dropdown js-step-mobile"><?php
                                                /*
                                                ?><div class="b-popular-brand-item b-popular-brand-item--menu-dropdown">
                                                    <a class="b-back-link__link js-back-submenu" href="<?=$sTmpUrl?>" title="<?=$sTmpTitle?>"><?php
                                                        echo $sArrowDownOrangeIco;
                                                        echo $sTmpText;
                                                    ?></a>
                                                </div><?php
                                                */
                                                foreach ($arResult['SECTIONS_POPULAR_BRANDS'][$arSecondLevelItem['SECTION_HREF']['ID']] as $arBrandItem) {
                                                    ?><div class="b-popular-brand-item b-popular-brand-item--menu-dropdown">
                                                        <a class="b-popular-brand-item__link b-popular-brand-item__link--menu-dropdown" title="<?=$arBrandItem['NAME']?>" href="<?=$arBrandItem['DETAIL_PAGE_URL']?>"><?php
                                                            echo '<span class="b-popular-brand-item__text">'.$arBrandItem['NAME'].'</span>';
                                                            echo $sArrowDownIcoBrand;
                                                            if ($arBrandItem['PRINT_PICTURE']) {
                                                                $arImg = $arBrandItem['PRINT_PICTURE'];
                                                                ?><img class="b-popular-brand-item__image js-image-wrapper" src="<?=$arImg['SRC']?>" alt="<?=$arImg['ALT']?>" title="<?=$arImg['TITLE']?>"><?php
                                                            }
                                                        ?></a>
                                                    </div><?php
                                                }
                                            ?></div>
                                        </div><?php
                                    }
                                }
                            ?></div>
                        </li><?php
                    }
                ?></ul>
            </div>
        </div>
        <div class="b-menu-mobile js-menu-mobile js-step-mobile">
        </div><?php
    } else {
        ?><div class="b-menu-dropdown b-menu-dropdown--brands js-menu-dropdown js-menu-brands-desktop">
            <div class="b-menu-brands js-menu-brand-content">
                <div class="b-back-link b-back-link--brands">
                    <a class="b-back-link__link js-close-popup js-close-brand-mobile"<?=$arFirstLevelItem['_LINK_ATTR2_']?> href="<?=$arFirstLevelItem['_URL_']?>"><?php
                        echo $sArrowDownOrangeIco;
                        echo $arFirstLevelItem['_TEXT_'];
                    ?></a>
                </div><?php
                //
                // Бренды (алфавитный указатель, сгруппированный список, популярные бренды)
                //
                $APPLICATION->IncludeComponent(
                    'bitrix:news.list',
                    'fp.17.0.brands',
                    array(
                        'BRANDS_POPULAR_LIMIT' => $arParams['BRANDS_MENU_POPULAR_LIMIT'] ?? 8,

                        'IBLOCK_TYPE' => \FourPaws\Enum\IblockType::CATALOG,
                        'IBLOCK_ID' => \FourPaws\Enum\IblockCode::BRANDS,
                        'SORT_BY1' => 'SORT',
                        'SORT_ORDER1' => 'ASC',
                        'SORT_BY2' => 'NAME',
                        'SORT_ORDER2' => 'ASC',
                        'FIELD_CODE' => array(
                            'NAME',
                            'DETAIL_PICTURE',
                            'PROPERTY_POPULAR'
                        ),
                        'FILTER_NAME' => '',
                        'CACHE_FILTER' => 'Y',
                        'CACHE_GROUPS' => 'N',
                        'NEWS_COUNT' => '9999',
                        'CACHE_TIME' => $arParams['TEMPLATE_NO_CACHE'] === 'Y' ? $arParams['CACHE_TIME'] : 'N',
                        'CACHE_TYPE' => $arParams['CACHE_TYPE'],
                        'CHECK_DATES' => 'Y',
                        'DETAIL_URL' => '',

                        'RESIZE_WIDTH' => $arParams['RESIZE_WIDTH'],
                        'RESIZE_HEIGHT' => $arParams['RESIZE_HEIGHT'],
                        'RESIZE_TYPE' => $arParams['RESIZE_TYPE'],

                        'ACTIVE_DATE_FORMAT' => 'd.m.Y',
                        'ADD_SECTIONS_CHAIN' => 'N',
                        'AJAX_MODE' => 'N',
                        'AJAX_OPTION_ADDITIONAL' => '',
                        'AJAX_OPTION_HISTORY' => 'N',
                        'AJAX_OPTION_JUMP' => 'N',
                        'AJAX_OPTION_STYLE' => 'N',
                        'HIDE_LINK_WHEN_NO_DETAIL' => 'N',
                        'INCLUDE_IBLOCK_INTO_CHAIN' => 'N',
                        'INCLUDE_SUBSECTIONS' => 'N',
                        'PAGER_BASE_LINK_ENABLE' => 'N',
                        'PAGER_DESC_NUMBERING' => 'N',
                        'PAGER_DESC_NUMBERING_CACHE_TIME' => '36000',
                        'PAGER_SHOW_ALL' => 'N',
                        'PAGER_SHOW_ALWAYS' => 'N',
                        'PAGER_TEMPLATE' => '',
                        'PAGER_TITLE' => '',
                        'PARENT_SECTION' => '',
                        'PARENT_SECTION_CODE' => '',
                        'PREVIEW_TRUNCATE_LEN' => '',
                        'PROPERTY_CODE' => array(
                        ),
                        'SET_BROWSER_TITLE' => 'N',
                        'SET_LAST_MODIFIED' => 'N',
                        'SET_META_DESCRIPTION' => 'N',
                        'SET_META_KEYWORDS' => 'N',
                        'SET_STATUS_404' => 'N',
                        'SET_TITLE' => 'N',
                        'SHOW_404' => 'N',
                    ),
                    $component,
                    array(
                        'HIDE_ICONS' => 'Y',
                        'ACTIVE_COMPONENT' => 'Y',
                    )
                );
            ?></div>
        </div><?php
    }
}
$arResult['header_dropdown_menu'] = ob_get_clean();
$component->setResultCacheKeys(
	array(
		'header_dropdown_menu',
	)
);
