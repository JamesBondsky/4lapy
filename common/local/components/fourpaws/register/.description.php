<?php

use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$arComponentDescription = [
    'NAME'        => Loc::getMessage('COMPONENT_REGISTER_FORM_NAME'),
    'DESCRIPTION' => Loc::getMessage('COMPONENT_REGISTER_FORM_DESCRIPTION'),
    'SORT'        => 10010,
    'CACHE_PATH'  => 'Y',
    'PATH'        => [
        'ID'    => 'utility',
        'CHILD' => [
            'ID'   => 'user',
            'NAME' => Loc::getMessage('MAIN_USER_GROUP_NAME'),
        ],
    ],
];