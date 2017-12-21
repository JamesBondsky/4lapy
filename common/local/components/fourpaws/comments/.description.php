<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;

$arComponentDescription = [
    'NAME'        => Loc::getMessage('COMPONENT_COMMENTS_NAME'),
    'DESCRIPTION' => Loc::getMessage('COMPONENT_COMMENTS_DESCRIPTION'),
    'ICON'        => '/images/icon.gif',
    'PATH'        => [
        'ID'    => 'utility',
    ],
];
