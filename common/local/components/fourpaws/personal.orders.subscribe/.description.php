<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;

$arComponentDescription = [
    'NAME' => Loc::getMessage('FOURPAWS.PERSONAL_ORDERS_SUBSCRIBE.COMPONENT_NAME'),
    'DESCRIPTION' => Loc::getMessage('FOURPAWS.PERSONAL_ORDERS_SUBSCRIBE.COMPONENT_DESCRIPTION'),
    'ICON' => '/images/icon.gif',
    'PATH' => [
        'ID' => 'fourpaws',
		'NAME' => Loc::getMessage('FOURPAWS.COMPONENTS'),
        'CHILD' => [
            'ID' => 'personal',
            'NAME' => Loc::getMessage('FFOURPAWS.PERSONAL.GROUP_NAME'),
        ],
    ],
];
