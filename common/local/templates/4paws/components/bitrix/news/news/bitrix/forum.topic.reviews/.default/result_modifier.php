<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @noinspection PhpUndefinedClassInspection */
/** @var CBitrixComponentTemplate $this */
$arParams['form_index'] = $this->randString(4);

$arParams['FORM_ID']   = 'REPLIER' . $arParams['form_index'];
$arParams['jsObjName'] = 'oLHE';
$arParams['LheId']     = 'idLHE' . $arParams['form_index'];

$arParams['tabIndex'] = (int)((int)$arParams['TAB_INDEX'] > 0 ? $arParams['TAB_INDEX'] : 10);

$arParams['EDITOR_CODE_DEFAULT'] = ($arParams['EDITOR_CODE_DEFAULT'] === 'Y' ? 'Y' : 'N');
/** @noinspection PhpUndefinedVariableInspection */
$arResult['QUESTIONS'] = (is_array($arResult['QUESTIONS']) ? array_values($arResult['QUESTIONS']) : []);

if (($_REQUEST['save_product_review'] === 'Y') && $arParams['AJAX_POST'] === 'Y') {
    ob_start();
}