<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
} ?>
<div class="b-registration__content b-registration__content--moiety">
    <a class="b-button b-button--social b-button--full-width js-reg-by-phone"
       href="javascript:void(0)"
       title="Регистрация по телефону"
       data-url="/ajax/user/auth/register/"
       data-action="get"
       data-step="addPhone">Регистрация по телефону</a>
    <span class="b-registration__else">или</span>
    <?php
    $APPLICATION->IncludeComponent(
        'bitrix:socserv.auth.form',
        'socserv_reg',
        [
            'AUTH_SERVICES' => $arResult['AUTH_SERVICES'],
            'AUTH_URL'      => $arResult['AUTH_URL'],
            'POST'          => $arResult['POST'],
        ],
        $component,
        ['HIDE_ICONS' => 'Y']
    );
    ?>
</div>
<section class="b-registration__additional-info">
    <h3 class="b-registration__title-advantage">Преимущества регистрации</h3>
    <ul class="b-social-advantage">
        <li class="b-social-advantage__item">Отслеживание статуса заказа</li>
        <li class="b-social-advantage__item">Подписка на доставку</li>
        <li class="b-social-advantage__item">Накопление бонусов</li>
        <li class="b-social-advantage__item">Персональные рекомендации</li>
    </ul>
</section>
