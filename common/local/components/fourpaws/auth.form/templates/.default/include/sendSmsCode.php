<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
} ?>
<div class="b-registration__content b-registration__content--moiety b-registration__content--step">
    <div class="b-step-form b-step-form--add-number">Шаг <span>1</span> из <span>2</span>
    </div>
    <div class="b-registration__your-number">Ваш номер <span><?= $phone ?></span>
    </div>
    <a class="b-link-gray b-link-gray--add-number"
       href="javascript:void(0);"
       title="Сменить номер"
       data-action="get"
       data-step="addPhone"
       data-phone="<?= $phone ?>">Сменить номер</a>
    <form class="b-registration__form" data-url="/ajax/user/auth/login/" method="post">
        <input type="hidden" name="action" value="savePhone">
        <input type="hidden" name="phone" value="<?= $phone ?>">
        <div class="b-input-line b-input-line--add-number">
            <div class="b-input-line__label-wrapper">
                <label class="b-input-line__label" for="sms-code-3">SMS-код</label>
            </div>
            <input class="b-input b-input--registration-form"
                   type="text"
                   id="sms-code-3"
                   placeholder=""
                   name="confirmCode" />
            <span class="b-link-gray">Отправить новый код можно будет через <span>59</span> сек.</span>
        </div>
        <div class="b-registration__captcha" data-sitekey=""></div>
        <button class="b-button b-button--social b-button--full-width" type="submit">Подтвердить</button>
    </form>
</div>
<section class="b-registration__additional-info b-registration__additional-info--step">
    <h3 class="b-registration__title-advantage">Зачем это нужно?</h3>
    <ul class="b-social-advantage">
        <li class="b-social-advantage__item">Для оперативной связи по поводу доставки</li>
        <li class="b-social-advantage__item">Для привязки бонусной карты</li>
        <li class="b-social-advantage__item">Телефон можно использовать как логин при входе</li>
    </ul>
</section>