<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

$APPLICATION->SetPageProperty('title', 'Сервисы флагманского магазина');
$APPLICATION->SetPageProperty('description', '');
$APPLICATION->SetTitle("Сервисы флагманского магазина");

use FourPaws\Decorators\SvgDecorator;

?>
<div class="flagship-store-page">
    <section class="banner-flagship-store">
        <div class="b-container">
            <div class="banner-flagship-store__title">Сервисы<br/> флагманского магазина</div>
            <div class="banner-flagship-store__subtitle">
                <span class="b-icon">
                    <?= new SvgDecorator('icon-delivery-header', 24, 24) ?>
                </span>
                <span>ул. Вавилова, 3, ТРК Гагаринский</span>
            </div>
        </div>
    </section>

    <section class="nav-flagship-store">
        <div class="b-container">
            <div class="nav-flagship-store__list">
                <div class="nav-flagship-store__item" data-nav-flagship-store="grooming">
                    <div class="nav-flagship-store__icon">
                        <?= new SvgDecorator('icon-flagship-grooming', 61, 61) ?>
                    </div>
                    <div class="nav-flagship-store__title">Груминг</div>
                </div>
                <div class="nav-flagship-store__item" data-nav-flagship-store="lectures">
                    <div class="nav-flagship-store__icon">
                        <?= new SvgDecorator('icon-flagship-lectures', 61, 61) ?>
                    </div>
                    <div class="nav-flagship-store__title">Лекции</div>
                </div>
                <div class="nav-flagship-store__item" data-nav-flagship-store="walking">
                    <div class="nav-flagship-store__icon">
                        <?= new SvgDecorator('icon-flagship-walking', 61, 61) ?>
                    </div>
                    <div class="nav-flagship-store__title">Выгул-тренировка</div>
                </div>
            </div>
        </div>
    </section>

    <section class="service-flagship-store" data-item-service-flagship-store="grooming">
        <div class="b-container">
            <div class="service-flagship-store__header service-flagship-store__header_grooming">
                <div class="service-flagship-store__inner-header">
                    <div class="service-flagship-store__title">Груминг</div>
                    <div class="service-flagship-store__btn active" data-toggle-service-flagship-store="true"></div>
                </div>
            </div>
            <div class="service-flagship-store__content" data-content-service-flagship-store="true" style="display: block">
                <div class="service-flagship-store__descr">
                    Груминг&nbsp;&mdash; это уход за&nbsp;внешностью животного, его кожей и&nbsp;шерстью, когтями и&nbsp;ушами.
                    Первоначально этот термин означал совокупность гигиенических процедур по&nbsp;уходу за&nbsp;домашними питомцами.
                </div>
                <form class="form-signup-grooming-flagship js-form-validation">
                    <div class="form-signup-grooming-flagship__content">
                        <div class="b-input-line">
                            <div class="b-input-line__label-wrapper">
                                <span class="b-input-line__label">Дата</span>
                            </div>
                            <div class="b-select">
                                <select class="b-select__block">
                                    <option value="" disabled="disabled" selected="selected">выберите</option>
                                    <option value="0" data-date-option="Четверг, 2019-11-07">Четверг, 07.11.2019</option>
                                    <option value="1" data-date-option="Пятница, 2019-11-08">Пятница, 08.11.2019</option>
                                    <option value="2" data-date-option="Суббота, 2019-11-09">Суббота, 09.11.2019</option>
                                    <option value="3" data-date-option="Воскресенье, 2019-11-10">Воскресенье, 10.11.2019</option>
                                    <option value="4" data-date-option="Понедельник, 2019-11-11">Понедельник, 11.11.2019</option>
                                    <option value="5" data-date-option="Вторник, 2019-11-12">Вторник, 12.11.2019</option>
                                    <option value="6" data-date-option="Среда, 2019-11-13">Среда, 13.11.2019</option>
                                    <option value="7" data-date-option="Четверг, 2019-11-14">Четверг, 14.11.2019</option>
                                    <option value="8" data-date-option="Пятница, 2019-11-15">Пятница, 15.11.2019</option>
                                    <option value="9" data-date-option="Суббота, 2019-11-16">Суббота, 16.11.2019</option>
                                </select>
                                <div class="b-error"><span class="js-message"></span></div>
                            </div>
                        </div>

                        <div class="b-input-line">
                            <div class="b-input-line__label-wrapper">
                                <span class="b-input-line__label">Время</span>
                            </div>
                            <div class="b-select">
                                <select class="b-select__block">
                                    <option value="" disabled="disabled" selected="selected">выберите</option>
                                    <option value="1">10:00 - 14:00</option>
                                    <option value="2">14:00 - 18:00</option>
                                    <option value="3">18:00 - 22:00</option>
                                    <option value="4">20:00 - 00:00</option>
                                    <option value="5">10:00 - 18:00</option>
                                    <option value="6">18:00 - 00:00</option>
                                </select>
                                <div class="b-error"><span class="js-message"></span></div>
                            </div>
                        </div>

                        <div class="b-input-line b-input-line--popup-authorization b-input-line--popup-pet js-breed">
                            <div class="b-input-line__label-wrapper">
                                <label class="b-input-line__label" for="breed-pet">Порода</label>
                            </div>
                            <div class="b-input b-input--registration-form">
                                <input class="js-id-breed-pet-form-add-pet js-no-valid" name="UF_BREED_ID" value="" type="hidden">
                                <div class="b-select b-select--select2" data-wrap-breed-pet-form-add-pet="true" data-id="breed-pet" data-name="UF_BREED">
                                    <select class="b-select__block" id="breed-pet" name="UF_BREED">
                                        <option value="" disabled="disabled" selected="selected">Выберите породу</option>
                                    </select>
                                </div>
                                <div class="b-error"><span class="js-message"></span>
                                </div>
                            </div>
                        </div>

                        <div class="b-input-line">
                            <div class="b-input-line__label-wrapper">
                                <span class="b-input-line__label">Услуга</span>
                            </div>
                            <div class="b-select">
                                <select class="b-select__block">
                                    <option value="" disabled="disabled" selected="selected">выберите</option>
                                    <option value="0" data-date-option="Четверг, 2019-11-07">Мытье и сушка</option>
                                </select>
                            </div>
                        </div>

                        <div class="b-button js-open-popup" data-popup-id="grooming-flagship-store">Записаться</div>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <section class="service-flagship-store" data-item-service-flagship-store="lectures">
        <div class="b-container">
            <div class="service-flagship-store__header service-flagship-store__header_lectures">
                <div class="service-flagship-store__inner-header">
                    <div class="service-flagship-store__title">Лекции</div>
                    <div class="service-flagship-store__btn" data-toggle-service-flagship-store="true"></div>
                </div>
            </div>
            <div class="service-flagship-store__content" data-content-service-flagship-store="true">
                <div class="service-flagship-store__descr">
                    Cтартовал курс тематических лекций, посвящённых здоровью кошек и&nbsp;собак. Вам расскажут, как правильно ухаживать
                    за&nbsp;четвероногими любимцами, купать, кормить и&nbsp;лечить их, как путешествовать с&nbsp;животными.
                </div>

                <div class="lectures-flagship-store">
                    <div class="lectures-flagship-store__list">
                        <div class="item">
                            <div class="item__img" style="background-image: url('/events/img/lectures1.jpg')"></div>
                            <div class="item__content">
                                <div class="item__info">
                                    <div class="item__count disabled" data-label-lectures-flagship-store="true">50 из 50 мест заняты</div>
                                    <div class="item__title">Мастер-классы – встречи друзей!</div>
                                </div>
                                <div class="item__datetime">
                                    <div class="item__col-date">
                                        <div class="item__subtitle">Дата</div>
                                        <div class="item__text">11 июля 2017, суббота</div>
                                    </div>
                                    <div class="item__col-time">
                                        <div class="item__subtitle">Время</div>
                                        <div class="item__text">10:20</div>
                                    </div>
                                </div>
                                <div class="item__btn">
                                    <div class="b-button disabled" data-id-lectures-flagship-store="1">Запись окончена</div>
                                </div>
                            </div>
                        </div>

                        <div class="item">
                            <div class="item__img" style="background-image: url('/events/img/lectures2.jpg')"></div>
                            <div class="item__content">
                                <div class="item__info">
                                    <div class="item__count" data-label-lectures-flagship-store="true">10 из 50 свободных мест</div>
                                    <div class="item__title">Как мыть вашего пса?</div>
                                </div>
                                <div class="item__datetime">
                                    <div class="item__col-date">
                                        <div class="item__subtitle">Дата</div>
                                        <div class="item__text">11 июля 2017, суббота</div>
                                    </div>
                                    <div class="item__col-time">
                                        <div class="item__subtitle">Время</div>
                                        <div class="item__text">15:30</div>
                                    </div>
                                </div>
                                <div class="item__btn">
                                    <div class="b-button disabled selected" data-id-lectures-flagship-store="2">Вы записаны</div>
                                </div>
                            </div>
                        </div>

                        <div class="item">
                            <div class="item__img" style="background-image: url('/events/img/lectures3.jpg')"></div>
                            <div class="item__content">
                                <div class="item__info">
                                    <div class="item__count" data-label-lectures-flagship-store="true">10 из 50 свободных мест</div>
                                    <div class="item__title">Мастер-классы – встречи друзей!</div>
                                </div>
                                <div class="item__datetime">
                                    <div class="item__col-date">
                                        <div class="item__subtitle">Дата</div>
                                        <div class="item__text">11 июля 2017, суббота</div>
                                    </div>
                                    <div class="item__col-time">
                                        <div class="item__subtitle">Время</div>
                                        <div class="item__text">18:30</div>
                                    </div>
                                </div>
                                <div class="item__btn">
                                    <div class="b-button js-open-popup" data-popup-id="lectures-flagship-store" data-id-lectures-flagship-store="3">Записаться</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="service-flagship-store service-flagship-store_walking" data-item-service-flagship-store="walking">
        <div class="b-container">
            <div class="service-flagship-store__header service-flagship-store__header_walking">
                <div class="service-flagship-store__inner-header">
                    <div class="service-flagship-store__title"><nobr>Выгул-тренировка</nobr><br/> собак</div>
                    <div class="service-flagship-store__btn" data-toggle-service-flagship-store="true"></div>
                </div>
            </div>
            <div class="service-flagship-store__content" data-content-service-flagship-store="true">
                <div class="steps-walking-flagship-store">
                    <div class="steps-walking-flagship-store__title">Как проходит тренировка?</div>
                    <div class="steps-walking-flagship-store__list">
                        <div class="item">
                            <div class="item__number">1</div>
                            <div class="item__descr">
                                Вы&nbsp;приводите своего пса к&nbsp;нам в&nbsp;магазин в&nbsp;выбраный день и&nbsp;время, сытым и&nbsp;после прогулки.
                            </div>
                        </div>
                        <div class="item">
                            <div class="item__number">2</div>
                            <div class="item__descr">
                                Проводим вам краткую лекцию по&nbsp;обращению с&nbsp;псом, показываем приемы дресссировки
                            </div>
                        </div>
                        <div class="item">
                            <div class="item__number">3</div>
                            <div class="item__descr">
                                Гуляем и&nbsp;развлекаем<br/> вашего любимца оговоренное время
                            </div>
                        </div>
                    </div>
                </div>

                <div class="timetable-walking-flagship-store">
                    <div class="timetable-walking-flagship-store__title">Расписание</div>

                    <div class="timetable-walking-flagship-store__list">
                        <div class="item" data-group-interval-walking-flagship="true">
                            <div class="item__date">11 июля 2017, суббота</div>

                            <div class="item__interval item__interval_mobile">
                                <div class="b-input-line">
                                    <div class="b-input-line__label-wrapper">
                                        <span class="b-input-line__label">Интервал</span>
                                    </div>
                                    <div class="b-select">
                                        <select class="b-select__block" disabled>
                                            <option value="" disabled="disabled" selected="selected">выберите</option>
                                            <option value="1" data-id-interval-walking-flagship="1">10:00 — 12:00</option>
                                            <option value="2" data-id-interval-walking-flagship="2">12:00 — 14:00</option>
                                            <option value="3" data-id-interval-walking-flagship="3">14:00 — 16:00</option>
                                            <option value="4" data-id-interval-walking-flagship="4">16:00 — 18:00</option>
                                            <option value="5" data-id-interval-walking-flagship="5">18:00 — 20:00</option>
                                        </select>
                                        <div class="b-error"><span class="js-message"></span></div>
                                    </div>
                                </div>
                            </div>

                            <div class="item__interval">
                                <div class="item__btn-interval disabled" data-id-interval-walking-flagship="1">10:00 — 12:00</div>
                                <div class="item__btn-interval disabled" data-id-interval-walking-flagship="2">12:00 — 14:00</div>
                                <div class="item__btn-interval disabled" data-id-interval-walking-flagship="3">14:00 — 16:00</div>
                                <div class="item__btn-interval disabled" data-id-interval-walking-flagship="4">16:00 — 18:00</div>
                                <div class="item__btn-interval disabled" data-id-interval-walking-flagship="5">18:00 — 20:00</div>
                            </div>

                            <div class="b-button disabled" disabled data-select-interval-walking-flagship="true">Запись окончена</div>
                        </div>

                        <div class="item" data-group-interval-walking-flagship="true">
                            <div class="item__date">12 июля 2017, воскресенье</div>

                            <div class="item__interval item__interval_mobile">
                                <div class="b-input-line">
                                    <div class="b-input-line__label-wrapper">
                                        <span class="b-input-line__label">Интервал</span>
                                    </div>
                                    <div class="b-select">
                                        <select class="b-select__block" disabled>
                                            <option value="" disabled="disabled" selected="selected">выберите</option>
                                            <option value="1" data-id-interval-walking-flagship="6">10:00 — 12:00</option>
                                            <option value="2" data-id-interval-walking-flagship="7">12:00 — 14:00</option>
                                            <option value="3" data-id-interval-walking-flagship="8">14:00 — 16:00</option>
                                            <option value="4" data-id-interval-walking-flagship="9">16:00 — 18:00</option>
                                            <option value="5" data-id-interval-walking-flagship="10">18:00 — 20:00</option>
                                        </select>
                                        <div class="b-error"><span class="js-message"></span></div>
                                    </div>
                                </div>
                            </div>

                            <div class="item__interval">
                                <div class="item__btn-interval" data-id-interval-walking-flagship="6">10:00 — 12:00</div>
                                <div class="item__btn-interval active" data-id-interval-walking-flagship="7">12:00 — 14:00</div>
                                <div class="item__btn-interval disabled" data-id-interval-walking-flagship="8">14:00 — 16:00</div>
                                <div class="item__btn-interval" data-id-interval-walking-flagship="9">16:00 — 18:00</div>
                                <div class="item__btn-interval" data-id-interval-walking-flagship="10">18:00 — 20:00</div>
                            </div>

                            <div class="b-button disabled selected" disabled data-select-interval-walking-flagship="true">Вы записаны</div>
                        </div>

                        <div class="item" data-group-interval-walking-flagship="true">
                            <div class="item__date">13 июля 2017, понедельник</div>

                            <div class="item__interval item__interval_mobile">
                                <div class="b-input-line">
                                    <div class="b-input-line__label-wrapper">
                                        <span class="b-input-line__label">Интервал</span>
                                    </div>
                                    <div class="b-select">
                                        <select class="b-select__block">
                                            <option value="" disabled="disabled" selected="selected">выберите</option>
                                            <option value="1" data-id-interval-walking-flagship="11">10:00 — 12:00</option>
                                            <option value="2" data-id-interval-walking-flagship="12">12:00 — 14:00</option>
                                            <option value="3" data-id-interval-walking-flagship="13">14:00 — 16:00</option>
                                            <option value="4" data-id-interval-walking-flagship="14">16:00 — 18:00</option>
                                            <option value="5" data-id-interval-walking-flagship="15">18:00 — 20:00</option>
                                        </select>
                                        <div class="b-error"><span class="js-message"></span></div>
                                    </div>
                                </div>
                            </div>

                            <div class="item__interval">
                                <div class="item__btn-interval" data-id-interval-walking-flagship="11">10:00 — 12:00</div>
                                <div class="item__btn-interval" data-id-interval-walking-flagship="12">12:00 — 14:00</div>
                                <div class="item__btn-interval" data-id-interval-walking-flagship="13">14:00 — 16:00</div>
                                <div class="item__btn-interval" data-id-interval-walking-flagship="14">16:00 — 18:00</div>
                                <div class="item__btn-interval" data-id-interval-walking-flagship="15">18:00 — 20:00</div>
                            </div>

                            <div class="b-button js-open-popup" data-popup-id="walking-flagship-store" data-select-interval-walking-flagship="true">Выберите интервал</div>
                        </div>

                        <div class="item" data-group-interval-walking-flagship="true">
                            <div class="item__date">14 июля 2017, вторник</div>

                            <div class="item__interval item__interval_mobile">
                                <div class="b-input-line">
                                    <div class="b-input-line__label-wrapper">
                                        <span class="b-input-line__label">Интервал</span>
                                    </div>
                                    <div class="b-select">
                                        <select class="b-select__block">
                                            <option value="" disabled="disabled" selected="selected">выберите</option>
                                            <option value="1" data-id-interval-walking-flagship="16">10:00 — 12:00</option>
                                            <option value="2" data-id-interval-walking-flagship="17">12:00 — 14:00</option>
                                            <option value="3" data-id-interval-walking-flagship="18">14:00 — 16:00</option>
                                            <option value="4" data-id-interval-walking-flagship="19">16:00 — 18:00</option>
                                            <option value="5" data-id-interval-walking-flagship="20">18:00 — 20:00</option>
                                        </select>
                                        <div class="b-error"><span class="js-message"></span></div>
                                    </div>
                                </div>
                            </div>

                            <div class="item__interval">
                                <div class="item__btn-interval" data-id-interval-walking-flagship="16">10:00 — 12:00</div>
                                <div class="item__btn-interval" data-id-interval-walking-flagship="17">12:00 — 14:00</div>
                                <div class="item__btn-interval" data-id-interval-walking-flagship="18">14:00 — 16:00</div>
                                <div class="item__btn-interval" data-id-interval-walking-flagship="19">16:00 — 18:00</div>
                                <div class="item__btn-interval" data-id-interval-walking-flagship="20">18:00 — 20:00</div>
                            </div>

                            <div class="b-button js-open-popup" data-popup-id="walking-flagship-store" data-select-interval-walking-flagship="true">Выберите интервал</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?/*
<script>
    window.addEventListener('load', function() {
        var items = document.querySelectorAll('.fashion-page .measure_dog__button.js-scroll-to-catalog, .fashion-page .b-news-item__link, .fashion-page .measure_dog__steps a, .b-main-slider a.b-main-item__link-main');

        for (var i = 0; i < items.length; i++) {
            items[i].setAttribute('target', '_blank');
            items[i].target = '_blank';
        }
    });
</script>
*/?>
<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'; ?>
