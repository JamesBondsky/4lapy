<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

use FourPaws\App\Application as App;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserAuthorizationInterface;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @noinspection PhpUnhandledExceptionInspection */
$container = App::getInstance()->getContainer();
/** @noinspection PhpUnhandledExceptionInspection */
if ($container->get(UserAuthorizationInterface::class)->isAuthorized()) {
    /** @noinspection PhpUnhandledExceptionInspection */
    $user                 = $container->get(CurrentUserProviderInterface::class)->getCurrentUser();
    $arResult['CUR_USER'] = [
        'name'  => $user->getName(),
        'email' => $user->getEmail(),
        'phone' => $user->getPersonalPhone(),
    ];
}
