<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
} ?>
<section class="b-popup-pick-city b-popup-pick-city--new-password js-popup-section" data-popup="edit-password">
    <a class="b-popup-pick-city__close b-popup-pick-city__close--new-password js-close-popup"
       href="javascript:void(0);"
       title="Закрыть"></a>
    <div class="b-registration b-registration--new-password">
        <header class="b-registration__header">
            <h1 class="b-title b-title--h1 b-title--registration">Изменение пароля</h1>
        </header>
        <form class="b-registration__form js-form-validation js-new-password"
              data-url="/ajax/personal/profile/changePassword/"
              method="post">
            <input class="js-data-id js-no-valid" name="ID" value="<?= $arResult['CUR_USER']['ID'] ?>" type="hidden">
            <div class="b-input-line b-input-line--create-password">
                <div class="b-input-line__label-wrapper">
                    <label class="b-input-line__label" for="registration-password-old-popup">Старый пароль</label>
                </div>
                <div class="b-input b-input--registration-form">
                    <input class="b-input__input-field b-input__input-field--registration-form"
                           type="password"
                           id="registration-password-old-popup"
                           data-password="0"
                           name="old_password"
                           placeholder="" />
                    <div class="b-error"><span class="js-message"></span>
                    </div>
                </div>
                <a class="b-input-line__eye js-open-password"
                   href="javascript:void(0);"
                   title=""></a><span class="b-link-gray">Минимум 6 символов</span>
            </div>
            <div class="b-input-line b-input-line--create-password">
                <div class="b-input-line__label-wrapper">
                    <label class="b-input-line__label" for="registration-password-first-popup">Новый пароль</label>
                </div>
                <div class="b-input b-input--registration-form">
                    <input class="b-input__input-field b-input__input-field--registration-form"
                           type="password"
                           id="registration-password-first-popup"
                           name="password"
                           data-password="1"
                           placeholder="" />
                    <div class="b-error"><span class="js-message"></span>
                    </div>
                </div>
                <a class="b-input-line__eye js-open-password"
                   href="javascript:void(0);"
                   title=""></a><span class="b-link-gray">Минимум 6 символов</span>
            </div>
            <div class="b-input-line b-input-line--create-password">
                <div class="b-input-line__label-wrapper">
                    <label class="b-input-line__label" for="registration-password-second-popup">Повторите новый
                                                                                                пароль</label>
                </div>
                <div class="b-input b-input--registration-form">
                    <input class="b-input__input-field b-input__input-field--registration-form"
                           type="password"
                           id="registration-password-second-popup"
                           name="confirm_password"
                           data-type="password_two"
                           placeholder="" />
                    <div class="b-error"><span class="js-message"></span>
                    </div>
                </div>
                <a class="b-input-line__eye js-open-password" href="javascript:void(0);" title=""></a>
            </div>
            <button class="b-button b-button--subscribe-delivery">Изменить</button>
        </form>
    </div>
</section>
