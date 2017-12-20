<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * @var \CBitrixComponentTemplate $this
 *
 * @var array $arParams
 * @var array $arResult
 * @var array $templateData
 * @var FourPawsExpertsenderFormComponent $component
 *
 * @var string $componentPath
 * @var string $templateName
 * @var string $templateFile
 * @var string $templateFolder
 *
 * @global CMain $APPLICATION
 * @global CDatabase $DB
 */

$component = $this->getComponent();
$email = '';
if ($component->getAuthorizationProvider()->isAuthorized()) {
    $email = $component->getCurrentUserProvider()->getCurrentUser()->getEmail();
}
?>
<div class="b-account-profile__column b-account-profile__column--bottom">
    <div class="b-account-profile__title b-account-profile__title--small">
        Рассылка
    </div
    <form class="b-account-profile__form" data-url="/ajax/user/subscribe/subscribe/" method="post">
        <input type="hidden" name="type" value="profile">
        <input type="hidden" name="email" value="<?= $arResult['EMAIL'] ?>">
        <div class="b-account-profile__subscribe-setting">
            <div class="b-checkbox b-checkbox--agree b-checkbox--account-subscribe">
                <input class="b-checkbox__input" name="subscribe_sale" id="subscribe-sale" type="checkbox">
                <label class="b-checkbox__name b-checkbox__name--agree b-checkbox__name--account-subscribe"
                       for="subscribe-sale"><span class="b-checkbox__text">Я хочу получать информацию о скидках и подарках</span>
                </label>
            </div>
            <div class="b-checkbox b-checkbox--agree b-checkbox--account-subscribe">
                <input class="b-checkbox__input" name="subscribe_material" id="subscribe-material" type="checkbox">
                <label
                    class="b-checkbox__name b-checkbox__name--agree b-checkbox__name--account-subscribe"
                    for="subscribe-material"><span class="b-checkbox__text">Я хочу получать полезные статьи и материалы о питомцах</span>
                </label>
            </div>
            <button class="b-button b-button--account-subcribe">Применить</button>
        </div>
    </form>
</div>
