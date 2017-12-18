<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
} ?>
<div class="b-account-bonus">
    <div class="b-account-bonus-card">
        <div class="b-account-bonus-card__colored-block">
            <div class="b-account-bonus-card__line">
                <div class="b-account-bonus-card__column">
                    <div class="b-account-bonus-card__title">Баланс</div>
                    <div class="b-account-bonus-card__number">164</div>
                </div>
                <div class="b-account-bonus-card__column b-account-bonus-card__column--bonus">
                    <div class="b-account-bonus-card__title">Бонус</div>
                    <div class="b-account-bonus-card__number">5%</div>
                </div>
            </div>
            <div class="b-account-bonus-card__column b-account-bonus-card__column--number">
                <div class="b-account-bonus-card__title">Номер карты</div>
                <div class="b-account-bonus-card__number">26000 000 23 705</div>
            </div>
        </div>
        <div class="b-account-bonus-card__form">
            <div class="b-account-bonus-card__link">
                <a class="b-link b-link--account-bonus js-open-card js-open-card--account-bonus js-open-card"
                   href="javascript:void(0)"
                   title="Привязать бонусную карту"><span class="b-link__text b-link__text--account-bonus js-open-card">Привязать бонусную карту</span></a>
            </div>
            <div class="b-account-bonus-card__link b-account-bonus-card__link--hidden js-number-input">
                <form class="b-account-bonus-card__form" action="/">
                    <input class="b-input b-input--account-bonus"
                           type="text"
                           id="bonus"
                           placeholder="" />
                    <button class="b-account-bonus-card__button">Привязать</button>
                </form>
            </div>
        </div>
    </div>
    <div class="b-account-bonus__info">
        <div class="b-account-bonus__title">Статистика</div>
        <div class="b-account-bonus-progress">
            <progress class="b-account-bonus-progress__progress"
                      max="100"
                      value="60"></progress>
            <ul class="b-account-bonus-progress__progress-list">
                <li class="b-account-bonus-progress__progress-value b-account-bonus-progress__progress-value--step-one active">
                    <div class="b-account-bonus-progress__percent">3%</div>
                    <div class="b-account-bonus-progress__number">0</div>
                </li>
                <li class="b-account-bonus-progress__progress-value b-account-bonus-progress__progress-value--step-second active">
                    <div class="b-account-bonus-progress__percent">4%</div>
                    <div class="b-account-bonus-progress__number"><span>9 000</span> <span
                                class="b-ruble b-ruble--progress">&nbsp;₽</span>
                    </div>
                </li>
                <li class="b-account-bonus-progress__progress-value b-account-bonus-progress__progress-value--step-third active mobile">
                    <div class="b-account-bonus-progress__percent">5%</div>
                    <div class="b-account-bonus-progress__number"><span>19 000</span> <span
                                class="b-ruble b-ruble--progress">&nbsp;₽</span>
                    </div>
                </li>
                <li class="b-account-bonus-progress__progress-value b-account-bonus-progress__progress-value--step-four mobile">
                    <div class="b-account-bonus-progress__percent">6%</div>
                    <div class="b-account-bonus-progress__number"><span>39 000</span> <span
                                class="b-ruble b-ruble--progress">&nbsp;₽</span>
                    </div>
                </li>
                <li class="b-account-bonus-progress__progress-value b-account-bonus-progress__progress-value--step-five">
                    <div class="b-account-bonus-progress__percent">7%</div>
                    <div class="b-account-bonus-progress__number"><span>59 000</span> <span
                                class="b-ruble b-ruble--progress">&nbsp;₽</span>
                    </div>
                </li>
            </ul>
            <div class="b-account-bonus-progress__progress-bg">
                <div class="b-account-bonus-progress__progress-bar"
                     style="width: 60%;"></div>
            </div>
        </div>
        <ul class="b-account-bonus__list-info">
            <li class="b-account-bonus__item-info">
                <div class="b-account-bonus__title-info">Осталось до 6% —</div>
                <div class="b-account-bonus__text">3 260
                    <span class="b-ruble b-ruble--bonus">₽</span>
                </div>
            </li>
            <li class="b-account-bonus__item-info">
                <div class="b-account-bonus__title-info">Всего потрачено бонусов —</div>
                <div class="b-account-bonus__text">4210</div>
            </li>
        </ul>
        <div class="b-account-bonus__title b-account-bonus__title--bonus">Зачем бонусная
                                                                          карта, если есть
                                                                          виртуальная?
        </div>
        <ul class="b-account-bonus__list-bonus">
            <li class="b-account-bonus__item">Не надо записывать номер виртуальной карты,
                                              чтобы предъявить ее в магазине
            </li>
            <li class="b-account-bonus__item">Вы можете передавать друзьям и родственникам
            </li>
            <li class="b-account-bonus__item">После привязки, баланс и размер бонуса с
                                              виртуальной карты добавляются к физической
            </li>
            <li class="b-account-bonus__item">Ее можно потрогать</li>
        </ul>
        <a class="b-link b-link--bonus-info b-link--bonus-info"
           href="javascript:void(0)"
           title="Подробнее о бонусной программе"><span class="b-link__text b-link__text--bonus-info">Подробнее о бонусной программе</span></a>
    </div>
</div>