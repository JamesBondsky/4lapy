<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';
$APPLICATION->SetTitle('Профиль');
?>
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
                            Орлов Константин
                        </div>
                    </div>
                </div>
                <div class="b-account-data">
                    <div class="b-account-data__title">
                        Телефон
                    </div>
                    <div class="b-account-data__value">
                        <div class="b-account-data__text">
                            +7 (916) 476-48-40
                        </div>
                        <span class="b-icon b-icon--account-profile active"></span>
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
                            20 июня 1984
                        </div>
                    </div>
                </div>
                <div class="b-account-data">
                    <div class="b-account-data__title">
                        Почта
                    </div>
                    <div class="b-account-data__value">
                        <div class="b-account-data__text">
                            <a href="mailto:mail@mail.com">mail@mail.com</a>
                        </div>
                        <span class="b-icon b-icon--account-profile"></span>
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
                            Мужской
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
                        <input class="b-checkbox__input" name="sale" id="subscribe-sale" type="checkbox">
                        <label class="b-checkbox__name b-checkbox__name--agree b-checkbox__name--account-subscribe"
                               for="subscribe-sale"><span class="b-checkbox__text">Я хочу получать информацию о скидках и подарках</span>
                        </label>
                    </div>
                    <div class="b-checkbox b-checkbox--agree b-checkbox--account-subscribe">
                        <input class="b-checkbox__input" name="material" id="subscribe-material" type="checkbox"> <label
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
    <br>
<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'; ?>