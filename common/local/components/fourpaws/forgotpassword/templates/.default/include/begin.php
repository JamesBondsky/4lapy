<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
} ?>
<div class="b-registration__content b-registration__content--create-password">
    <div class="b-registration__text-instruction b-registration__text-instruction--create-password">Для восстановления
                                                                                                    пароля, введите
                                                                                                    телефон или почту,
                                                                                                    которую вы указывали
                                                                                                    при регистрации
    </div>
    <form class="b-registration__form" data-url="/ajax/user/auth/forgotPassword/" type="post">
        <input type="hidden" name="action" value="get">
        <div class="b-choice-recovery">
            <input class="b-choice-recovery__input js-recovery-telephone"
                   id="registration-recovery-telephone"
                   type="radio"
                   name="recovery"
                   value="phone"
                   checked="checked" />
            <label class="b-choice-recovery__label b-choice-recovery__label--left"
                   for="registration-recovery-telephone">Телефон</label>
            <input class="b-choice-recovery__input js-recovery-email"
                   id="registration-recovery-email"
                   type="radio"
                   value="email"
                   name="recovery" />
            <label class="b-choice-recovery__label b-choice-recovery__label--right" for="registration-recovery-email">Почта</label>
        </div>
        <div class="b-input-line b-input-line--create-password b-input-line--recovery js-telephone-recovery">
            <input class="b-input b-input--registration-form"
                   type="tel"
                   id="registration-tel-recovery"
                   name="phone"
                   value="<?=$phone?>"
                   placeholder="" />
        </div>
        <div class="b-input-line b-input-line--create-password b-input-line--recovery js-email-recovery">
            <input class="b-input b-input--registration-form"
                   type="email"
                   id="registration-email-recovery"
                   name="email"
                   placeholder="" />
        </div>
        <button class="b-button b-button--social b-button--full-width">Далее</button>
    </form>
</div>
