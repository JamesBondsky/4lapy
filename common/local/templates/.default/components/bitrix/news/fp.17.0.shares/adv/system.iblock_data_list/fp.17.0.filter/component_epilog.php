<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * Фильтр списка акций по видам питомцев в разделе Акции
 * component_epilog.php
 *
 * @updated: 29.12.2017
 */

use FourPaws\App\MainTemplate;
use Bitrix\Main\Application as BitrixApplication;

//\Bitrix\Main\Localization\Loc::loadMessages(__FILE__);

$sSelectedValue = isset($arParams['~SELECTED_VALUE']) ? trim($arParams['~SELECTED_VALUE']) : '';
$arParams['FILTER_PROPERTY_CODE'] = !empty($arParams['FILTER_PROPERTY_CODE']) ? $arParams['FILTER_PROPERTY_CODE'] : 'TYPE';

/**
 * По задаче LP23-254 - убрать by_pet в фильтрах
 * Детальные акции перелинковали на .html
 * Уже проиндексированные без расширения перенаправляются на соответствующие страницы
 */

$filterCategoryCode = $arParams['~SELECTED_VALUE'];
if(!isset($arResult['PRINT_LIST'][$filterCategoryCode])){
    /** @var MainTemplate $mainTemplate */
    /** @noinspection PhpUnhandledExceptionInspection */
    $mainTemplate = MainTemplate::getInstance(
        BitrixApplication::getInstance()->getContext()
    );
    $uri = $mainTemplate->getUri();

    $page = $uri->getPath();
    $query = $uri->getQuery();
    if(substr($page, -1) === '/'){
        $page = substr_replace($page, '', -1);
    }
    $destinationPage = $page . '.html';
    if(strlen($query)){
        $destinationPage .= '?' . $query;
    }
    LocalRedirect($destinationPage, false, '302 Found');
}

?><div class="b-category-nav__wrapper">
    <div class="b-category-nav"><?php
        if (count($arResult['PRINT_LIST']) > 1) {
            ?><ul><?php
            foreach ($arResult['PRINT_LIST'] as $arItem) {
                if ($arItem['URL'] && $arItem['XML_ID'] != $sSelectedValue) {
                    echo '<li><a href="'.$arItem['URL'].'">'.$arItem['NAME'].'</a></li>';
                } else {
                    echo '<li><span>'.$arItem['NAME'].'</span></li>';
                }
            }
        }
    ?></ul>
  </div>
</div><?php

// генерация внешнего фильтра
if (strlen($sSelectedValue) && isset($arParams['ELEMENT_FILTER_NAME']) && strlen($arParams['ELEMENT_FILTER_NAME'])) {
    if (!isset($GLOBALS[$arParams['ELEMENT_FILTER_NAME']])) {
        $GLOBALS[$arParams['ELEMENT_FILTER_NAME']] = [];
    }
    $GLOBALS[$arParams['ELEMENT_FILTER_NAME']]['PROPERTY_'.$arParams['FILTER_PROPERTY_CODE']] = $sSelectedValue;
}
