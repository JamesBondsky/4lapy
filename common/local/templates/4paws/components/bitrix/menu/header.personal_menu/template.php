<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\Decorators\SvgDecorator;
use FourPaws\Menu\Helper\TreeMenuBuilder;

if (empty($arResult)) {
    return;
}

$oMenu = new TreeMenuBuilder($arResult, $arParams);

$notSeenCouponsCount = $arParams['NOT_SEEN_COUPONS'];

$drawMenuLevel1 = function ($menu = [], $title = '') use ($oMenu, $notSeenCouponsCount) {
    if (empty($menu)) {
        return '';
    }
    
    $outString = '<div class="b-popover b-popover--person js-popover js-content-popover-mobile-header"><div class="b-person"><ul class="b-link-block">';
    foreach ($menu as $index => $item) {
        $isPersonalOffersItem = strpos($item['LINK'], '/personal-offers') !== false || strpos($item['TEXT'], 'Персональные предложения') !== false; // т.к. никакого id нет, на всякий случай двойная проверка
        $outString .= '<li class="b-link-block__item">';
        $outString .= '<span class="b-icon">';
        $outString .= new SvgDecorator($item['PARAMS']['icon'], 16, 16);
        $outString .= '</span>';
        $outString .= '<a href="' . $item['LINK'] . '" class="b-link-block__link ' . ($notSeenCouponsCount && $isPersonalOffersItem ? 'b-link-block__link--count' : '') . '" title="' . $item['TEXT'] . '">';
        $outString .= $item['TEXT'];
        if ($notSeenCouponsCount && $isPersonalOffersItem) {
            $outString .= '<span class="b-link-block__count">' . $notSeenCouponsCount . '</span>';
        }
        $outString .= '</a>';
        $outString .= '</li>';
    }
    $outString .= '</ul></div></div>';
    
    return $outString;
};

$oMenu->setMarkupFunction($drawMenuLevel1, 1);
$oMenu->drawMenu();
