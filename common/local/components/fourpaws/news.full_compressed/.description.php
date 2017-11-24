<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;

$arComponentDescription = [
    'NAME'        => Loc::getMessage('COMPONENT_NEWS_FULL_COMPRESSED_NAME'),
    'DESCRIPTION' => Loc::getMessage('COMPONENT_NEWS_FULL_COMPRESSED_DESCRIPTION'),
    'ICON'        => '/images/icon.gif',
    'PATH'        => [
        'ID'    => 'utility',
        'CHILD' => [
            'ID'   => 'news_full_compressed',
            'NAME' => Loc::getMessage('MAIN_NEWS_FULL_COMPRESSED_GROUP_NAME'),
        ],
    ],
];
