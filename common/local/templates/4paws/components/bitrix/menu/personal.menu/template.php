<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
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
    
    $outString = '<aside class="b-account__navigation-panel"><ul class="b-account-link">';
    foreach ($menu as $index => $item) {
        $outString .= '<li class="b-account-link__item' . ($item['SELECTED'] ? ' active' : '') . '">';
        $outString .= '<a href="' . $item['LINK'] . '" class="b-account-link__link js-tab-link-account" title="'
                      . $item['TEXT'] . '">';
        $outString .= $item['TEXT'];
        $outString .= '</a>';
        $outString .= '</li>';
    }
    $outString .= '</ul></aside>';
    
    return $outString;
};

$oMenu->setMarkupFunction($drawMenuLevel1, 1);
$oMenu->drawMenu();
