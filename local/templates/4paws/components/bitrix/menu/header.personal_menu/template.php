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
    
    $outString = '<div class="b-popover b-popover--person js-popover"><div class="b-person"><ul class="b-link-block">';
    foreach ($menu as $index => $item) {
        $outString .= '<li class="b-link-block__item">';
        $outString .= '<span class="b-icon"><svg class="b-icon__svg" viewBox="0 0 16 16 " width="16px" height="16px">';
        $outString .= '<use class="b-icon__use" xlink:href="/static/build/icons.svg#' . $item['PARAMS']['icon']
                      . '"></use>';
        $outString .= '</svg></span>';
        $outString .= '<a href="' . $item['LINK'] . '"class="b-link-block__link" title="' . $item['TEXT'] . '">';
        $outString .= $item['TEXT'];
        $outString .= '</a>';
        
        $outString .= '</LI>';
    }
    $outString .= '</ul></div></div>';
    
    return $outString;
};

$oMenu->setMarkupFunction($drawMenuLevel1, 1);
$oMenu->drawMenu();
