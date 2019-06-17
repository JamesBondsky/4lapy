<?php

use FourPaws\Decorators\SvgDecorator;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * Главное меню сайта
 *
 * @updated: 16.02.2018
 */
/**
 * @global CMain                 $APPLICATION
 * @var array                    $arParams
 * @var array                    $arResult
 * @var CBitrixComponentTemplate $this
 * @var string                   $templateName
 * @var string                   $componentPath
 * @var CBitrixComponent         $component
 */

$this->setFrameMode(true);

if (!$arResult['MENU_TREE']) {
    return;
}

$sArrowDownSwg_10_10 = (new SvgDecorator('icon-arrow-down', 10, 10))->__toString();
$sArrowDownSwg_6_10 = (new SvgDecorator('icon-arrow-down', 6, 10))->__toString();
$sArrowDownIco = '<span class="b-icon b-icon--more b-icon--orange b-icon--left-5">' . $sArrowDownSwg_10_10 . '</span>';
$sArrowDownOrangeIco = '<span class="b-icon b-icon--back-mobile b-icon--orange">' . $sArrowDownSwg_10_10 . '</span>';
$sArrowDownOrangeIcoBack = '<span class="b-icon b-icon--back-mobile b-icon--orange">' . $sArrowDownSwg_6_10 . '</span>';
$sArrowDownIcoSecond = '<span class="b-icon b-icon--menu-main">' . $sArrowDownSwg_6_10 . '</span>';
$sArrowDownIcoThird = '<span class="b-icon">' . $sArrowDownSwg_6_10 . '</span>';
$sArrowDownIcoFourth = '<span class="b-icon b-icon--menu-main b-icon--none-desktop">' . $sArrowDownSwg_6_10 . '</span>';
$sArrowDownIcoBrand = '<span class="b-icon b-icon--brand-menu">' . $sArrowDownSwg_6_10 . '</span>';

// 
// Основной блок меню
//
?>
    <nav class="b-menu js-nav-first-mobile">
        <ul class="b-menu__list">
            <?php foreach ($arResult['MENU_TREE'] as $arItem) {
                if ($arItem['NESTED'] || $arItem['IS_BRAND_MENU']) {
                    /** @todo переделать это дерьмо - не расширяемо нормально */
                    //|| $arItem['CODE'] === 'services'
                    if ($arItem['CODE'] === 'pet') {
                        $sAddClass1 = ' js-menu-pet-mobile';
                        $sAddClass2 = ' js-open-main-menu js-open-step-mobile';
                    } else {
                        $sAddClass1 = $arItem['IS_BRAND_MENU'] ? ' js-menu-brand-mobile' : ' js-show-dropdown';
                        $sAddClass2 = $arItem['IS_BRAND_MENU'] ? ' js-open-main-menu js-open-brand-mobile' : '';
                    } ?>
                    <li class="b-menu__item b-menu__item--more js-item-more-menu<?= $sAddClass1 ?>">
                        <a class="b-menu__link b-menu__link--more js-link-item-more-menu<?= $sAddClass2 ?>"<?= $arItem['_LINK_ATTR1_'] ?>
                           href="<?= $arItem['_URL_'] ?>">
                            <?php echo $arItem['_TEXT_'];
                            echo $sArrowDownIco; ?>
                        </a>
                        <?php
                        // Выпадающее меню, если это не меню "Товары по питомцу" и "По бренду".
                        // Только второй уровень версткой предусмотрен
                        //&& $arItem['CODE'] === 'services'
                        if ($arItem['CODE'] !== 'pet' && !$arItem['IS_BRAND_MENU']) { ?>
                            <div class="b-menu__dropdown b-dropdown-menu">
                                <div class="b-item-back">
                                    <a class="b-item-back__link js-close-dropdown"<?= $arItem['_LINK_ATTR2_'] ?>
                                       href="<?= $arItem['_URL_'] ?>">
                                        <?php echo $sArrowDownOrangeIco;
                                        echo $arItem['_TEXT_']; ?>
                                    </a>
                                </div>
                                <?php foreach ($arItem['NESTED'] as $arSecondLevelItem) { ?>
                                    <a class="b-menu__link"<?= $arSecondLevelItem['_LINK_ATTR1_'] ?>
                                       href="<?= $arSecondLevelItem['_URL_'] ?>">
                                        <?= $arSecondLevelItem['_TEXT_'] ?>
                                    </a>
                                <?php } ?>
                            </div>
                        <?php } ?>
                    </li>
                <?php } else { ?>
                    <li class="b-menu__item<?= ($arItem['XML_ID'] == 'vetapteka') ? ' b-menu__item--vetapteka' : ''; ?>">
                        <a class="b-menu__link<?= ($arItem['XML_ID'] == 'vetapteka') ? ' b-menu__link--blue' : ''; ?>"<?= $arItem['_LINK_ATTR1_'] ?>
                           href="<?= $arItem['_URL_'] ?>">
                            <?= $arItem['_TEXT_'] ?>
                        </a>
                    </li>
                <?php }
            } ?>
        </ul>
    </nav>
<?php
//
// Dropdown-меню для пунктов "Товары по питомцу" и "По бренду"
//
ob_start();
foreach ($arResult['MENU_TREE'] as $arFirstLevelItem) {
    if (!$arFirstLevelItem['IS_BRAND_MENU']) {
        if (!$arFirstLevelItem['NESTED']) {
            continue;
        }
        if ($arFirstLevelItem['CODE'] !== 'pet') {
            continue;
        } ?>
        <div class="b-menu-dropdown js-menu-dropdown js-menu-pet-desktop">
            <div class="b-container">
                <ul class="b-menu-main js-dropdown-menu js-permutation-second-menu js-step-mobile">
                    <li class="b-back-link">
                        <a class="b-back-link__link js-back-submenu"<?= $arFirstLevelItem['_LINK_ATTR2_'] ?>
                           href="javascript:void(0);">
                            <?php echo $sArrowDownOrangeIco;
                            echo $arFirstLevelItem['_TEXT_']; ?>
                        </a>
                    </li>
                    <?php foreach ($arFirstLevelItem['NESTED'] as $arSecondLevelItem) { ?>
                        <li class="b-menu-main__item js-item-main-menu">
                            <a class="b-menu-main__link js-active-submenu <?=$arSecondLevelItem['NESTED'] ? ' js-open-step-mobile' : ''?>"<?= $arSecondLevelItem['_LINK_ATTR2_'] ?>
                               href="<?= $arSecondLevelItem['_URL_'] ?>">
                               <!--noindex-->
                                <?php echo $arSecondLevelItem['_TEXT_'];
                                if ($arSecondLevelItem['NESTED']) {
                                    echo $sArrowDownIcoSecond;
                                } ?>
                                <!--/noindex-->
                            </a>
                            <?php if ($arSecondLevelItem['NESTED']) { ?>
                                <div class="b-menu-main__submenu js-submenu js-step-mobile">
                                    <div class="b-back-link">
                                        <a class="b-back-link__link js-back-submenu"<?= $arSecondLevelItem['_LINK_ATTR2_'] ?>
                                           href="javascript:void(0);">
                                            <?php echo $sArrowDownOrangeIco;
                                            echo $arSecondLevelItem['_TEXT_']; ?>
                                        </a>
                                    </div>
                                    <?php if ($arSecondLevelItem['NESTED']) {
                                        foreach ($arSecondLevelItem['NESTED'] as $arThirdLevelItem) { ?>
                                            <div class="b-submenu-column">
                                                <a class="b-link b-link--submenu <?=$arThirdLevelItem['NESTED'] ? ' js-open-step-mobile js-open-step-mobile--submenu' : ''?>"<?= $arThirdLevelItem['_LINK_ATTR2_'] ?>
                                                   href="<?= $arThirdLevelItem['_URL_'] ?>">
                                                   <!--noindex-->
                                                    <?php echo '<span class="b-link__text b-link__text--submenu">' . $arThirdLevelItem['_TEXT_'] . '</span>';
                                                    if ($arThirdLevelItem['NESTED']) {
                                                        echo $sArrowDownIcoThird;
                                                    } ?>
                                                    <!--/noindex-->
                                                </a>
                                                <?php if ($arThirdLevelItem['NESTED']) { ?>
                                                    <ul class="b-submenu-column__list js-step-mobile">
                                                        <li class="b-back-link">
                                                            <a class="b-back-link__link js-back-submenu"<?= $arThirdLevelItem['_LINK_ATTR2_'] ?>
                                                               href="javascript:void(0);">
                                                                <?php echo $sArrowDownOrangeIco;
                                                                echo $arThirdLevelItem['_TEXT_']; ?>
                                                            </a>
                                                        </li>
                                                        <?php if ($arThirdLevelItem['NESTED']) {
                                                            foreach ($arThirdLevelItem['NESTED'] as $arFourthLevelItem) { ?>
                                                                <li class="b-submenu-column__item">
                                                                    <a class="b-submenu-column__link"<?= $arFourthLevelItem['_LINK_ATTR1_'] ?>
                                                                       href="<?= $arFourthLevelItem['_URL_'] ?>">
                                                                        <!--noindex-->
                                                                        <?php echo $arFourthLevelItem['_TEXT_'];
                                                                        //                                                                        echo $sArrowDownIcoFourth; ?>
                                                                        <!--/noindex-->
                                                                    </a>
                                                                </li>
                                                            <?php }
                                                        } ?>
                                                    </ul>
                                                <?php } ?>
                                            </div>
                                        <?php }
                                    }
                                    if ($arSecondLevelItem['SECTION_HREF'] && $arSecondLevelItem['SECTION_HREF']['ID'] && $arResult['SECTIONS_POPULAR_BRANDS'][$arSecondLevelItem['SECTION_HREF']['ID']]) {
                                        $sTmpUrl = '/brand/';
                                        $sTmpText = 'Популярные бренды';
                                        $sTmpTitle = 'Популярные бренды'; ?>
                                        <div class="b-menu-main__popular-brand">
                                            <div class="b-menu-main__title js-open-step-mobile">
                                                <?php //* ?>
                                                <a class="b-link b-link--brand-menu js-not-href js-not-href--brand-menu"
                                                   href="<?= $sTmpUrl ?>" title="<?= $sTmpTitle ?>">
                                                   <!--noindex-->
                                                   <?php
                                                    echo '<span class="b-link__text b-link__text--brand-menu">' . $sTmpText . '</span>';
                                                    echo $sArrowDownIcoThird;
                                                    ?>
                                                    <!--/noindex-->
                                                </a><?php
                                                /*/
                                                ?><span class="b-link b-link--brand-menu js-not-href js-not-href--brand-menu"><?php
                                                    echo '<span class="b-link__text b-link__text--brand-menu">'.$sTmpText.'</span>';
                                                    echo $sArrowDownIcoThird;
                                                ?></span><?php
                                                //*/
                                                ?></div>
                                            <div class="b-popular-brand b-popular-brand--flex b-popular-brand--menu-dropdown js-step-mobile">
                                                <?php /*
                                            ?><div class="b-popular-brand-item b-popular-brand-item--menu-dropdown">
                                                <a class="b-back-link__link js-back-submenu" href="<?=$sTmpUrl?>" title="<?=$sTmpTitle?>"><?php
                                                    echo $sArrowDownOrangeIco;
                                                    echo $sTmpText;
                                                ?></a>
                                            </div><?php
                                            */
                                                foreach ($arResult['SECTIONS_POPULAR_BRANDS'][$arSecondLevelItem['SECTION_HREF']['ID']] as $arBrandItem) { ?>
                                                    <div class="b-popular-brand-item b-popular-brand-item--menu-dropdown">
                                                        <a class="b-popular-brand-item__link b-popular-brand-item__link--menu-dropdown"
                                                           title="<?= $arBrandItem['NAME'] ?>"
                                                           href="<?= $arBrandItem['DETAIL_PAGE_URL'] ?>">
                                                            <?php echo '<span class="b-popular-brand-item__text">' . $arBrandItem['NAME'] . '</span>';
                                                            //                                                                echo $sArrowDownIcoBrand;
                                                            if ($arBrandItem['PRINT_PICTURE']) {
                                                                $arImg = $arBrandItem['PRINT_PICTURE']; ?>
                                                                <img
                                                                        class="b-popular-brand-item__image js-image-wrapper"
                                                                        src="<?= $arImg['SRC'] ?>"
                                                                        alt="<?= $arImg['ALT'] ?>"
                                                                        title="<?= $arImg['TITLE'] ?>">
                                                            <?php } ?>
                                                        </a>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        </div>
        <div class="b-menu-mobile js-menu-mobile js-step-mobile"></div>
    <?php } else { ?>
        <div class="b-menu-dropdown b-menu-dropdown--brands js-menu-dropdown js-menu-brands-desktop">
            <div class="b-menu-brands js-menu-brand-content">
                <div class="b-back-link b-back-link--brands">
                    <a class="b-back-link__link js-close-popup js-close-brand-mobile"<?= $arFirstLevelItem['_LINK_ATTR2_'] ?>
                       href="javascript:void(0);">
                        <?php echo $sArrowDownOrangeIco;
                        echo $arFirstLevelItem['_TEXT_']; ?>
                    </a>
                </div>
                <?php
                //
                // Бренды (алфавитный указатель, сгруппированный список, популярные бренды)
                //
                $APPLICATION->IncludeComponent('refactoring:brands.list', 'top.menu', [
                    'BRANDS_POPULAR_LIMIT' => $arParams['BRANDS_MENU_POPULAR_LIMIT'] ?? 8,
                ], $component, [
                    'HIDE_ICONS'       => 'Y',
                    'ACTIVE_COMPONENT' => 'Y',
                ]);
                ?>
            </div>
        </div>
    <?php }
}
$arResult['header_dropdown_menu'] = ob_get_clean();
$component->setResultCacheKeys(
    [
        'header_dropdown_menu',
    ]
);
