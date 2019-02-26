<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

use Bitrix\Main\GroupTable;
use FourPaws\Enum\UserGroup;

global $optId, $USER;
$optId = (int)GroupTable::query()->setFilter(['STRING_ID' => UserGroup::OPT_CODE])->setLimit(1)->setSelect(['ID'])->setCacheTtl(360000)->exec()->fetch()['ID'];
if($optId === 0){
    $optId = UserGroup::OPT_ID;
}

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
        'Мои заказы',
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
        "\\in_array(\$GLOBALS['optId'], \$USER->GetUserGroupArray())"
    ],
    [
        'Подписка на доставку',
        '/personal/subscribe/',
    ],
];

global $USER;
if ($USER->IsAdmin())
{
    $aMenuLinks[] = [
        'Копи марки',
        '/personal/piggy-bank/',
    ];

}

$aMenuLinks[] = [
    'Топ 10 товаров',
    '/personal/top/',
];