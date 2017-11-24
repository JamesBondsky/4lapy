<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @var array $arCurrentValues */

use Bitrix\Iblock\{
    IblockTable, PropertyTable
};
use Bitrix\Main\{
    Application, Loader, Localization\Loc
};

Loc::loadMessages(__FILE__);

if (!Loader::includeModule('iblock')) {
    return;
}

$arTypesEx = CIBlockParameters::GetIBlockTypes(['-' => ' ']);

$arIBlocks = [];
$params    = [
    'select' => [
        'ID',
        'NAME',
    ],
    'order'  => ['SORT' => 'ASC'],
];
$site_get  = Application::getInstance()->getContext()->getRequest()->get('site');
if (!empty($site_get)) {
    $params['filter']['SITE_ID'] = $site_get;
}
if ($arCurrentValues['IBLOCK_TYPE'] !== '-') {
    $params['filter']['TYPE'] = $arCurrentValues['IBLOCK_TYPE'];
}
$res = IblockTable::getList($params);

while ($item = $res->fetch()) {
    $arIBlocks[$item['ID']] = '[' . $item['ID'] . '] ' . $item['NAME'];
}

$arSorts      = [
    'ASC'  => Loc::getMessage('T_IBLOCK_DESC_ASC'),
    'DESC' => Loc::getMessage('T_IBLOCK_DESC_DESC'),
];
$arSortFields = [
    'ID'          => Loc::getMessage('T_IBLOCK_DESC_FID'),
    'NAME'        => Loc::getMessage('T_IBLOCK_DESC_FNAME'),
    'ACTIVE_FROM' => Loc::getMessage('T_IBLOCK_DESC_FACT'),
    'SORT'        => Loc::getMessage('T_IBLOCK_DESC_FSORT'),
    'TIMESTAMP_X' => Loc::getMessage('T_IBLOCK_DESC_FTSAMP'),
];

$arProperty_LNS = [];
$params         = [
    'select' => [
        'CODE',
        'NAME',
        'PROPERTY_TYPE',
    ],
    'filter' => [
        'ACTIVE'    => 'Y',
        'IBLOCK_ID' => $arCurrentValues['IBLOCK_ID'] ?? $arCurrentValues['ID'],
    ],
    'order'  => [
        'SORT' => 'asc',
        'NAME' => 'asc',
    ],
];
$res            = PropertyTable::getList($params);
while ($arr = $res->fetch()) {
    $arProperty[$arr['CODE']] = '[' . $arr['CODE'] . '] ' . $arr['NAME'];
    if (in_array($arr['PROPERTY_TYPE'],
                 [
                     'L',
                     'N',
                     'S',
                 ],
                 true)) {
        $arProperty_LNS[$arr['CODE']] = '[' . $arr['CODE'] . '] ' . $arr['NAME'];
    }
}

$arComponentParameters = [
    'GROUPS'     => [],
    'PARAMETERS' => [
        'AJAX_MODE'            => [],
        'IBLOCK_TYPE'          => [
            'PARENT'   => 'BASE',
            'NAME'     => Loc::getMessage('T_IBLOCK_DESC_LIST_TYPE'),
            'TYPE'     => 'LIST',
            'VALUES'   => $arTypesEx,
            'DEFAULT'  => 'news',
            'REFRESH'  => 'Y',
            'MULTIPLE' => 'Y',
        ],
        'IBLOCK_ID'            => [
            'PARENT'            => 'BASE',
            'NAME'              => Loc::getMessage('T_IBLOCK_DESC_LIST_ID'),
            'TYPE'              => 'LIST',
            'VALUES'            => $arIBlocks,
            'DEFAULT'           => '={$_REQUEST["ID"]}',
            'ADDITIONAL_VALUES' => 'Y',
            'REFRESH'           => 'Y',
            'MULTIPLE'          => 'Y',
        ],
        'NEWS_COUNT'           => [
            'PARENT'  => 'BASE',
            'NAME'    => Loc::getMessage('T_IBLOCK_DESC_LIST_CONT'),
            'TYPE'    => 'STRING',
            'DEFAULT' => '20',
        ],
        'SORT_BY1'             => [
            'PARENT'            => 'DATA_SOURCE',
            'NAME'              => Loc::getMessage('T_IBLOCK_DESC_IBORD1'),
            'TYPE'              => 'LIST',
            'DEFAULT'           => 'ACTIVE_FROM',
            'VALUES'            => $arSortFields,
            'ADDITIONAL_VALUES' => 'Y',
        ],
        'SORT_ORDER1'          => [
            'PARENT'            => 'DATA_SOURCE',
            'NAME'              => Loc::getMessage('T_IBLOCK_DESC_IBBY1'),
            'TYPE'              => 'LIST',
            'DEFAULT'           => 'DESC',
            'VALUES'            => $arSorts,
            'ADDITIONAL_VALUES' => 'Y',
        ],
        'SORT_BY2'             => [
            'PARENT'            => 'DATA_SOURCE',
            'NAME'              => Loc::getMessage('T_IBLOCK_DESC_IBORD2'),
            'TYPE'              => 'LIST',
            'DEFAULT'           => 'SORT',
            'VALUES'            => $arSortFields,
            'ADDITIONAL_VALUES' => 'Y',
        ],
        'SORT_ORDER2'          => [
            'PARENT'            => 'DATA_SOURCE',
            'NAME'              => Loc::getMessage('T_IBLOCK_DESC_IBBY2'),
            'TYPE'              => 'LIST',
            'DEFAULT'           => 'ASC',
            'VALUES'            => $arSorts,
            'ADDITIONAL_VALUES' => 'Y',
        ],
        'FILTER_NAME'          => [
            'PARENT'  => 'DATA_SOURCE',
            'NAME'    => Loc::getMessage('T_IBLOCK_FILTER'),
            'TYPE'    => 'STRING',
            'DEFAULT' => '',
        ],
        'FIELD_CODE'           => CIBlockParameters::GetFieldCode(Loc::getMessage('IBLOCK_FIELD'), 'DATA_SOURCE'),
        'PROPERTY_CODE'        => [
            'PARENT'            => 'DATA_SOURCE',
            'NAME'              => Loc::getMessage('T_IBLOCK_PROPERTY'),
            'TYPE'              => 'LIST',
            'MULTIPLE'          => 'Y',
            'VALUES'            => $arProperty_LNS,
            'ADDITIONAL_VALUES' => 'Y',
        ],
        'CHECK_DATES'          => [
            'PARENT'  => 'DATA_SOURCE',
            'NAME'    => Loc::getMessage('T_IBLOCK_DESC_CHECK_DATES'),
            'TYPE'    => 'CHECKBOX',
            'DEFAULT' => 'Y',
        ],
        'PREVIEW_TRUNCATE_LEN' => [
            'PARENT'  => 'ADDITIONAL_SETTINGS',
            'NAME'    => Loc::getMessage('T_IBLOCK_DESC_PREVIEW_TRUNCATE_LEN'),
            'TYPE'    => 'STRING',
            'DEFAULT' => '',
        ],
        'ACTIVE_DATE_FORMAT'   => CIBlockParameters::GetDateFormat(Loc::getMessage('T_IBLOCK_DESC_ACTIVE_DATE_FORMAT'),
                                                                   'ADDITIONAL_SETTINGS'),
        'SET_LAST_MODIFIED'    => [
            'PARENT'  => 'ADDITIONAL_SETTINGS',
            'NAME'    => Loc::getMessage('CP_BNL_SET_LAST_MODIFIED'),
            'TYPE'    => 'CHECKBOX',
            'DEFAULT' => 'N',
        ],
        'CACHE_TIME'           => ['DEFAULT' => 36000000],
        'CACHE_FILTER'         => [
            'PARENT'  => 'CACHE_SETTINGS',
            'NAME'    => Loc::getMessage('IBLOCK_CACHE_FILTER'),
            'TYPE'    => 'CHECKBOX',
            'DEFAULT' => 'N',
        ],
        'CACHE_GROUPS'         => [
            'PARENT'  => 'CACHE_SETTINGS',
            'NAME'    => Loc::getMessage('CP_BNL_CACHE_GROUPS'),
            'TYPE'    => 'CHECKBOX',
            'DEFAULT' => 'Y',
        ],
    ],
];
