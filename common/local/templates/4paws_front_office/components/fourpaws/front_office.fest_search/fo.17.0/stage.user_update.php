<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @global CMain                                     $APPLICATION
 * @var array                                        $arParams
 * @var array                                        $arResult
 * @var CBitrixComponent $component
 * @var CBitrixComponentTemplate                     $this
 * @var string                                       $templateName
 * @var string                                       $componentPath
 */

$jsonResult = [
    'success' => 'N',
    'message' => '',
];
if ($arResult['IS_UPDATED']) {
    $jsonResult['message']     = 'Информация об участнике обновлена';
    $jsonResult['success']     = 'Y';
} else {
    $jsonResult['message'] = 'Произошла ошибка; ' . $arResult['UPDATE_ERROR'];
}

$GLOBALS['APPLICATION']->RestartBuffer();
header('Content-Type: application/json');
echo \Bitrix\Main\Web\Json::encode($jsonResult);
die();
