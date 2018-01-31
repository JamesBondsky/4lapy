<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var array $arResult */ ?>
<section class="b-popup-pick-city b-popup-pick-city--edit-data js-popup-section" data-popup="edit-data">
    <a class="b-popup-pick-city__close b-popup-pick-city__close--edit-data js-close-popup"
       href="javascript:void(0);"
       title="Закрыть"></a>
    <div class="b-registration b-registration--edit-data">
        <header class="b-registration__header">
            <h1 class="b-title b-title--h1 b-title--registration">Редактирование данных</h1>
        </header>
        <form class="b-registration__form js-form-validation js-edit-data-query"
              data-url="/ajax/personal/profile/changeData/"
              method="post">
            <input class="js-data-id js-no-valid" name="ID" value="<?= $arResult['CUR_USER']['ID'] ?>" type="hidden">
            <div class="b-input-line b-input-line--popup-authorization b-input-line--referal js-last-name">
                <div class="b-input-line__label-wrapper">
                    <label class="b-input-line__label" for="data-last-name">Фамилия</label>
                </div>
                <div class="b-input b-input--registration-form">
                    <input class="b-input__input-field b-input__input-field--registration-form"
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
            <div class="b-input-line b-input-line--popup-authorization b-input-line--referal js-first-name">
                <div class="b-input-line__label-wrapper">
                    <label class="b-input-line__label" for="data-first-name">Имя</label>
                    <span class="b-input-line__require">(обязательно)</span>
                </div>
                <div class="b-input b-input--registration-form">
                    <input class="b-input__input-field b-input__input-field--registration-form"
                           type="text"
                           id="data-first-name"
                           name="NAME"
                           value="<?= $arResult['CUR_USER']['NAME'] ?>"
                           data-text="1"
                           placeholder="" />
                    <div class="b-error"><span class="js-message"></span>
                    </div>
                </div>
            </div>
            <div class="b-input-line b-input-line--popup-authorization b-input-line--referal js-patronymic">
                <div class="b-input-line__label-wrapper">
                    <label class="b-input-line__label" for="data-patronymic">Отчество</label>
                </div>
                <div class="b-input b-input--registration-form">
                    <input class="b-input__input-field b-input__input-field--registration-form"
                           type="text"
                           id="data-patronymic"
                           name="SECOND_NAME"
                           value="<?= $arResult['CUR_USER']['SECOND_NAME'] ?>"
                           data-text="2"
                           placeholder="" />
                    <div class="b-error"><span class="js-message"></span>
                    </div>
                </div>
            </div>
            <div class="b-input-line b-input-line--popup-authorization b-input-line--referal js-date js-date-valid">
                <div class="b-input-line__label-wrapper">
                    <label class="b-input-line__label" for="data-date">Дата рождения</label>
                </div>
                <div class="b-input b-input--registration-form">
                    <input class="b-input__input-field b-input__input-field--registration-form"
                           type="text"
                           id="data-date"
                           placeholder=""
                           name="PERSONAL_BIRTHDAY"
                           data-text="3"
                           value="<?= $arResult['CUR_USER']['BIRTHDAY_POPUP'] ?>" />
                    <div class="b-error"><span class="js-message"></span>
                    </div>
                </div>
            </div>
            <div class="b-input-line b-input-line--popup-authorization b-input-line--referal js-email">
                <div class="b-input-line__label-wrapper">
                    <label class="b-input-line__label" for="data-email">Эл. почта</label>
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
            <div class="b-registration__wrapper-radio js-male">
                <div class="b-radio b-radio--add-pet">
                    <input class="b-radio__input"
                           type="radio"
                           name="PERSONAL_GENDER"
                           data-radio="0"
                           value="M" <?= $arResult['CUR_USER']['GENDER'] === 'M' ? ' checked' : '' ?>
                           id="male-people" />
                    <label class="b-radio__label b-radio__label--add-pet"
                           for="male-people"><span class="b-radio__text-label">мужской</span>
                    </label>
                </div>
                <div class="b-radio b-radio--add-pet">
                    <input class="b-radio__input"
                           type="radio"
                           name="PERSONAL_GENDER"
                           data-radio="1"
                           value="F" <?= $arResult['CUR_USER']['GENDER'] === 'F' ? ' checked' : '' ?>
                           id="female-people" />
                    <label class="b-radio__label b-radio__label--add-pet"
                           for="female-people"><span class="b-radio__text-label">женский</span>
                    </label>
                </div>
            </div>
            <button class="b-button b-button--subscribe-delivery">Изменить</button>
        </form>
    </div>
</section>

