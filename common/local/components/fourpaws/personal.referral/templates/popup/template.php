<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var array $arResult */ ?>
<section class="b-popup-pick-city b-popup-pick-city--add-referal js-popup-section" data-popup="add-referal">
    <a class="b-popup-pick-city__close b-popup-pick-city__close--add-referal js-close-popup"
       href="javascript:void(0);"
       title="Закрыть"></a>
    <div class="b-registration b-registration--add-referal">
        <header class="b-registration__header">
            <h1 class="b-title b-title--h1 b-title--registration">Новый реферал</h1>
        </header>
        <form class="b-registration__form js-form-validation js-referal-form" data-url="/ajax/personal/referral/add/" method="post">
            <div class="b-input-line b-input-line--popup-authorization b-input-line--referal">
                <div class="b-input-line__label-wrapper">
                    <label class="b-input-line__label" for="number-card">Номер бонусной карты</label>
                    <span class="b-input-line__require">(обязательно)</span>
                </div>
                <div class="b-input b-input--registration-form">
                    <input class="b-input__input-field b-input__input-field--registration-form js-number-card js-offers"
                           type="text"
                           id="number-card"
                           placeholder=""
                           name="UF_CARD"
                           data-url="/ajax/personal/referral/get_user_info/"
                           data-method="post"/>
                    <div class="b-error"><span class="js-message"></span>
                    </div>
                </div>
            </div>
            <div class="b-input-line b-input-line--popup-authorization b-input-line--referal">
                <div class="b-input-line__label-wrapper">
                    <label class="b-input-line__label" for="last-name">Фамилия</label>
                </div>
                <div class="b-input b-input--registration-form b-kek">
                    <input class="b-input__input-field b-input__input-field--registration-form js-no-valid"
                           type="text"
                           id="last-name"
                           placeholder=""
                           name="UF_LAST_NAME" />
                    <div class="b-error"><span class="js-message"></span>
                    </div>
                </div>
            </div>
            <div class="b-input-line b-input-line--popup-authorization b-input-line--referal">
                <div class="b-input-line__label-wrapper">
                    <label class="b-input-line__label" for="first-name">Имя</label>
                </div>
                <div class="b-input b-input--registration-form b-kek">
                    <input class="b-input__input-field b-input__input-field--registration-form js-no-valid"
                           type="text"
                           id="first-name"
                           placeholder=""
                           name="UF_NAME" />
                    <div class="b-error"><span class="js-message"></span>
                    </div>
                </div>
            </div>
            <div class="b-input-line b-input-line--popup-authorization b-input-line--referal">
                <div class="b-input-line__label-wrapper">
                    <label class="b-input-line__label" for="patronymic">Отчество</label>
                </div>
                <div class="b-input b-input--registration-form b-kek">
                    <input class="b-input__input-field b-input__input-field--registration-formjs-no-valid"
                           type="text"
                           id="patronymic"
                           placeholder=""
                           name="UF_SECOND_NAME	" />
                    <div class="b-error"><span class="js-message"></span>
                    </div>
                </div>
            </div>
            <div class="b-input-line b-input-line--popup-authorization b-input-line--referal">
                <div class="b-input-line__label-wrapper">
                    <label class="b-input-line__label" for="phone-referal">Телефон</label>
                </div>
                <div class="b-input b-input--registration-form b-kek">
                    <input class="b-input__input-field b-input__input-field--registration-form js-no-valid"
                           type="tel"
                           id="phone-referal"
                           placeholder=""
                           name="UF_PHONE" />
                    <div class="b-error"><span class="js-message"></span>
                    </div>
                </div>
            </div>
            <div class="b-input-line b-input-line--popup-authorization b-input-line--referal">
                <div class="b-input-line__label-wrapper">
                    <label class="b-input-line__label" for="email-referal">Эл. почта</label>
                </div>
                <div class="b-input b-input--registration-form b-kek">
                    <input class="b-input__input-field b-input__input-field--registration-form js-no-valid"
                           type="email"
                           id="email-referal"
                           placeholder=""
                           name="UF_EMAIL" />
                    <div class="b-error"><span class="js-message"></span>
                    </div>
                </div>
            </div>
            <button class="b-button b-button--subscribe-delivery">Сохранить</button>
            <div class="b-registration__text b-registration__text--referal">Начисление баллов начнется после
                                                                            успешной проверки данных
            </div>
        </form>
    </div>
</section>