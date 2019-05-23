<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

if (\is_array($arParams['~AUTH_SERVICES']) && !empty($arParams['~AUTH_SERVICES'])) {
    foreach ($arParams['~AUTH_SERVICES'] as &$service) {
        switch ($service['ICON']) {
            case 'vkontakte':
                $service['ICON']           = 'vk';
                $service['ICON_DECORATOR'] = [
                    'CODE'   => 'vk-social',
                    'WIDTH'  => 29,
                    'HEIGHT' => 17,
                ];
                break;
            case 'odnoklassniki':
                $service['ICON']           = 'ok';
                $service['ICON_DECORATOR'] = [
                    'CODE'   => 'ok',
                    'WIDTH'  => 14,
                    'HEIGHT' => 23,
                ];
                break;
            case 'facebook':
                $service['ICON']           = 'facebook';
                $service['ICON_DECORATOR'] = [
                    'CODE'   => 'facebook',
                    'WIDTH'  => 12,
                    'HEIGHT' => 22,
                ];
                break;
        }
    }
    unset($service);
}
