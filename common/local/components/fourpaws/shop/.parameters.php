<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$arComponentParameters = [
    'GROUPS'     => [],
    'PARAMETERS' => [
        'VARIABLE_ALIASES' => [
            'ID' => 'ID магазина',
        ],
        'SEF_MODE'         => [
            'list'     => [
                'NAME'      => 'Магазин',
                'DEFAULT'   => '',
                'VARIABLES' => [],
            ],
            'detail'     => [
                'NAME'      => 'Магазин детально',
                'DEFAULT'   => '',
                'VARIABLES' => [],
            ],
        ],
    ],
];