<?php

use Bitrix\Iblock\Component\Tools;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @global CMain $APPLICATION */
if (isset($arParams['USE_FILTER']) && $arParams['USE_FILTER'] === 'Y') {
    $arParams['FILTER_NAME'] = trim($arParams['FILTER_NAME']);
    if ($arParams['FILTER_NAME'] === '' || !preg_match('/^[A-Za-z_][A-Za-z01-9_]*$/', $arParams['FILTER_NAME'])) {
        $arParams['FILTER_NAME'] = 'arrFilter';
    }
} else {
    $arParams['FILTER_NAME'] = '';
}

//default gifts

$smartBase = ($arParams['SEF_URL_TEMPLATES']['section'] ?: '#SECTION_ID#/');
$arDefaultUrlTemplates404 = [
    'sections'     => '',
    'section'      => '#SECTION_ID#/',
    'element'      => '#SECTION_ID#/#ELEMENT_ID#/',
    'compare'      => 'compare.php?action=COMPARE',
    'smart_filter' => $smartBase . 'filter/#SMART_FILTER_PATH#/apply/',
];

$arDefaultVariableAliases404 = [];

$arDefaultVariableAliases = [];

$arComponentVariables = [
    'SECTION_ID',
    'SECTION_CODE',
    'ELEMENT_ID',
    'ELEMENT_CODE',
    'action',
];

$arVariables = [];

$engine = new CComponentEngine($this);
try {
    if (Loader::includeModule('iblock')) {
        $engine->addGreedyPart('#SECTION_CODE_PATH#');
        $engine->addGreedyPart('#SMART_FILTER_PATH#');
        $engine->setResolveCallback(['CIBlockFindTools', 'resolveComponentEngine']);
    }
} catch (LoaderException $e) {
}
$arUrlTemplates = CComponentEngine::makeComponentUrlTemplates(
    $arDefaultUrlTemplates404,
    $arParams['SEF_URL_TEMPLATES']
);
$arVariableAliases = CComponentEngine::makeComponentVariableAliases(
    $arDefaultVariableAliases404,
    $arParams['VARIABLE_ALIASES']
);

$componentPage = $engine->guessComponentPath(
    $arParams['SEF_FOLDER'],
    $arUrlTemplates,
    $arVariables
);

if ($componentPage === 'smart_filter') {
    $componentPage = 'section';
}

if (!$componentPage && isset($_REQUEST['q'])) {
    $componentPage = 'search';
}

$b404 = false;
if (!$componentPage) {
    $componentPage = 'sections';
    $b404 = true;
}

if ($componentPage === 'section') {
    if (isset($arVariables['SECTION_ID'])) {
        $b404 |= ((int)$arVariables['SECTION_ID'] . '' !== $arVariables['SECTION_ID']);
    } else {
        $b404 |= !isset($arVariables['SECTION_CODE']);
    }
}

if ($b404 && CModule::IncludeModule('iblock')) {
    $folder404 = str_replace("\\", '/', $arParams['SEF_FOLDER']);
    if ($folder404 !== '/') {
        $folder404 = '/' . trim($folder404, "/ \t\n\r\0\x0B") . '/';
    }
    if (substr($folder404, -1) === '/') {
        $folder404 .= 'index.php';
    }

    if ($folder404 !== $APPLICATION->GetCurPage(true)) {
        Tools::process404(
            '',
            $arParams['SET_STATUS_404'] === 'Y',
            $arParams['SET_STATUS_404'] === 'Y',
            $arParams['SHOW_404'] === 'Y',
            $arParams['FILE_404']
        );
    }
}

CComponentEngine::initComponentVariables($componentPage, $arComponentVariables, $arVariableAliases, $arVariables);
$arResult = [
    'FOLDER'        => $arParams['SEF_FOLDER'],
    'URL_TEMPLATES' => $arUrlTemplates,
    'VARIABLES'     => $arVariables,
    'ALIASES'       => $arVariableAliases,
];

$this->IncludeComponentTemplate($componentPage);
