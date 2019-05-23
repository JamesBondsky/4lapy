<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @global CMain                                     $APPLICATION
 * @var array                                        $arParams
 * @var array                                        $arResult
 * @var CBitrixComponent $component
 * @var CBitrixComponentTemplate                     $this
 * @var string                                       $templateName
 * @var string                                       $componentPath
 */

if ($arResult['CAN_ACCESS'] !== 'Y') {
    ShowError('При обработке запроса произошла ошибка: отказано в доступе');
    return;
}
/*if ($arResult['IS_AVATAR_AUTHORIZED'] === 'Y') {
    echo '<br><p>Вы уже находитесь в режиме "аватар". <a href="'.$arParams['LOGOUT_URL'].'">Выйти из режима</a>.</p>';
    return;
}*/

// форма
include __DIR__ . '/inc.form.php';
