<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$arComponentParameters = [
    'GROUPS'     => [],
    'PARAMETERS' => [
        'VARIABLE_ALIASES' => [
            'ID' => 'ID Заказа',
        ],
        'SEF_MODE'         => [
            'personal'     => [
                'NAME'      => 'Профиль',
                'DEFAULT'   => '',
                'VARIABLES' => [],
            ],
            'address'      => [
                'NAME'      => 'Адреса доставки',
                'DEFAULT'   => 'address/',
                'VARIABLES' => [],
            ],
            'bonus'        => [
                'NAME'      => 'Бонусы',
                'DEFAULT'   => 'bonus/',
                'VARIABLES' => [],
            ],
            'orders'       => [
                'NAME'      => 'Последние заказы',
                'DEFAULT'   => 'orders/',
                'VARIABLES' => [],
            ],
            'order_detail' => [
                'NAME'      => 'Детальный заказ',
                'DEFAULT'   => 'orders/#ID#/',
                'VARIABLES' => ['ID'],
            ],
            'pets'         => [
                'NAME'      => 'Мои питомцы',
                'DEFAULT'   => 'pets/',
                'VARIABLES' => [],
            ],
            'referal'      => [
                'NAME'      => 'Реферальная программа',
                'DEFAULT'   => 'referal/',
                'VARIABLES' => [],
            ],
            'subscribe'    => [
                'NAME'      => 'Подписка на доставку',
                'DEFAULT'   => 'subscribe/',
                'VARIABLES' => [],
            ],
            'top'          => [
                'NAME'      => 'Топ 10 товаров',
                'DEFAULT'   => 'top/',
                'VARIABLES' => [],
            ],
        ],
    ],
];