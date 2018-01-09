<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var string $phone */ ?>
<div class="b-registration__content b-registration__content--create-password">
    <div class="b-registration__text-instruction b-registration__text-instruction--create-password">Введите код
                                                                                                    подтверждения из
                                                                                                    SMS, который мы
                                                                                                    выслали вам на
                                                                                                    номер <?= $phone ?>
    </div>
    <form class="b-registration__form js-form-validation js-password-recovery-code"
          data-url="/ajax/user/auth/forgotPassword/"
          method="post">
        <input type="hidden" name="action" value="createNewPassword">
        <input type="hidden" name="phone" value="<?= $phone ?>">
        <div class="b-input-line b-input-line--create-password b-input-line--recovery">
            <div class="b-input-line__label-wrapper">
                <label class="b-input-line__label" for="registration-tel-recovery-code">SMS-код</label>
            </div>
            <div class="b-input b-input--registration-form b-kek">
                <input class="b-input__input-field b-input__input-field--registration-form b-kek"
                       type="text"
                       id="registration-tel-recovery-code"
                       placeholder=""
                       name="confirmCode" />
                <div class="b-error"><span class="js-message"></span>
                </div>
            </div>
            <span class="b-link-gray">Отправить новый код можно будет через <span>59</span> сек.</span>
        </div>
        <button class="b-button b-button--social b-button--full-width" type="submit">Далее</button>
    </form>
</div>