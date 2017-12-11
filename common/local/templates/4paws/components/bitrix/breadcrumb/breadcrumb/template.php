<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @global CMain $APPLICATION
 */

//delayed function must return a string
if (empty($arResult)) {
    return '';
}

$return = '<nav class="b-breadcrumbs"><ul class="b-breadcrumbs__list">';

foreach ($arResult as $item) {
    $return .= '<li class="b-breadcrumbs__item">
    <a class="b-breadcrumbs__link"
       href="' . $item['LINK'] . '"
       title="' . $item['TITLE'] . '">' . $item['TITLE'] . '</a>
</li>';
}

$return .= '</ul></nav>';

return $return;
