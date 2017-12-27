<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * Главное меню сайта
 *
 * @updated: 26.12.2017
 */

if (!$arResult['MENU_TREE']) {
    return;
}

//_log_array($arResult);

$funcWalkRecursive = function($arData, $funcSelf) {
    foreach ($arData as &$arItem) {
        $arItem['_TEXT_'] = $arItem['NAME'];
        $arItem['_URL_'] = strlen($arItem['URL']) ? $arItem['URL'] : 'javascript:void(0);';
        $arItem['_LINK_ATTR1_'] = '';
        if ($arItem['TARGET_BLANK']) {
            $arItem['_LINK_ATTR1_'] .= ' target="_blank"';
        }
        $arItem['_LINK_ATTR2_'] = $arItem['_LINK_ATTR1_'];
        $arItem['_LINK_ATTR2_'] .= ' title="'.$arItem['NAME'].'"';
        if($arItem['NESTED']) {
            $arItem['NESTED'] = $funcSelf($arItem['NESTED'], $funcSelf);
        }
    }
    unset($arItem);

    return $arData;    
};
$arResult['MENU_TREE'] = $funcWalkRecursive($arResult['MENU_TREE'], $funcWalkRecursive);
