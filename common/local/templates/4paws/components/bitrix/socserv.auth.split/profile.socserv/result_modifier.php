<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

use FourPaws\App\Application;
use FourPaws\EcommerceBundle\Enum\DataLayer;
use FourPaws\EcommerceBundle\Service\DataLayerService;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

if (!is_array($arResult['DB_SOCSERV_USER'])) {
    $arResult['DB_SOCSERV_USER'] = [];
}
if (!is_array($arResult['AUTH_SERVICES'])) {
    $arResult['AUTH_SERVICES'] = [];
}
$arResult['AUTH_SERVICES'] = array_merge($arResult['DB_SOCSERV_USER'], $arResult['AUTH_SERVICES']);

$arReplaceNames = [
    'Facebook'      => 'Фейсбук',
    'Odnoklassniki' => 'Одноклассники',
    'VKontakte'     => 'ВКонтакте',
];


$dataLayerService = Application::getInstance()->getContainer()->get(DataLayerService::class);

foreach ($arResult['AUTH_SERVICES'] as &$service) {
    $service['FORM_HTML']['ON_CLICK'] = str_replace(
        'onclick="',
        'onclick="' . str_replace(
            '"',
            '\'',
            $dataLayerService->renderBindSocials(DataLayer::SOCIAL_SERVICE_MAP[$service['ID']] ?? '')
        ),
        $service['FORM_HTML']['ON_CLICK']
    );

    if (is_numeric($service['ID'])) {
        $service['SOCSERV_NAME'] = $arReplaceNames[$service['EXTERNAL_AUTH_ID']];
        $service['SOCSERV_CODE'] = ToLower($service['EXTERNAL_AUTH_ID']);
        $service['ACTIVE'] = true;
    } else {
        if (array_key_exists($service['NAME'], $arReplaceNames)) {
            $service['NAME'] = $arReplaceNames[$service['NAME']];
        }
        $service['SOCSERV_NAME'] = $service['NAME'];
        $service['SOCSERV_CODE'] = $service['ICON'];
    }

    switch ($service['SOCSERV_CODE']) {
        case 'vkontakte':
            $service['ICON'] = 'vk';
            $service['ICON_DECORATOR'] = [
                'CODE'   => 'vk-social',
                'WIDTH'  => 29,
                'HEIGHT' => 17,
            ];
            break;
        case 'odnoklassniki':
            $service['ICON'] = 'ok';
            $service['ICON_DECORATOR'] = [
                'CODE'   => 'ok',
                'WIDTH'  => 14,
                'HEIGHT' => 23,
            ];
            break;
        case 'facebook':
            $service['ICON'] = 'facebook';
            $service['ICON_DECORATOR'] = [
                'CODE'   => 'facebook',
                'WIDTH'  => 12,
                'HEIGHT' => 22,
            ];
            break;
    }
}
unset($service);
