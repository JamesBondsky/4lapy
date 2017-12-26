<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$arComponentParameters = [
    'PARAMETERS' => [
        'LOCATION_CODE' => [
            'NAME'     => Loc::getMessage('CITY_PHONE_LOCATION_CODE_PARAMETER'),
            'PARENT'   => 'BASE',
            'TYPE'     => 'STRING',
            'MULTIPLE' => 'N',
        ],
        'CACHE_TIME' => ['DEFAULT' => 36000000],
    ],
];
