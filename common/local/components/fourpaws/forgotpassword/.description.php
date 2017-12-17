<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;

$arComponentDescription = [
    'NAME'        => Loc::getMessage('COMPONENT_FORGOT_PASSWORD_FORM_NAME'),
    'DESCRIPTION' => Loc::getMessage('COMPONENT_FORGOT_PASSWORD_FORM_DESCRIPTION'),
    'ICON'        => '/images/icon.gif',
    'PATH'        => [
        'ID'    => 'utility',
        'CHILD' => [
            'ID'   => 'user',
            'NAME' => Loc::getMessage('MAIN_USER_GROUP_NAME'),
        ],
    ],
];
