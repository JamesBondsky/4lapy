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

    $outString = '<nav class="b-footer-nav">';
    foreach ($menu as $index => $item) {
        $outString .= '<div class="b-footer-nav__list">';
        $outString .= '<h4 class="b-footer-nav__header">';
        $outString .= '<a href="' . ($item['LINK'] ?: 'javascript:void(0)') . '" class="b-footer-nav__header-link" title="' . $item['TEXT'] . '">';
        $outString .= $item['TEXT'];
        $outString .= '</a>';
        $outString .= '</h4>';
        if ($item['PARAMS']['CHILDS_BY_MENU']) {
            $menu = new \CMenu($item['PARAMS']['CHILDS_BY_MENU']);
            $menu->disableDebug();
            $success = $menu->Init('/', false, false, true);
            $subMenuExists = ($success && \count($menu->arMenu) > 0);

            if ($subMenuExists) {
                $menu->RecalcMenu(false, true);
                $childs = [];
                foreach ($menu->arMenu as $menuItem) {
                    $childs[] = [
                        'DEPTH_LEVEL'   => 2,
                        'LINK'   => $menuItem[1],
                        'TEXT'   => $menuItem[0],
//                        '2' => $menuItem[2],
                        'PARAMS'   => $menuItem[3],
//                        '4'   => $menuItem[4],
                    ];
                }
                $item['CHILDREN'] = $childs;
            }
        }
        if(!empty($item['CHILDREN'])) {
            $outString .= $oMenu->drawMenuNextLevel($item['CHILDREN'], $item['DEPTH_LEVEL'] + 1, $item['TEXT']);
        }
        $outString .= '</div>';
    }
    $outString .= '</nav>';

    return $outString;
};

$drawMenuLevel2 = function ($menu = [], $title = '') use ($oMenu) {
    if (empty($menu)) {
        return '';
    }

    $outString = '<ul class="b-footer-nav__list-inner">';
    foreach ($menu as $index => $item) {
        $target = preg_match('~^http~', $item['LINK']) > 0 ? ' target="_blank"' : '';

        $outString .= '<li class="b-footer-nav__item">';
        $outString .= '<a href="' . $item['LINK'] . '" class="b-footer-nav__link" title="' . $item['TEXT'] . '"' . $target . '>';
        $outString .= $item['TEXT'];
        $outString .= '</a>';
        $outString .= '</li>';
    }
    $outString .= '</ul>';

    return $outString;
};

$oMenu->setMarkupFunction($drawMenuLevel1, 1);
$oMenu->setMarkupFunction($drawMenuLevel2, 2);
$oMenu->drawMenu();
