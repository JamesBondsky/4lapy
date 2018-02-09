<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var FourPawsFrontOfficeCardRegistrationComponent $component
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $componentPath
 */

$jsonResult = [
    'success' => 'N',
    'message' => ''
];
if ($arResult['AUTH_ACTION_SUCCESS'] === 'Y') {
    $jsonResult['message'] = 'Авторизация произведена успешно. Вы будете перенаправлены на главную страницу сайта.';
    $jsonResult['success'] = 'Y';
    $proto = \Bitrix\Main\Application::getInstance()->getContext()->getRequest()->isHttps() ? 'https' : 'http';
    $jsonResult['redirectUrl'] = $proto.'://'.SITE_SERVER_NAME;
} else {
    if (!empty($arResult['ERROR']['EXEC']['authFailed'])) {
        $jsonResult['message'] = 'Невозможно авторизоваться под указанным пользователем';
    } elseif (!empty($arResult['ERROR']['EXEC']['canNotLogin'])) {
        $jsonResult['message'] = 'Невозможно авторизоваться под указанным пользователем';
    } elseif (!empty($arResult['ERROR']['EXEC']['emptyUserId'])) {
        $jsonResult['message'] = 'Идентификатор пользователя не задан, либо задан некорректно';
    }
}

$GLOBALS['APPLICATION']->RestartBuffer();
header('Content-Type: application/json');
echo \Bitrix\Main\Web\Json::encode($jsonResult);
die();