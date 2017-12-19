<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
} ?>
<div class="b-account-profile__column b-account-profile__column--bottom">
    <div class="b-account-profile__title b-account-profile__title--small">
        Рассылка
    </div>
    <form class="b-account-profile__form">
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
