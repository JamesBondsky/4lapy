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
if ($arResult['IS_AVATAR_AUTHORIZED'] === 'Y') {
    echo '<br><p>Вы уже находитесь в режиме "аватар". <a href="'.$arParams['LOGOUT_URL'].'">Выйти из режима</a>.</p>';
    return;
}

// форма
include __DIR__ . '/inc.form.php';

// Список пользователей
echo '<div class="lk-container">';
echo '<div class="tab-user-list">';
if (!empty($arResult['USERS_LIST'])) {
    ?>
    <table class="user-list">
        <thead>
        <tr>
            <th class="user-list__full-name">Ф.И.О.</th>
            <th class="user-list__phone">Телефон</th>
            <th class="user-list__card-number">Номер карты</th>
            <th class="user-list__bd">Род.</th>
            <th class="user-list__auth">Действие</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $rowClass = 'even';
        foreach ($arResult['USERS_LIST'] as $user) {
            $rowClass = $rowClass === 'even' ? 'odd' : 'even';
            ?>
            <tr class="user-list__item-row <?= $rowClass ?>">
                <td class="user-list__full-name">
                    <div class="cell-value"><?= ($user['_FULL_NAME_'] ? htmlspecialcharsbx($user['_FULL_NAME_']) : '-') ?></div>
                </td>
                <td class="user-list__phone">
                    <div class="cell-value"><?= ($user['_PERSONAL_PHONE_NORMALIZED_'] ? htmlspecialcharsbx($user['_PERSONAL_PHONE_NORMALIZED_']) : '-') ?></div>
                </td>
                <td class="user-list__card-number">
                    <div class="cell-value"><?= ($user['UF_DISCOUNT_CARD'] ? htmlspecialcharsbx($user['UF_DISCOUNT_CARD']) : '-') ?></div>
                </td>
                <td class="user-list__bd">
                    <div class="cell-value"><?= ($user['PERSONAL_BIRTHDAY'] ? htmlspecialcharsbx($user['PERSONAL_BIRTHDAY']) : '-') ?></div>
                </td>
                <td class="user-list__auth">
                    <div class="cell-value"><span class="_action-auth"
                                                  data-id="<?= $user['ID'] ?>">авторизоваться</span></div>
                </td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
    <?php
} elseif (empty($arResult['ERROR'])) {
    echo '<p>По запросу ничего не найдено</p>';
}
echo '</div>';
echo '</div>';
