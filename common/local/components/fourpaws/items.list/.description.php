<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;

$arComponentDescription = [
    'NAME'        => Loc::getMessage('COMPONENT_ITEMS_LIST_NAME'),
    'DESCRIPTION' => Loc::getMessage('COMPONENT_ITEMS_LIST_DESCRIPTION'),
    'ICON'        => '/images/icon.gif',
    'PATH'        => [
        'ID'    => 'utility',
        'CHILD' => [
            'ID'   => 'items_list',
            'NAME' => Loc::getMessage('MAIN_ITEMS_LIST_GROUP_NAME'),
        ],
    ],
];
