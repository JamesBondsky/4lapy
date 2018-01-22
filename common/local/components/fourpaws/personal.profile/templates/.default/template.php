<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * @global CMain $APPLICATION
 */
?>
<div class="b-account-profile">
    <div class="b-account-profile__title">
        Личные данные
    </div>
    <div class="b-account-profile__data">
        <?php require_once 'include/mainUserData.php' ?>
        <?php require_once 'include/userActions.php' ?>
    </div>
    <div class="b-account-profile__other">
        <?php $APPLICATION->IncludeComponent(
            'bitrix:socserv.auth.split',
            'profile.socserv',
            [
                'ALLOW_DELETE'  => 'Y',
                // Разрешить удалять объединенные профили
                'SHOW_PROFILES' => 'Y',
                // Показывать объединенные профили
            ],
            false
        ); ?>
        <?php require_once 'include/subscribe.php' ?>
    </div>
</div>