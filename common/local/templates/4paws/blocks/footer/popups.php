<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
} ?>
<div class="b-popup-wrapper js-popup-wrapper">
    <?php $APPLICATION->IncludeComponent(
        'fourpaws:city.selector',
        'popup',
        [],
        false,
        ['HIDE_ICONS' => 'Y']
    ); ?>
    <?php $APPLICATION->IncludeComponent(
        'fourpaws:auth.form',
        'popup',
        [],
        false,
        ['HIDE_ICONS' => 'Y']
    ); ?>
    <section class="b-popup-pick-city b-popup-pick-city--subscribe-delivery js-popup-section"
             data-popup="subscribe-delivery">
        <a class="b-popup-pick-city__close b-popup-pick-city__close--subscribe-delivery js-close-popup"
           href="javascript:void(0);"
           title="Закрыть"></a>
        <div class="b-registration b-registration--subscribe-delivery">
            <header class="b-registration__header">
                <h1 class="b-title b-title--h1 b-title--registration">Подписка на доставку</h1>
            </header>
            <form class="b-registration__form js-form-validation">
                <label class="b-registration__label b-registration__label--subscribe-delivery">День первой
                                                                                               доставки</label>
                <div class="b-select b-select--subscribe-delivery">
                    <select class="b-select__block b-select__block--subscribe-delivery" name="first-delivery">
                        <option value="first-delivery-0">10:00—16:00</option>
                        <option value="first-delivery-1">60:00—18:00</option>
                        <option value="first-delivery-2">18:00—20:00</option>
                    </select>
                </div>
                <label class="b-registration__label b-registration__label--subscribe-delivery">Интервал</label>
                <div class="b-select b-select--subscribe-delivery">
                    <select class="b-select__block b-select__block--subscribe-delivery" name="delivery-interval">
                        <option value="delivery-interval-0">10:00—16:00</option>
                        <option value="delivery-interval-1">60:00—18:00</option>
                        <option value="delivery-interval-2">18:00—20:00</option>
                    </select>
                </div>
                <label class="b-registration__label b-registration__label--subscribe-delivery">Как часто</label>
                <div class="b-select b-select--subscribe-delivery">
                    <select class="b-select__block b-select__block--subscribe-delivery" name="frequency-delivery">
                        <option value="frequency-delivery-0">10:00—16:00</option>
                        <option value="frequency-delivery-1">60:00—18:00</option>
                        <option value="frequency-delivery-2">18:00—20:00</option>
                    </select>
                </div>
                <div class="b-registration__text b-registration__text--subscribe-delivery">Периодичность, день и время
                                                                                           доставки вы сможете поменять
                                                                                           в личном кабинете в любой
                                                                                           момент
                </div>
                <ul class="b-registration__info-delivery">
                    <li class="b-registration__item-delivery"><span class="b-icon b-icon--delivery-calendar"><svg class="b-icon__svg"
                                                                                                                  viewBox="0 0 16 17 "
                                                                                                                  width="16px"
                                                                                                                  height="17px"><use
                                        class="b-icon__use"
                                        xlink:href="icons.svg#icon-delivery-calendar"></use></svg></span>
                        <div class="b-registration__text b-registration__text--info-delivery">
                            <p>Параметры подписки: по субботам, раз в неделю, с 10 до 20.</p>
                            <p>Первая доставка: суббота 20.07.2017 с 10 до 20</p>
                        </div>
                    </li>
                    <li class="b-registration__item-delivery"><span class="b-icon b-icon--delivery-calendar"><svg class="b-icon__svg"
                                                                                                                  viewBox="0 0 18 12 "
                                                                                                                  width="18px"
                                                                                                                  height="12px"><use
                                        class="b-icon__use"
                                        xlink:href="icons.svg#icon-delivery-car"></use></svg></span>
                        <div class="b-registration__text b-registration__text--info-delivery">
                            <p>Доставка курьером, по адресу:</p>
                            <p>г. Москва, ул. Ленина, д. 4, кв. 24, под. 3, эт. 4</p>
                        </div>
                    </li>
                    <li class="b-registration__item-delivery"><span class="b-icon b-icon--delivery-calendar"><svg class="b-icon__svg"
                                                                                                                  viewBox="0 0 18 14 "
                                                                                                                  width="18px"
                                                                                                                  height="14px"><use
                                        class="b-icon__use"
                                        xlink:href="icons.svg#icon-delivery-dollar"></use></svg></span>
                        <div class="b-registration__text b-registration__text--info-delivery">
                            <p>Оплата: наличными или картой при получении.</p>
                        </div>
                    </li>
                </ul>
                <button class="b-button b-button--subscribe-delivery">Сохранить</button>
            </form>
        </div>
    </section>
    <section class="b-popup-pick-city b-popup-pick-city--add-referal js-popup-section" data-popup="add-referal">
        <a class="b-popup-pick-city__close b-popup-pick-city__close--add-referal js-close-popup"
           href="javascript:void(0);"
           title="Закрыть"></a>
        <div class="b-registration b-registration--add-referal">
            <header class="b-registration__header">
                <h1 class="b-title b-title--h1 b-title--registration">Новый реферал</h1>
            </header>
            <form class="b-registration__form js-form-validation">
                <div class="b-input-line b-input-line--popup-authorization b-input-line--referal">
                    <div class="b-input-line__label-wrapper">
                        <label class="b-input-line__label" for="number-card">Номер бонусной карты</label>
                        <span class="b-input-line__require">(обязательно)</span>
                    </div>
                    <input class="b-input b-input--registration-form" type="text" id="number-card" placeholder="" />
                </div>
                <div class="b-input-line b-input-line--popup-authorization b-input-line--referal">
                    <div class="b-input-line__label-wrapper">
                        <label class="b-input-line__label" for="last-name">Фамилия</label>
                    </div>
                    <input class="b-input b-input--registration-form" type="text" id="last-name" placeholder="" />
                </div>
                <div class="b-input-line b-input-line--popup-authorization b-input-line--referal">
                    <div class="b-input-line__label-wrapper">
                        <label class="b-input-line__label" for="first-name">Имя</label>
                    </div>
                    <input class="b-input b-input--registration-form" type="text" id="first-name" placeholder="" />
                </div>
                <div class="b-input-line b-input-line--popup-authorization b-input-line--referal">
                    <div class="b-input-line__label-wrapper">
                        <label class="b-input-line__label" for="patronymic">Отчество</label>
                    </div>
                    <input class="b-input b-input--registration-form" type="text" id="patronymic" placeholder="" />
                </div>
                <div class="b-input-line b-input-line--popup-authorization b-input-line--referal">
                    <div class="b-input-line__label-wrapper">
                        <label class="b-input-line__label" for="phone-referal">Телефон</label>
                    </div>
                    <input class="b-input b-input--registration-form" type="tel" id="phone-referal" placeholder="" />
                </div>
                <div class="b-input-line b-input-line--popup-authorization b-input-line--referal">
                    <div class="b-input-line__label-wrapper">
                        <label class="b-input-line__label" for="email-referal">Эл. почта</label>
                    </div>
                    <input class="b-input b-input--registration-form" type="email" id="email-referal" placeholder="" />
                </div>
                <button class="b-button b-button--subscribe-delivery">Сохранить</button>
                <div class="b-registration__text b-registration__text--referal">Начисление баллов начнется после
                                                                                успешной проверки данных
                </div>
            </form>
        </div>
    </section>
    <section class="b-popup-pick-city b-popup-pick-city--add-adress js-popup-section" data-popup="edit-adress-popup">
        <a class="b-popup-pick-city__close b-popup-pick-city__close--add-adress js-close-popup"
           href="javascript:void(0);"
           title="Закрыть"></a>
        <div class="b-registration b-registration--add-adress">
            <header class="b-registration__header">
                <h1 class="b-title b-title--h1 b-title--registration">Новый адрес доставки</h1>
            </header>
            <form class="b-registration__form js-form-validation">
                <div class="b-input-line b-input-line--popup-authorization">
                    <div class="b-input-line__label-wrapper">
                        <label class="b-input-line__label" for="name-adress">Название</label>
                        <span class="b-input-line__require">(например, дом, работа, дача)</span>
                    </div>
                    <input class="b-input b-input--registration-form" type="text" id="name-adress" placeholder="" />
                </div>
                <div class="b-input-line b-input-line--popup-authorization">
                    <div class="b-input-line__label-wrapper">
                        <label class="b-input-line__label" for="city-adress">Город</label>
                        <span class="b-input-line__require">(обязательно)</span>
                    </div>
                    <input class="b-input b-input--registration-form" type="text" id="city-adress" placeholder="" />
                </div>
                <div class="b-input-line b-input-line--popup-authorization">
                    <div class="b-input-line__label-wrapper">
                        <label class="b-input-line__label" for="street-adress">Улица</label>
                        <span class="b-input-line__require">(обязательно)</span>
                    </div>
                    <input class="b-input b-input--registration-form" type="text" id="street-adress" placeholder="" />
                </div>
                <div class="b-registration__wrapper-input">
                    <div class="b-input-line b-input-line--popup-authorization b-input-line--popup-add-adress">
                        <div class="b-input-line__label-wrapper">
                            <label class="b-input-line__label" for="home-adress">Дом</label>
                            <span class="b-input-line__require">(обязательно)</span>
                        </div>
                        <input class="b-input b-input--registration-form" type="text" id="home-adress" placeholder="" />
                    </div>
                    <div class="b-input-line b-input-line--popup-authorization b-input-line--popup-add-adress">
                        <div class="b-input-line__label-wrapper">
                            <label class="b-input-line__label" for="housing-adress">Корпус</label>
                        </div>
                        <input class="b-input b-input--registration-form"
                               type="text"
                               id="housing-adress"
                               placeholder="" />
                    </div>
                    <div class="b-input-line b-input-line--popup-authorization b-input-line--popup-add-adress">
                        <div class="b-input-line__label-wrapper">
                            <label class="b-input-line__label" for="porch-adress">Подъезд</label>
                        </div>
                        <input class="b-input b-input--registration-form"
                               type="text"
                               id="porch-adress"
                               placeholder="" />
                    </div>
                    <div class="b-input-line b-input-line--popup-authorization b-input-line--popup-add-adress">
                        <div class="b-input-line__label-wrapper">
                            <label class="b-input-line__label" for="floor-adress">Этаж</label>
                        </div>
                        <input class="b-input b-input--registration-form"
                               type="text"
                               id="floor-adress"
                               placeholder="" />
                    </div>
                    <div class="b-input-line b-input-line--popup-authorization b-input-line--popup-add-adress">
                        <div class="b-input-line__label-wrapper">
                            <label class="b-input-line__label" for="flat-adress">Квартира, офис</label>
                        </div>
                        <input class="b-input b-input--registration-form" type="text" id="flat-adress" placeholder="" />
                    </div>
                    <div class="b-input-line b-input-line--popup-authorization b-input-line--popup-add-adress">
                        <div class="b-input-line__label-wrapper">
                            <label class="b-input-line__label" for="doorphone-code-adress">Код домофона</label>
                        </div>
                        <input class="b-input b-input--registration-form"
                               type="text"
                               id="doorphone-code-adress"
                               placeholder="" />
                    </div>
                </div>
                <div class="b-checkbox b-checkbox--add-adress">
                    <input class="b-checkbox__input" type="checkbox" name="main-adress" id="main-adress" />
                    <label class="b-checkbox__name b-checkbox__name--add-adress"
                           for="main-adress"><span class="b-checkbox__text">Основной адрес</span>
                    </label>
                </div>
                <button class="b-button b-button--subscribe-delivery">Добавить</button>
            </form>
        </div>
    </section>
    <section class="b-popup-pick-city b-popup-pick-city--add-pet js-popup-section" data-popup="edit-popup-pet">
        <a class="b-popup-pick-city__close b-popup-pick-city__close--add-pet js-close-popup"
           href="javascript:void(0);"
           title="Закрыть"></a>
        <div class="b-registration b-registration--add-pet">
            <header class="b-registration__header">
                <h1 class="b-title b-title--h1 b-title--registration">Дэймон</h1>
            </header>
            <form class="b-registration__form js-form-validation">
                <div class="b-registration__wrapper-avatar">
                    <div class="b-registration__add-photos">
                        <input class="b-registration__load"
                               type="file"
                               name="load-avatar"
                               accept="image/*,image/jpeg" /><span
                                class="b-icon b-icon--upload"><svg class="b-icon__svg"
                                                                   viewBox="0 0 69 57 "
                                                                   width="69px"
                                                                   height="57px"><use class="b-icon__use"
                                                                                      xlink:href="icons.svg#icon-upload"></use></svg></span>
                        <div class="b-registration__text b-registration__text--upload">Перетащите картинку сюда или
                                                                                       нажмите на область для выбора
                                                                                       файла
                        </div>
                    </div>
                    <a class="b-registration__link-pet"
                       href="javascript:void(0);"
                       title="Дэймон"><span class="b-icon b-icon--pet-edit"><svg class="b-icon__svg"
                                                                                 viewBox="0 0 25 25 "
                                                                                 width="25px"
                                                                                 height="25px"><use class="b-icon__use"
                                                                                                    xlink:href="icons.svg#icon-edit"></use></svg></span><img
                                class="b-registration__image js-image-wrapper"
                                src="images/content/dog.jpg"
                                alt="Дэймон"
                                title="" /></a>
                </div>
                <div class="b-input-line b-input-line--popup-authorization b-input-line--popup-pet">
                    <div class="b-input-line__label-wrapper">
                        <label class="b-input-line__label" for="name-pet">Имя питомца</label>
                    </div>
                    <input class="b-input b-input--registration-form" type="text" id="name-pet" placeholder="" />
                </div>
                <label class="b-registration__label b-registration__label--subscribe-delivery">Вид питомца</label>
                <div class="b-select b-select--subscribe-delivery">
                    <select class="b-select__block b-select__block--subscribe-delivery" name="pet">
                        <option value="pet-0">Кот</option>
                        <option value="pet-1">Пес</option>
                        <option value="pet-2">Овца</option>
                    </select>
                </div>
                <div class="b-input-line b-input-line--popup-authorization b-input-line--popup-pet">
                    <div class="b-input-line__label-wrapper">
                        <label class="b-input-line__label" for="breed-pet">Порода</label>
                    </div>
                    <input class="b-input b-input--registration-form" type="text" id="breed-pet" placeholder="" />
                </div>
                <div class="b-input-line b-input-line--popup-authorization b-input-line--popup-pet">
                    <div class="b-input-line__label-wrapper">
                        <label class="b-input-line__label" for="birthday-pet">Дата рождения</label>
                    </div>
                    <input class="b-input b-input--registration-form"
                           type="date"
                           id="birthday-pet"
                           placeholder="ДД.ММ.ГГГГ" />
                </div>
                <div class="b-registration__wrapper-radio">
                    <div class="b-radio b-radio--add-pet">
                        <input class="b-radio__input" type="radio" name="sex" id="male" />
                        <label class="b-radio__label b-radio__label--add-pet"
                               for="male"><span class="b-radio__text-label">Мальчик</span>
                        </label>
                    </div>
                    <div class="b-radio b-radio--add-pet">
                        <input class="b-radio__input" type="radio" name="sex" id="female" />
                        <label class="b-radio__label b-radio__label--add-pet"
                               for="female"><span class="b-radio__text-label">Девочка</span>
                        </label>
                    </div>
                </div>
                <button class="b-button b-button--subscribe-delivery">Сохранить</button>
            </form>
        </div>
    </section>
    <?php $APPLICATION->IncludeComponent(
        'fourpaws:personal.profile',
        'popupChangePassword',
        [],
        $component,
        ['HIDE_ICONS' => 'Y']
    ); ?>
    <?php $APPLICATION->IncludeComponent(
        'fourpaws:personal.profile',
        'popupChangeData',
        [],
        $component,
        ['HIDE_ICONS' => 'Y']
    ); ?>
    <?php $APPLICATION->IncludeComponent(
        'fourpaws:personal.profile',
        'popupChangePhone',
        [],
        $component,
        ['HIDE_ICONS' => 'Y']
    ); ?>
    <div class="b-popup-wrapper__wrapper-modal">
        <section class="b-popup-pick-city b-popup-pick-city--choose-gift js-popup-section"
                 data-popup="popup-choose-gift">
            <a class="b-popup-pick-city__close b-popup-pick-city__close--choose-gift js-close-popup"
               href="javascript:void(0);"
               title="Закрыть"></a>
            <header class="b-popup-pick-city__header-popup b-popup-pick-city__header-popup--choose-gift">
                <h1 class="b-title b-title--h1 b-title--choose-gift">Выберите 2 подарка</h1>
            </header>
            <div class="b-common-item b-common-item--popup-gift">
                <span class="b-common-item__image-wrap b-common-item__image-wrap--popup-gift"><img class="b-common-item__image b-common-item__image--popup-gift"
                                                                                                   src="images/content/hills-cat.jpg"
                                                                                                   alt="для щенков с ягненком и рисом"
                                                                                                   title="" /></span>
                <div
                        class="b-common-item__info-center-block b-common-item__info-center-block--popup-gift">
                    <a class="b-common-item__description-wrap"
                       href="javascript:void(0);"
                       title=""><span class="b-clipped-text b-clipped-text--popup-gift"><span><strong>Хиллс</strong> для щенков с ягненком и рисом</span></span><span
                                class="b-common-item__variant b-common-item__variant--shopping b-common-item__variant--choose-gift"><span
                                    class="b-common-item__name-value">100 г</span></span></a>
                </div>
                <div class="b-choose-radio">
                    <input class="b-choose-radio__input" type="checkbox" name="hills-1" id="id-hills-gift-1" />
                    <label class="b-choose-radio__label" for="id-hills-gift-1"></label>
                </div>
            </div>
            <div class="b-common-item b-common-item--popup-gift">
                <span class="b-common-item__image-wrap b-common-item__image-wrap--popup-gift"><img class="b-common-item__image b-common-item__image--popup-gift"
                                                                                                   src="images/content/clean-cat.jpg"
                                                                                                   alt="собак мелкие и миниатюрные породы Лайт"
                                                                                                   title="" /></span>
                <div
                        class="b-common-item__info-center-block b-common-item__info-center-block--popup-gift">
                    <a class="b-common-item__description-wrap"
                       href="javascript:void(0);"
                       title=""><span class="b-clipped-text b-clipped-text--popup-gift"><span><strong>Хиллс</strong> собак мелкие и миниатюрные породы Лайт</span></span><span
                                class="b-common-item__variant b-common-item__variant--shopping b-common-item__variant--choose-gift"><span
                                    class="b-common-item__name-value">1 кг</span></span></a>
                </div>
                <div class="b-choose-radio">
                    <input class="b-choose-radio__input" type="checkbox" name="hills-2" id="id-hills-gift-2" />
                    <label class="b-choose-radio__label" for="id-hills-gift-2"></label>
                </div>
            </div>
            <div class="b-common-item b-common-item--popup-gift">
                <span class="b-common-item__image-wrap b-common-item__image-wrap--popup-gift"><img class="b-common-item__image b-common-item__image--popup-gift"
                                                                                                   src="images/content/royal-canin-2.jpg"
                                                                                                   alt="для собак мини пород Сеньор"
                                                                                                   title="" /></span>
                <div class="b-common-item__info-center-block b-common-item__info-center-block--popup-gift">
                    <a class="b-common-item__description-wrap"
                       href="javascript:void(0);"
                       title=""><span class="b-clipped-text b-clipped-text--popup-gift"><span><strong>Хиллс</strong> для собак мини пород Сеньор</span></span><span
                                class="b-common-item__variant b-common-item__variant--shopping b-common-item__variant--choose-gift"><span
                                    class="b-common-item__name-value">100 г</span></span></a>
                </div>
                <div class="b-choose-radio">
                    <input class="b-choose-radio__input" type="checkbox" name="hills-3" id="id-hills-gift-3" />
                    <label class="b-choose-radio__label" for="id-hills-gift-3"></label>
                </div>
            </div>
            <div class="b-common-item b-common-item--popup-gift">
                <span class="b-common-item__image-wrap b-common-item__image-wrap--popup-gift"><img class="b-common-item__image b-common-item__image--popup-gift"
                                                                                                   src="images/content/hills-cat.jpg"
                                                                                                   alt="ля собак идеальный вес состоит"
                                                                                                   title="" /></span>
                <div class="b-common-item__info-center-block b-common-item__info-center-block--popup-gift">
                    <a class="b-common-item__description-wrap"
                       href="javascript:void(0);"
                       title=""><span class="b-clipped-text b-clipped-text--popup-gift"><span><strong>Хиллс</strong> ля собак идеальный вес состоит</span></span><span
                                class="b-common-item__variant b-common-item__variant--shopping b-common-item__variant--choose-gift"><span
                                    class="b-common-item__name-value">100 г</span></span></a>
                </div>
                <div class="b-choose-radio">
                    <input class="b-choose-radio__input" type="checkbox" name="hills-4" id="id-hills-gift-4" />
                    <label class="b-choose-radio__label" for="id-hills-gift-4"></label>
                </div>
            </div>
            <div class="b-common-item b-common-item--popup-gift">
                <span class="b-common-item__image-wrap b-common-item__image-wrap--popup-gift"><img class="b-common-item__image b-common-item__image--popup-gift"
                                                                                                   src="images/content/royal-canin-2.jpg"
                                                                                                   alt="для собак миниатюрных пород с ягненком вкусным"
                                                                                                   title="" /></span>
                <div
                        class="b-common-item__info-center-block b-common-item__info-center-block--popup-gift">
                    <a class="b-common-item__description-wrap"
                       href="javascript:void(0);"
                       title=""><span class="b-clipped-text b-clipped-text--popup-gift"><span><strong>Хиллс</strong> для собак миниатюрных пород с ягненком вкусным</span></span><span
                                class="b-common-item__variant b-common-item__variant--shopping b-common-item__variant--choose-gift"><span
                                    class="b-common-item__name-value">100 г</span></span></a>
                </div>
                <div class="b-choose-radio">
                    <input class="b-choose-radio__input" type="checkbox" name="hills-5" id="id-hills-gift-5" />
                    <label class="b-choose-radio__label" for="id-hills-gift-5"></label>
                </div>
            </div>
        </section>
    </div>
</div>
