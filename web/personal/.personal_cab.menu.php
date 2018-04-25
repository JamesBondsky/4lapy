<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

$aMenuLinks = [
    [
        'Профиль',
        '/personal/index.php',
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
        [],
        [],
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
