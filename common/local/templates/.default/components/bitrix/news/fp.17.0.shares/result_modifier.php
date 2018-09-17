<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @var array $arParams
  * @var array $arResult
  * @var CBitrixComponent $component
  * @var CBitrixComponentTemplate $this */

use FourPaws\App\MainTemplate;
use Bitrix\Main\Application as BitrixApplication;

if(isset($arResult['VARIABLES']['SECTION_CODE'])){

    /** @var MainTemplate $mainTemplate */
    /** @noinspection PhpUnhandledExceptionInspection */
    $mainTemplate = MainTemplate::getInstance(
        BitrixApplication::getInstance()->getContext()
    );

    $exitingCategories = $mainTemplate->getSharesFilterDirs();
    if(!in_array($arResult['VARIABLES']['SECTION_CODE'], $exitingCategories, true)){
        $destinationPage = substr_replace($mainTemplate->getPath(), '.html', -1);
        LocalRedirect($destinationPage, false, '301 Moved Permanently');
    }
}