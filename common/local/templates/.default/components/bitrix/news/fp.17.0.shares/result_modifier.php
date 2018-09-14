<?php

/** @var array $arParams */
/** @var array $arResult */
/** @var CBitrixComponent $component */
/** @var CBitrixComponentTemplate $this */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
use FourPaws\App\MainTemplate;
use Bitrix\Main\Application as BitrixApplication;

$component = $this->getComponent();
if(isset($arResult['VARIABLES']['ELEMENT_CODE'])){

    $arFilter = array(
        'IBLOCK_ID' => $arParams['IBLOCK_ID'],
        'CODE' => 'TYPE',
        'ACTIVE' => 'Y'
    );
    $arPropType = CIBlockProperty::GetList([], $arFilter)->GetNext();

    $MainTemplate = MainTemplate::getInstance(BitrixApplication::getInstance()
        ->getContext());
    $arExitingCategories = $MainTemplate->getSharesFilterDirs();

    if(in_array($arResult['VARIABLES']['ELEMENT_CODE'], $arExitingCategories)){
        $arResult['VARIABLES']['SECTION_CODE'] = $arResult['VARIABLES']['ELEMENT_CODE'];
        unset($arResult['VARIABLES']['ELEMENT_CODE']);
        include(__DIR__.'/section.php');
    }
}
