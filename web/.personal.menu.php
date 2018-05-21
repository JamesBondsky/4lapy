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
        "\$GLOBALS['isAuth'] && !\$GLOBALS['isAvatarAuth']",
    ],
    [
        'Адреса доставки',
        '/personal/address/',
        [],
        ['icon' => 'icon-delivery-header'],
        "\$GLOBALS['isAuth'] && !\$GLOBALS['isAvatarAuth']",
    ],
    [
        'Подписка на доставку',
        '/personal/subscribe/',
        [],
        ['icon' => 'icon-delivery-menu'],
        "\$GLOBALS['isAuth']",
    ],
    [
        'Мои питомцы',
        '/personal/pets/',
        [],
        ['icon' => 'icon-pet'],
        "\$GLOBALS['isAuth'] && !\$GLOBALS['isAvatarAuth']",
    ],
    [
        'Бонусы',
        '/personal/bonus/',
        [],
        ['icon' => 'icon-bonus'],
        "\$GLOBALS['isAuth'] && !\$GLOBALS['isAvatarAuth']",
    ],
    [
        'Реферальная программа',
        '/personal/referral/',
        [],
        ['icon' => 'icon-menu-referal'],
        "\$GLOBALS['isAuth'] && \\in_array(!\$GLOBALS['optId'], \$USER->GetUserGroupArray()) && !\$GLOBALS['isAvatarAuth']",
    ],
    [
        'Профиль',
        '/personal/',
        [],
        ['icon' => 'icon-profile'],
        "\$GLOBALS['isAuth'] && !\$GLOBALS['isAvatarAuth']",
    ],
    [
        'Топ 10 товаров',
        '/personal/top/',
        [],
        ['icon' => 'icon-empty-star'],
        "\$GLOBALS['isAuth'] && !\$GLOBALS['isAvatarAuth']",
    ],
    [
        'Выход',
        '?logout=yes',
        [],
        ['icon' => 'icon-exit'],
        "\$GLOBALS['isAuth'] && !\$GLOBALS['isAvatarAuth']",
    ],
    [
        'Вернуться',
        '/front-office/avatar/logout.php',
        [],
        ['icon' => 'icon-exit'],
        "\$GLOBALS['isAuth'] && \$GLOBALS['isAvatarAuth']",
    ],
];
