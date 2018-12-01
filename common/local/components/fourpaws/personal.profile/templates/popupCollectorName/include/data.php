<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var string $phone
 * @var string $oldPhone*/ ?>
<form class="b-registration__form js-form-validation js-phone-change-one"
      data-url="/ajax/personal/profile/collectUserData/"
      method="post">
    <input type="hidden" class="js-data-id js-no-valid" name="ID" value="<?= $arResult['CUR_USER']['ID'] ?>">
    <div class="b-input-line b-input-line--popup-authorization b-input-line--referal js-first-name">
        <div class="b-input-line__label-wrapper">
            <label class="b-input-line__label" for="data-first-name">Имя</label>
            <span class="b-input-line__require">(обязательно)</span>
        </div>
        <div class="b-input b-input--registration-form">
            <input class="b-input__input-field b-input__input-field--registration-form js-small-input"
                   type="text"
                   id="data-first-name"
                   name="NAME"
                   value="<?= $arResult['CUR_USER']['NAME'] ?>"
                   data-text="1"
                   placeholder="" />
            <div class="b-error"><span class="js-message"></span>
            </div>
        </div>
        <? if($arParams['TYPE'] == 'more') { ?>
            <div class="b-input-line b-input-line--popup-authorization b-input-line--referal js-last-name">
                <div class="b-input-line__label-wrapper">
                    <label class="b-input-line__label" for="data-last-name">Фамилия</label>
                </div>
                <div class="b-input b-input--registration-form">
                    <input class="b-input__input-field b-input__input-field--registration-form js-no-valid"
                           type="text"
                           id="data-last-name"
                           name="LAST_NAME"
                           value="<?= $arResult['CUR_USER']['LAST_NAME'] ?>"
                           data-text="0"
                           placeholder="" />
                    <div class="b-error"><span class="js-message"></span>
                    </div>
                </div>
            </div>
            <div class="b-input-line b-input-line--popup-authorization b-input-line--referal js-email">
                <div class="b-input-line__label-wrapper">
                    <label class="b-input-line__label" for="data-email">Эл. почта</label>
                    <span class="b-input-line__require">(обязательно)</span>
                </div>
                <div class="b-input b-input--registration-form">
                    <input class="b-input__input-field b-input__input-field--registration-form"
                           type="email"
                           id="data-email"
                           name="EMAIL"
                           value="<?= $arResult['CUR_USER']['EMAIL'] ?>"
                           placeholder="" />
                    <div class="b-error"><span class="js-message"></span>
                    </div>
                </div>
            </div>
        <? } ?>
    </div>
    <div class="b-registration__step b-registration__step--one js-phone-change-one">
        <input type="hidden" name="action" value="get">
        <input type="hidden" name="step" value="confirm">
        <div class="b-input-line b-input-line--popup-authorization b-input-line--referal">
            <div class="b-input-line__label-wrapper">
                <label class="b-input-line__label" for="edit-phone">Мобильный</label>
            </div>
            <div class="b-input b-input--registration-form">
                <input class="b-input__input-field b-input__input-field--registration-form"
                       type="tel"
                       id="edit-phone"
                       name="phone"
                       value="<?= $phone ?>"
                       placeholder="" />
                <div class="b-error"><span class="js-message"></span>
                </div>
            </div>
        </div>
    </div>
    <a class="b-link b-link--subscribe-delivery js-open-popup js-open-popup--subscribe-delivery js-open-popup"
       href="javascript:void(0)"
       title="Изменить"
       data-popup-id="edit-phone-step">
        <span class="b-link__text b-link__text--subscribe-delivery js-open-popup">Изменить</span>
    </a>
    <button
            class="b-button b-button--subscribe-delivery js-sms-step">Подтвердить
    </button>
</form>

