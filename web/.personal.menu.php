<?php

global $optId;

use Bitrix\Main\GroupTable;
use FourPaws\App\Application;
use FourPaws\Enum\UserGroup;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;

global $optId, $isAuth, $isAvatarAuth, $USER;
$isAuth = $USER->IsAuthorized();
$isAvatarAuth = Application::getInstance()->getContainer()->get(CurrentUserProviderInterface::class)->isAvatarAuthorized();
$optId = (int)GroupTable::query()->setFilter(['STRING_ID' => UserGroup::OPT_CODE])->setLimit(1)->setSelect(['ID'])->setCacheTtl(360000)->exec()->fetch()['ID'];
if ($optId === 0) {
    $optId = UserGroup::OPT_ID;
}

$aMenuLinks = [
    [
        'Мои заказы',
        '/personal/orders/',
        [],
        ['icon' => 'icon-order'],
        "\$isAuth && !\$isAvatarAuth",
    ],
    [
        'Адреса доставки',
        '/personal/address/',
        [],
        ['icon' => 'icon-delivery-header'],
        "\$isAuth && !\$isAvatarAuth",
    ],
    [
        'Подписка на доставку',
        '/personal/subscribe/',
        [],
        ['icon' => 'icon-delivery-menu'],
        "\$isAuth",
    ],
    [
        'Мои питомцы',
        '/personal/pets/',
        [],
        ['icon' => 'icon-pet'],
        "\$isAuth && !\$isAvatarAuth",
    ],
    [
        'Бонусы',
        '/personal/bonus/',
        [],
        ['icon' => 'icon-bonus'],
        "\$isAuth && !\$isAvatarAuth",
    ],
    [
        'Реферальная программа',
        '/personal/referral/',
        [],
        ['icon' => 'icon-menu-referal'],
        "\$isAuth && \\in_array(30, \$USER->GetUserGroupArray()) && !\$isAvatarAuth",
    ],
    [
        'Профиль',
        '/personal/',
        [],
        ['icon' => 'icon-profile'],
        "\$isAuth && !\$isAvatarAuth",
    ],
    [
        'Топ 10 товаров',
        '/personal/top/',
        [],
        ['icon' => 'icon-empty-star'],
        "\$isAuth && !\$isAvatarAuth",
    ],
    [
        'Выход',
        '?logout=yes',
        [],
        ['icon' => 'icon-exit'],
        "\$isAuth && !\$isAvatarAuth",
    ],
    [
        'Вернуться',
        '/front-office/avatar/logout.php',
        [],
        ['icon' => 'icon-exit'],
        "\$isAuth && \$isAvatarAuth",
    ],
];
