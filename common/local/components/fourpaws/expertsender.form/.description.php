<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;

$arComponentDescription = [
    'NAME'        => Loc::getMessage('COMPONENT_EXPERTSENDER_FORM_FORM_NAME'),
    'DESCRIPTION' => Loc::getMessage('COMPONENT_EXPERTSENDER_FORM_FORM_DESCRIPTION'),
    'ICON'        => '/images/icon.gif',
    'PATH'        => [
        'ID'    => 'utility',
        'CHILD' => [
            'ID'   => 'expertesender_form',
            'NAME' => Loc::getMessage('MAIN_EXPERTSENDER_GROUP_NAME'),
        ],
    ],
];
