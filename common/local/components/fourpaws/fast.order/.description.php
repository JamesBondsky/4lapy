<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;

$arComponentDescription = [
    'NAME'        => Loc::getMessage('COMPONENT_FAST_ORDER_NAME'),
    'DESCRIPTION' => Loc::getMessage('COMPONENT_FAST_ORDER_DESCRIPTION'),
    'ICON'        => '/images/icon.gif',
    'PATH'        => [
        'ID'    => 'utility',
        'CHILD' => [
            'ID'   => 'order',
            'NAME' => Loc::getMessage('MAIN_FAST_ORDER_GROUP_NAME'),
        ],
    ],
];