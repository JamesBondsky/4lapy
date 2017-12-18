<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';
$APPLICATION->SetTitle('Реферальная программа');
?>
    
    <div class="b-account-referal">
        <div class="b-account-referal-top">
            <div class="b-account-referal-top__info-block">
                <a class="b-link b-link--add-referal js-open-popup js-open-popup--add-referal"
                   href="javascript:void(0)"
                   title="Добавить реферала"
                   data-popup-id="add-referal"><span class="b-link__text b-link__text--add-referal">Добавить реферала</span></a>
                <div
                        class="b-account-referal-top__text">Начисление баллов начнется после
                                                            успешной проверки данных
                </div>
            </div>
            <div class="b-account-referal-top__search">
                <div class="b-form-inline b-form-inline--search-referal">
                    <form class="b-form-inline__form b-form-inline__form--search-referal">
                        <input class="b-input"
                               type="text"
                               id="referal-search"
                               placeholder="Найти реферала" />
                        <button class="b-button b-button--form-inline b-button--search-referal">
                                                    <span class="b-icon"><svg class="b-icon__svg"
                                                                              viewBox="0 0 16 16 "
                                                                              width="16px"
                                                                              height="16px"><use class="b-icon__use"
                                                                                                 xlink:href="icons.svg#icon-search"></use></svg></span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="b-account-referal__bottom">
            <div class="b-account-referal__title">Список рефералов</div>
            <div class="b-account-referal__link-block">
                <div class="b-account-referal__full-number">
                    <div class="b-tab-title b-tab-title--referal">
                        <ul class="b-tab-title__list b-tab-title__list--referal">
                            <li class="b-tab-title__item active js-tab-referal-item">
                                <a class="b-tab-title__link js-referal-link"
                                   href="javascript:void(0);"
                                   title="Все "
                                   data-tab="all"><span class="b-tab-title__text">Все <span
                                                class="b-tab-title__number">(11)</span></span></a>
                            </li>
                            <li class="b-tab-title__item js-tab-referal-item">
                                <a class="b-tab-title__link js-referal-link"
                                   href="javascript:void(0);"
                                   title="Активные "
                                   data-tab="active-referal"><span class="b-tab-title__text">Активные <span
                                                class="b-tab-title__number">(3)</span></span></a>
                            </li>
                            <li class="b-tab-title__item js-tab-referal-item">
                                <a class="b-tab-title__link js-referal-link"
                                   href="javascript:void(0);"
                                   title="На модерации "
                                   data-tab="moderate"><span class="b-tab-title__text">На модерации <span
                                                class="b-tab-title__number">(1)</span></span></a>
                            </li>
                        </ul>
                    </div>
                    <div class="b-account-referal__text-number">Начислено за все время <span>2 456</span>
                        <span class="b-ruble b-ruble--referal">&nbsp;₽</span>
                    </div>
                </div>
                <ul class="b-account-referal__list">
                    <li class="b-account-referal-item js-item-referal"
                        data-referal="active-referal">
                        <div class="b-account-referal-item__wrapper">
                            <div class="b-account-referal-item__column">
                                <div class="b-account-referal-item__title">Константинопольский
                                                                           Константин
                                                                           Константинович
                                </div>
                                <div class="b-account-referal-item__info">
                                    <div class="b-account-referal-item__info-text b-account-referal-item__info-text--number">
                                        +7 (916) 234-45-67
                                    </div>
                                    <div class="b-account-referal-item__info-text b-account-referal-item__info-text--email">
                                        ekaterina-pavlova-777@mail.com
                                    </div>
                                    <div class="b-account-referal-item__info-text b-account-referal-item__info-text--card">
                                        26000 234 57 345
                                    </div>
                                </div>
                            </div>
                            <div class="b-account-referal-item__column">
                                <div class="b-account-referal-item__bonus">Начислено бонусов
                                    <span class="b-account-referal-item__number"><span>10 048</span><span
                                                class="b-ruble b-ruble--referal-item">&nbsp;₽</span></span>
                                </div>
                                <div class="b-account-referal-item__status b-account-referal-item__status--active">
                                    Активна до <span>10 ноября 2017</span>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li class="b-account-referal-item js-item-referal"
                        data-referal="active-referal">
                        <div class="b-account-referal-item__wrapper">
                            <div class="b-account-referal-item__column">
                                <div class="b-account-referal-item__title">Щербаков Алексей
                                </div>
                                <div class="b-account-referal-item__info">
                                    <div class="b-account-referal-item__info-text b-account-referal-item__info-text--number">
                                        +7 (916) 234-45-67
                                    </div>
                                    <div class="b-account-referal-item__info-text b-account-referal-item__info-text--email">
                                        alex_she88@mail.com
                                    </div>
                                    <div class="b-account-referal-item__info-text b-account-referal-item__info-text--card">
                                        26000 234 57 345
                                    </div>
                                </div>
                            </div>
                            <div class="b-account-referal-item__column">
                                <div class="b-account-referal-item__bonus">Начислено бонусов
                                    <span class="b-account-referal-item__number"><span>348</span><span
                                                class="b-ruble b-ruble--referal-item">&nbsp;₽</span></span>
                                </div>
                                <div class="b-account-referal-item__status b-account-referal-item__status--active">
                                    Активна до <span>10 ноября 2017</span>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li class="b-account-referal-item js-item-referal"
                        data-referal="active-referal">
                        <div class="b-account-referal-item__wrapper">
                            <div class="b-account-referal-item__column">
                                <div class="b-account-referal-item__title">Петров Андрей</div>
                                <div class="b-account-referal-item__info">
                                    <div class="b-account-referal-item__info-text b-account-referal-item__info-text--number">
                                        +7 (916) 234-45-67
                                    </div>
                                    <div class="b-account-referal-item__info-text b-account-referal-item__info-text--email">
                                        kostya@mail.com
                                    </div>
                                    <div class="b-account-referal-item__info-text b-account-referal-item__info-text--card">
                                        26000 234 57 345
                                    </div>
                                </div>
                            </div>
                            <div class="b-account-referal-item__column">
                                <div class="b-account-referal-item__bonus">Начислено бонусов
                                    <span class="b-account-referal-item__number"><span>5</span><span
                                                class="b-ruble b-ruble--referal-item">&nbsp;₽</span></span>
                                </div>
                                <div class="b-account-referal-item__status b-account-referal-item__status--active">
                                    Активна до <span>10 ноября 2017</span>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li class="b-account-referal-item js-item-referal" data-referal="moderate">
                        <div class="b-account-referal-item__wrapper">
                            <div class="b-account-referal-item__column">
                                <div class="b-account-referal-item__title">Константинопольский
                                                                           Константин
                                                                           Константинович
                                </div>
                                <div class="b-account-referal-item__info">
                                    <div class="b-account-referal-item__info-text b-account-referal-item__info-text--number">
                                        +7 (916) 234-45-67
                                    </div>
                                    <div class="b-account-referal-item__info-text b-account-referal-item__info-text--email">
                                        ekaterina-pavlova-777@mail.com
                                    </div>
                                    <div class="b-account-referal-item__info-text b-account-referal-item__info-text--card">
                                        26000 234 57 345
                                    </div>
                                </div>
                            </div>
                            <div class="b-account-referal-item__column">
                                <div class="b-account-referal-item__bonus">Начислено бонусов
                                    <span class="b-account-referal-item__number"><span>0</span><span
                                                class="b-ruble b-ruble--referal-item">&nbsp;₽</span></span>
                                </div>
                                <div class="b-account-referal-item__status b-account-referal-item__status--moderate">
                                    На модерации
                                </div>
                            </div>
                        </div>
                    </li>
                    <li class="b-account-referal-item js-item-referal"
                        data-referal="not-active">
                        <div class="b-account-referal-item__wrapper">
                            <div class="b-account-referal-item__column">
                                <div class="b-account-referal-item__title">Михайлов Николай
                                </div>
                                <div class="b-account-referal-item__info">
                                    <div class="b-account-referal-item__info-text b-account-referal-item__info-text--card">
                                        26000 234 57 345
                                    </div>
                                </div>
                            </div>
                            <div class="b-account-referal-item__column">
                                <div class="b-account-referal-item__bonus">Начислено бонусов
                                    <span class="b-account-referal-item__number"><span>145</span><span
                                                class="b-ruble b-ruble--referal-item">&nbsp;₽</span></span>
                                </div>
                                <div class="b-account-referal-item__status b-account-referal-item__status--not-active">
                                    Неактивна <span>с 1 сентября 2017</span>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li class="b-account-referal-item js-item-referal"
                        data-referal="not-active">
                        <div class="b-account-referal-item__wrapper">
                            <div class="b-account-referal-item__column">
                                <div class="b-account-referal-item__title">Михайлов Николай
                                </div>
                                <div class="b-account-referal-item__info">
                                    <div class="b-account-referal-item__info-text b-account-referal-item__info-text--number">
                                        +7 (916) 234-45-67
                                    </div>
                                    <div class="b-account-referal-item__info-text b-account-referal-item__info-text--card">
                                        26000 234 57 345
                                    </div>
                                </div>
                            </div>
                            <div class="b-account-referal-item__column">
                                <div class="b-account-referal-item__bonus">Начислено бонусов
                                    <span class="b-account-referal-item__number"><span>145</span><span
                                                class="b-ruble b-ruble--referal-item">&nbsp;₽</span></span>
                                </div>
                                <div class="b-account-referal-item__status b-account-referal-item__status--not-active">
                                    Неактивна <span>с 1 сентября 2017</span>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li class="b-account-referal-item js-item-referal"
                        data-referal="not-active">
                        <div class="b-account-referal-item__wrapper">
                            <div class="b-account-referal-item__column">
                                <div class="b-account-referal-item__title">Михайлов Николай
                                </div>
                                <div class="b-account-referal-item__info">
                                    <div class="b-account-referal-item__info-text b-account-referal-item__info-text--email">
                                        kostya@mail.com
                                    </div>
                                    <div class="b-account-referal-item__info-text b-account-referal-item__info-text--card">
                                        26000 234 57 345
                                    </div>
                                </div>
                            </div>
                            <div class="b-account-referal-item__column">
                                <div class="b-account-referal-item__bonus">Начислено бонусов
                                    <span class="b-account-referal-item__number"><span>145</span><span
                                                class="b-ruble b-ruble--referal-item">&nbsp;₽</span></span>
                                </div>
                                <div class="b-account-referal-item__status b-account-referal-item__status--not-active">
                                    Неактивна <span>с 1 сентября 2017</span>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li class="b-account-referal-item js-item-referal"
                        data-referal="active-referal">
                        <div class="b-account-referal-item__wrapper">
                            <div class="b-account-referal-item__column">
                                <div class="b-account-referal-item__title">Петров Андрей</div>
                                <div class="b-account-referal-item__info">
                                    <div class="b-account-referal-item__info-text b-account-referal-item__info-text--number">
                                        +7 (916) 234-45-67
                                    </div>
                                    <div class="b-account-referal-item__info-text b-account-referal-item__info-text--email">
                                        kostya@mail.com
                                    </div>
                                    <div class="b-account-referal-item__info-text b-account-referal-item__info-text--card">
                                        26000 234 57 345
                                    </div>
                                </div>
                            </div>
                            <div class="b-account-referal-item__column">
                                <div class="b-account-referal-item__bonus">Начислено бонусов
                                    <span class="b-account-referal-item__number"><span>5</span><span
                                                class="b-ruble b-ruble--referal-item">&nbsp;₽</span></span>
                                </div>
                                <div class="b-account-referal-item__status b-account-referal-item__status--active">
                                    Активна до <span>10 ноября 2017</span>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li class="b-account-referal-item js-item-referal" data-referal="moderate">
                        <div class="b-account-referal-item__wrapper">
                            <div class="b-account-referal-item__column">
                                <div class="b-account-referal-item__title">Константинопольский
                                                                           Константин
                                                                           Константинович
                                </div>
                                <div class="b-account-referal-item__info">
                                    <div class="b-account-referal-item__info-text b-account-referal-item__info-text--number">
                                        +7 (916) 234-45-67
                                    </div>
                                    <div class="b-account-referal-item__info-text b-account-referal-item__info-text--email">
                                        ekaterina-pavlova-777@mail.com
                                    </div>
                                    <div class="b-account-referal-item__info-text b-account-referal-item__info-text--card">
                                        26000 234 57 345
                                    </div>
                                </div>
                            </div>
                            <div class="b-account-referal-item__column">
                                <div class="b-account-referal-item__bonus">Начислено бонусов
                                    <span class="b-account-referal-item__number"><span>0</span><span
                                                class="b-ruble b-ruble--referal-item">&nbsp;₽</span></span>
                                </div>
                                <div class="b-account-referal-item__status b-account-referal-item__status--moderate">
                                    На модерации
                                </div>
                            </div>
                        </div>
                    </li>
                    <li class="b-account-referal-item js-item-referal"
                        data-referal="not-active">
                        <div class="b-account-referal-item__wrapper">
                            <div class="b-account-referal-item__column">
                                <div class="b-account-referal-item__title">Михайлов Николай
                                </div>
                                <div class="b-account-referal-item__info">
                                    <div class="b-account-referal-item__info-text b-account-referal-item__info-text--email">
                                        kostya@mail.com
                                    </div>
                                    <div class="b-account-referal-item__info-text b-account-referal-item__info-text--card">
                                        26000 234 57 345
                                    </div>
                                </div>
                            </div>
                            <div class="b-account-referal-item__column">
                                <div class="b-account-referal-item__bonus">Начислено бонусов
                                    <span class="b-account-referal-item__number"><span>145</span><span
                                                class="b-ruble b-ruble--referal-item">&nbsp;₽</span></span>
                                </div>
                                <div class="b-account-referal-item__status b-account-referal-item__status--not-active">
                                    Неактивна <span>с 1 сентября 2017</span>
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="b-pagination b-pagination--referal">
                <ul class="b-pagination__list">
                    <li class="b-pagination__item b-pagination__item--prev b-pagination__item--disabled">
                        <span class="b-pagination__link">Назад</span>
                    </li>
                    <li class="b-pagination__item"><a class="b-pagination__link"
                                                      href="javascript:void(0);"
                                                      title="2">2</a>
                    </li>
                    <li class="b-pagination__item b-pagination__item--next">
                        <a class="b-pagination__link" href="javascript:void(0);" title="Вперед">Вперед</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'; ?>