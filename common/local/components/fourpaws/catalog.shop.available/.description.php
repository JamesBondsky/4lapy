<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;

$arComponentDescription = [
    'NAME'        => Loc::getMessage('COMPONENT_CATALOG_SHOP_AVAILABLE_NAME'),
    'DESCRIPTION' => Loc::getMessage('COMPONENT_CATALOG_SHOP_AVAILABLE_DESCRIPTION'),
    'ICON'        => '/images/icon.gif',
    'PATH'        => [
        'ID'    => 'utility',
        'CHILD' => [
            'ID'   => 'catalog',
            'NAME' => Loc::getMessage('MAIN_CATALOG_GROUP_NAME'),
        ],
    ],
];
