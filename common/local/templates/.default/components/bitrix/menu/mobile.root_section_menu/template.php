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
        $code = '';
        switch ($item['LINK']) {
            case '/catalog/koshki/':
                $code = 'cat';
                break;
            case '/catalog/sobaki/':
                $code = 'dog';
                break;
            case '/shops/':
                $code = 'shops';
                break;
            case '/shares/':
                $code = 'shares';
                break;
        }

        $outString .= '<li class="b-main-list-category__item b-main-list-category__item_' . $code . '">';
        $outString .= '<a href="' . $item['LINK'] . '" class="b-main-list-category__link" title="' . $item['TEXT'] . '"><span class="b-main-list-category__name">';
        $outString .= $item['TEXT'];
        $outString .= '</span></a>';
        $outString .= '</li>';
    }
    $outString .= '</ul>';

    return $outString;
};

$oMenu->setMarkupFunction($drawMenuLevel1, 1);
$oMenu->drawMenu();
