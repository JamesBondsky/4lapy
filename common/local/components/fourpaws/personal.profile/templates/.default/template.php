<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * @var \CBitrixComponentTemplate $this
 *
 * @var array                     $arParams
 * @var array                     $arResult
 * @var array                     $templateData
 *
 * @var string                    $componentPath
 * @var string                    $templateName
 * @var string                    $templateFile
 * @var string                    $templateFolder
 *
 * @global CUser                  $USER
 * @global CMain                  $APPLICATION
 * @global CDatabase              $DB
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
<?php require_once '/common/popupChangePassword.phpws/personal.profile/templates/popupChangePassword/popupChangePassword.php' ?>
<?php require_once '/common/popupChangeData.phpurpaws/personal.profile/templates/popupChangeData/popupChangeData.php' ?>
<?php require_once '/common/popupChangePhone.phprpaws/personal.profile/templates/popupChangePhone/popupChangePhone.php' ?>