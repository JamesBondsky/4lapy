<?php

use Bitrix\Main\Application;
use Bitrix\Main\Web\Uri;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$arResult['TABS'] = [
    'all' => [
        'NAME' => 'Все',
        'COUNT' => $arResult['COUNT']
    ],
    'active' => [
        'NAME' => 'Активные',
        'COUNT' => $arResult['COUNT_ACTIVE']
    ],
    'moderated' => [
        'NAME' => 'На модерации',
        'COUNT' => $arResult['COUNT_MODERATE']
    ]
];
/** @noinspection PhpUnhandledExceptionInspection */
$request                   = Application::getInstance()->getContext()->getRequest();
$requestUri                = $request->getRequestUri();
foreach ($arResult['TABS'] as $code => &$tab) {
    $uri = new Uri($requestUri);
    $uri->deleteParams(['referral_type']);
    if($code !== 'all') {
        $uri->addParams(['referral_type' => $code]);
    }
    $tab['URI'] = $uri->getUri();
}
