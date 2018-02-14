<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\Menu\Helper\TreeMenuBuilder;

if (empty($arResult)) {
    return;
}

$oMenu = new TreeMenuBuilder($arResult, $arParams);

$drawMenuLevel1 = function ($menu = [], $title = '') use ($oMenu) {
    if (empty($menu)) {
        return '';
    }

    $outString = '<ul class="b-main-list-category">';
    foreach ($menu as $index => $item) {
        $outString .= '<li class="b-main-list-category__item">';
        $outString .= '<a href="' . $item['LINK'] . '" class="b-main-list-category__link" title="' . $item['TEXT'] . '">';
        $outString .= $item['TEXT'];
        $outString .= '</a>';
        $outString .= '</li>';
    }
    $outString .= '</ul>';

    return $outString;
};

$oMenu->setMarkupFunction($drawMenuLevel1, 1);
$oMenu->drawMenu();
