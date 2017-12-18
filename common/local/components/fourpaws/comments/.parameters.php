<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Localization\Loc;

$params = [
    'select' => ['ID', 'NAME' => 'LANG.NAME']
];
$hlBlocks = [];
try {
    $res = HighloadBlockTable::getList($params);
    while ($item = $res->fetch()){
        $hlBlocks[$item['ID']] = '['.$item['ID'].'] '.$item['NAME'];
    }
} catch (ArgumentException $e) {
}

$arComponentParameters = [
    'GROUPS'     => [],
    'PARAMETERS' => [
        'HL_ID' => [
            'PARENT'   => 'BASE',
            'NAME'     => Loc::getMessage('COMMENTS_HL_ID'),
            'TYPE'              => 'LIST',
            'VALUES'            => $hlBlocks,
        ],
        'OBJECT_ID'          => [
            'PARENT'   => 'BASE',
            'NAME'     => Loc::getMessage('COMMENTS_OBJECT_ID'),
            'TYPE'     => 'STRING',
        ],
        'ITEMS_COUNT'           => [
            'PARENT'  => 'BASE',
            'NAME'    => Loc::getMessage('COMMENTS_ITEMS_COUNT'),
            'TYPE'    => 'STRING',
            'DEFAULT' => '5',
        ],
        'TYPE'           => [
            'PARENT'  => 'BASE',
            'NAME'    => Loc::getMessage('COMMENTS_TYPE'),
            'TYPE'    => 'STRING',
            'DEFAULT' => 'iblock',
        ],
        'ACTIVE_DATE_FORMAT'   => CIBlockParameters::GetDateFormat(
            Loc::getMessage('COMMENTS_ACTIVE_DATE_FORMAT'),
            'ADDITIONAL_SETTINGS'
        ),
        'SORT_DESC'         => [
            'PARENT'  => 'CACHE_SETTINGS',
            'NAME'    => Loc::getMessage('COMMENTS_SORT_DESC'),
            'TYPE'    => 'CHECKBOX',
            'DEFAULT' => 'Y',
        ],
    ],
];