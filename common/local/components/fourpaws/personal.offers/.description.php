<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;

$arComponentDescription = [
    'NAME'        => Loc::getMessage('COMPONENT_PERSONAL_CABINET_OFFERS_NAME'),
    'DESCRIPTION' => Loc::getMessage('COMPONENT_PERSONAL_CABINET_OFFERS_DESCRIPTION'),
    'ICON'        => '/images/icon.gif',
    'PATH'        => [
        'ID'    => 'utility',
        'CHILD' => [
            'ID'   => 'personal_offers',
            'NAME' => Loc::getMessage('MAIN_PERSONAL_CABINET_OFFERS_GROUP_NAME'),
        ],
    ],
];