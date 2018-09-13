<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var FourPawsFrontOfficeCardRegistrationComponent $component
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $componentPath
 */

//echo '<div class="lk-container">';
echo '<div class="tab-user-list">';
if (!empty($arResult['ALREADY_REGISTERED_USERS'])) {
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
        foreach ($arResult['ALREADY_REGISTERED_USERS'] as $user) {
            /** @var \FourPaws\UserBundle\Entity\User $user */
            $rowClass = $rowClass === 'even' ? 'odd' : 'even';
            ?>
            <tr class="user-list__item-row <?= $rowClass ?>">
                <td class="user-list__full-name">
                    <div class="cell-value"><?= htmlspecialcharsbx($user->getFullName()) ?></div>
                </td>
                <td class="user-list__phone">
                    <div class="cell-value"><?= htmlspecialcharsbx($user->getNormalizePersonalPhone()) ?></div>
                </td>
                <td class="user-list__card-number">
                    <div class="cell-value"><?= htmlspecialcharsbx($user->getDiscountCardNumber()) ?></div>
                </td>
                <td class="user-list__bd">
                    <div class="cell-value"><?= $user->getBirthday() ?></div>
                </td>
                <td class="user-list__auth">
                    <div class="cell-value">
                        <span class="_action-auth avatarAuth" data-user-id="<?= $user->getId() ?>">авторизоваться</span>
                    </div>
                </td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
    <?php
}

echo '</div>';
//echo '</div>';

?>
<div>
    <a href="<?= $arParams['CURRENT_PAGE'] ?>" class="btn inline-block">Отмена</a>
</div>
<?php
