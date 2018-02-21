<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;

$arComponentDescription = [
    'NAME'        => Loc::getMessage('COMPONENT_CATALOG_OFTEN_SEEK_NAME'),
    'DESCRIPTION' => Loc::getMessage('COMPONENT_CATALOG_OFTEN_SEEK_DESCRIPTION'),
    'ICON'        => '/images/icon.gif',
    'PATH'        => [
        'ID'    => 'utility',
        'CHILD' => [
            'ID'   => 'catalog_often_seek',
            'NAME' => Loc::getMessage('MAIN_CATALOG_OFTEN_SEEK_GROUP_NAME'),
        ],
    ],
];
