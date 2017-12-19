<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}?>
<div class="b-account-profile">
    <div class="b-account-profile__title">
        Личные данные
    </div>
    <div class="b-account-profile__data">
        <div class="b-account-profile__personal-data">
            <div class="b-account-profile__column b-account-profile__column--data">
                <div class="b-account-data">
                    <div class="b-account-data__title">
                        ФИО
                    </div>
                    <div class="b-account-data__value">
                        <div class="b-account-data__text">
                            <?=$arResult['CUR_USER']['FULL_NAME']?>
                        </div>
                    </div>
                </div>
                <div class="b-account-data">
                    <div class="b-account-data__title">
                        Телефон
                    </div>
                    <div class="b-account-data__value">
                        <div class="b-account-data__text">
                            <?=$arResult['CUR_USER']['PERSONAL_PHONE']?>
                        </div>
                        <span class="b-icon b-icon--account-profile<?=$arResult['CUR_USER']['PHONE_CONFIRMED'] ? ' active' : ''?>"></span>
                    </div>
                </div>
            </div>
            <div class="b-account-profile__column b-account-profile__column--data">
                <div class="b-account-data">
                    <div class="b-account-data__title">
                        День рождения
                    </div>
                    <div class="b-account-data__value">
                        <div class="b-account-data__text">
                            <?=$arResult['CUR_USER']['BIRTHDAY']?>
                        </div>
                    </div>
                </div>
                <div class="b-account-data">
                    <div class="b-account-data__title">
                        Почта
                    </div>
                    <div class="b-account-data__value">
                        <div class="b-account-data__text">
                            <a href="mailto:<?=$arResult['CUR_USER']['EMAIL']?>"><?=$arResult['CUR_USER']['EMAIL']?></a>
                        </div>
                        <span class="b-icon b-icon--account-profile<?=$arResult['CUR_USER']['EMAIL_CONFIRMED'] ? ' active' : ''?>"></span>
                    </div>
                </div>
            </div>
            <div class="b-account-profile__column b-account-profile__column--data">
                <div class="b-account-data">
                    <div class="b-account-data__title">
                        Пол
                    </div>
                    <div class="b-account-data__value">
                        <div class="b-account-data__text">
                            <?=$arResult['CUR_USER']['GENDER_TEXT']?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="b-account-profile__link-block">
            <a class="b-link b-link--edit-data js-open-popup js-open-popup--edit-data"
               href="javascript:void(0)"
               title="Редактировать данные"
               data-popup-id="edit-data"><span class="b-link__text b-link__text--edit-data">Редактировать данные</span></a>
            <a class="b-link b-link--edit-phone js-open-popup js-open-popup--edit-phone"
               href="javascript:void(0)"
               title="Изменить телефон"
               data-popup-id="edit-phone"><span class="b-link__text b-link__text--edit-phone">Изменить телефон</span>
            </a><a class="b-link b-link--edit-password js-open-popup js-open-popup--edit-password"
                   href="javascript:void(0)"
                   title="Изменить пароль"
                   data-popup-id="edit-password"><span class="b-link__text b-link__text--edit-password">Изменить пароль</span></a>
        </div>
    </div>
    <div class="b-account-profile__other">
        <?php $APPLICATION->IncludeComponent(
            'bitrix:socserv.auth.split',
            'profile.socserv',
            [
                'ALLOW_DELETE'  => 'Y',
                // Разрешить удалять объединенные профили
                'SHOW_PROFILES' => 'Y',
                // Показывать объединенные профили
            ],
            false
        ); ?>
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
                        <input class="b-checkbox__input" name="subscribe_material" id="subscribe-material" type="checkbox"> <label
                            class="b-checkbox__name b-checkbox__name--agree b-checkbox__name--account-subscribe"
                            for="subscribe-material"><span class="b-checkbox__text">Я хочу получать полезные статьи и материалы о питомцах</span>
                        </label>
                    </div>
                    <button class="b-button b-button--account-subcribe">Применить</button>
                </div>
            </form>
        </div>
    </div>
</div>
<section class="b-popup-pick-city b-popup-pick-city--new-password js-popup-section" data-popup="edit-password">
    <a class="b-popup-pick-city__close b-popup-pick-city__close--new-password js-close-popup" href="javascript:void(0);" title="Закрыть"></a>
    <div class="b-registration b-registration--new-password">
        <header class="b-registration__header">
            <h1 class="b-title b-title--h1 b-title--registration">Изменение пароля</h1>
        </header>
        <form class="b-registration__form js-form-validation">
            <div class="b-input-line b-input-line--create-password">
                <div class="b-input-line__label-wrapper">
                    <label class="b-input-line__label" for="registration-password-old-popup">Старый пароль</label>
                </div>
                <input class="b-input b-input--registration-form" type="password" id="registration-password-old-popup" name="old_password" placeholder="" />
                <a class="b-input-line__eye js-open-password" href="javascript:void(0);" title=""></a><span class="b-link-gray">Минимум 6 символов</span>
            </div>
            <div class="b-input-line b-input-line--create-password">
                <div class="b-input-line__label-wrapper">
                    <label class="b-input-line__label" for="registration-password-first-popup">Новый пароль</label>
                </div>
                <input class="b-input b-input--registration-form" type="password" id="registration-password-first-popup" name="password" placeholder="" />
                <a class="b-input-line__eye js-open-password" href="javascript:void(0);" title=""></a><span class="b-link-gray">Минимум 6 символов</span>
            </div>
            <div class="b-input-line b-input-line--create-password">
                <div class="b-input-line__label-wrapper">
                    <label class="b-input-line__label" for="registration-password-second-popup">Повторите новый пароль</label>
                </div>
                <input class="b-input b-input--registration-form" type="password" id="registration-password-second-popup" name="confirm_password" placeholder="" />
                <a class="b-input-line__eye js-open-password" href="javascript:void(0);" title=""></a>
            </div>
            <button class="b-button b-button--subscribe-delivery">Изменить</button>
        </form>
    </div>
</section>
<section class="b-popup-pick-city b-popup-pick-city--edit-data js-popup-section" data-popup="edit-data">
    <a class="b-popup-pick-city__close b-popup-pick-city__close--edit-data js-close-popup" href="javascript:void(0);" title="Закрыть"></a>
    <div class="b-registration b-registration--edit-data">
        <header class="b-registration__header">
            <h1 class="b-title b-title--h1 b-title--registration">Редактирование данных</h1>
        </header>
        <form class="b-registration__form js-form-validation">
            <div class="b-input-line b-input-line--popup-authorization b-input-line--referal">
                <div class="b-input-line__label-wrapper">
                    <label class="b-input-line__label" for="data-last-name">Фамилия</label>
                </div>
                <input class="b-input b-input--registration-form" type="text" id="data-last-name" name="LAST_NAME" value="<?=$arResult['CUR_USER']['LAST_NAME']?>" placeholder="" />
            </div>
            <div class="b-input-line b-input-line--popup-authorization b-input-line--referal">
                <div class="b-input-line__label-wrapper">
                    <label class="b-input-line__label" for="data-first-name">Имя</label> <span class="b-input-line__require">(обязательно)</span>
                </div>
                <input class="b-input b-input--registration-form" type="text" id="data-first-name" name="NAME" value="<?=$arResult['CUR_USER']['NAME']?>" placeholder="" />
            </div>
            <div class="b-input-line b-input-line--popup-authorization b-input-line--referal">
                <div class="b-input-line__label-wrapper">
                    <label class="b-input-line__label" for="data-patronymic">Отчество</label>
                </div>
                <input class="b-input b-input--registration-form" type="text" id="data-patronymic" name="SECOND_NAME" value="<?=$arResult['CUR_USER']['SECOND_NAME']?>" placeholder="" />
            </div>
            <div class="b-input-line b-input-line--popup-authorization b-input-line--referal">
                <div class="b-input-line__label-wrapper">
                    <label class="b-input-line__label" for="data-phone">Телефон</label>
                </div>
                <input class="b-input b-input--registration-form" type="tel" id="data-phone" name="PERSONAL_PHONE" value="<?=$arResult['CUR_USER']['PERSONAL_PHONE']?>" placeholder="" />
            </div>
            <div class="b-input-line b-input-line--popup-authorization b-input-line--referal">
                <div class="b-input-line__label-wrapper">
                    <label class="b-input-line__label" for="data-email">Эл. почта</label>
                </div>
                <input class="b-input b-input--registration-form" type="email" id="data-email" name="EMAIL" value="<?=$arResult['CUR_USER']['EMAIL']?>" placeholder="" />
            </div>
            <div class="b-registration__wrapper-radio">
                <div class="b-radio b-radio--add-pet">
                    <input class="b-radio__input" type="radio" name="PERSONAL_GENDER" value="M" <?=$arResult['CUR_USER']['GENDER'] === 'M' ? ' checked' : ''?> id="male-people" />
                    <label class="b-radio__label b-radio__label--add-pet" for="male-people"><span class="b-radio__text-label">мужской</span>
                    </label>
                </div>
                <div class="b-radio b-radio--add-pet">
                    <input class="b-radio__input" type="radio" name="PERSONAL_GENDER" value="F" <?=$arResult['CUR_USER']['GENDER'] === 'F' ? ' checked' : ''?> id="female-people" />
                    <label class="b-radio__label b-radio__label--add-pet" for="female-people"><span class="b-radio__text-label">женский</span>
                    </label>
                </div>
            </div>
            <button class="b-button b-button--subscribe-delivery">Изменить</button>
        </form>
    </div>
</section>
<section class="b-popup-pick-city b-popup-pick-city--edit-data js-popup-section" data-popup="edit-phone">
    <a class="b-popup-pick-city__close b-popup-pick-city__close--edit-data js-close-popup" href="javascript:void(0);" title="Закрыть"></a>
    <div class="b-registration b-registration--edit-data">
        <header class="b-registration__header">
            <h1 class="b-title b-title--h1 b-title--registration">Изменение телефона</h1>
        </header>
        <form class="b-registration__form js-form-validation">
            <div class="b-registration__step b-registration__step--one">
                <div class="b-input-line b-input-line--popup-authorization b-input-line--referal">
                    <div class="b-input-line__label-wrapper">
                        <label class="b-input-line__label" for="edit-phone">Мобильный</label>
                    </div>
                    <input class="b-input b-input--registration-form" type="tel" id="edit-phone" name="PERSONAL_PHONE" value="<?=$arResult['CUR_USER']['PERSONAL_PHONE']?>" placeholder="" />
                </div>
            </div>
            <div class="b-registration__step b-registration__step--two">
                <div class="b-registration__text b-registration__text--phone">Ваш номер <?=$arResult['CUR_USER']['PERSONAL_PHONE']?></div><a class="b-registration__text b-registration__text--phone-edit js-open-popup" href="javascript:void(0);" title="Сменить номер" data-popup-id="edit-phone">Сменить номер</a>
                <div class="b-input-line b-input-line--popup-authorization b-input-line--sms">
                    <div class="b-input-line__label-wrapper">
                        <label class="b-input-line__label" for="sms-phone">SMS-код</label>
                    </div>
                    <input class="b-input b-input--registration-form" type="text" id="sms-phone" placeholder="" name="CONFIRM_CODE" /><a class="b-link-gray" href="javascript:void(0);" title="Отправить снова">Отправить снова</a>
                </div>
            </div><a class="b-link b-link--subscribe-delivery js-open-popup js-open-popup--subscribe-delivery js-open-popup" href="javascript:void(0)" title="Изменить" data-popup-id="edit-phone-step"><span class="b-link__text b-link__text--subscribe-delivery js-open-popup">Изменить</span></a>
            <button
                    class="b-button b-button--subscribe-delivery">Подтвердить</button>
        </form>
    </div>
</section>
