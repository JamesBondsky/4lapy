<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

$aMenuLinks = [
    [
        'Профиль',
        '/personal/',
    ],
    [
        'Адреса доставки',
        '/personal/address/',
    ],
    [
        'Бонусы',
        '/personal/bonus/',
    ],
    [
        'Последние заказы',
        '/personal/orders/',
    ],
    [
        'Мои питомцы',
        '/personal/pets/',
    ],
    [
        'Реферальная программа',
        '/personal/referral/',
        array(),
        array(),
        "\\in_array(30, \$USER->GetUserGroupArray())"
    ],
    [
        'Подписка на доставку',
        '/personal/subscribe/',
    ],
    [
        'Топ 10 товаров',
        '/personal/top/',
    ],
];
