<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;

$arComponentDescription = [
    'NAME'        => Loc::getMessage('COMPONENT_PERSONAL_CABINET_BONUS_NAME'),
    'DESCRIPTION' => Loc::getMessage('COMPONENT_PERSONAL_CABINET_BONUS_DESCRIPTION'),
    'ICON'        => '/images/icon.gif',
    'PATH'        => [
        'ID'    => 'utility',
        'CHILD' => [
            'ID'   => 'personal_bonus',
            'NAME' => Loc::getMessage('MAIN_PERSONAL_CABINET_BONUS_GROUP_NAME'),
        ],
    ],
];