<?php

use FourPaws\PersonalBundle\Entity\UserBonus;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @var UserBonus $bonus */
$bonus = $arResult['BONUS'];
?>
<div class="b-account-bonus">
    <div class="b-account-bonus-card">
        <div class="b-account-bonus-card__colored-block">
            <div class="b-account-bonus-card__line">
                <div class="b-account-bonus-card__column">
                    <div class="b-account-bonus-card__title">Баланс</div>
                    <div class="b-account-bonus-card__number"><?= $bonus->getActiveBonus() ?>
                    </div>
                </div>
                <div class="b-account-bonus-card__column b-account-bonus-card__column--bonus">
                    <div class="b-account-bonus-card__title">Бонус</div>
                    <div class="b-account-bonus-card__number"><?= $bonus->getRealDiscount() ?>%
                    </div>
                </div>
            </div>
            <div class="b-account-bonus-card__column b-account-bonus-card__column--number">
                <div class="b-account-bonus-card__title">Номер карты</div>
                <div class="b-account-bonus-card__number"><?= $bonus->getCard()->getFormatedCardNumber() ?></div>
            </div>
        </div>
        <?php if (!$bonus->getCard()->isReal()) { ?>
            <div class="b-account-bonus-card__form">
                <div class="b-account-bonus-card__link">
                    <a class="b-link b-link--account-bonus js-open-card js-open-card--account-bonus js-open-card"
                       href="javascript:void(0)"
                       title="Привязать бонусную карту"><span class="b-link__text b-link__text--account-bonus js-open-card">Привязать бонусную карту</span></a>
                </div>
                <div class="b-account-bonus-card__link b-account-bonus-card__link--hidden js-number-input">
                    <form class="b-account-bonus-card__form js-form-validation js-offers-query"
                          action="/"
                          method="post"
                          data-url="/ajax/personal/bonus/card/link/">
                        <div class="b-input b-input--account-bonus js-offers">
                            <input class="b-input__input-field b-input__input-field--account-bonus js-offers"
                                   type="text"
                                   id="bonus"
                                   placeholder=""
                                   name="card"
                                   data-url="" />
                            <div class="b-error"><span class="js-message"></span>
                            </div>
                        </div>
                        <button class="b-account-bonus-card__button">Привязать</button>
                    </form>
                </div>
            </div>
        <?php } ?>
    </div>
    <div class="b-account-bonus__info">
        <div class="b-account-bonus__title">Статистика</div>
        <div class="b-account-bonus-progress">
            <progress class="b-account-bonus-progress__progress" max="100" value="<?= $bonus->getProgress() ?>">
            </progress>
            <ul class="b-account-bonus-progress__progress-list">
                <?php $i = 0;
                $steps   = [
                    'one active',
                    'second active',
                    'third active mobile',
                    'four mobile',
                    'five',
                ];
                foreach (UserBonus::$discountTable as $discountPercent => $discountSum) {
                    $i++; ?>
                    <li class="b-account-bonus-progress__progress-value b-account-bonus-progress__progress-value--step-<?= $steps[$i] ?>">
                        <div class="b-account-bonus-progress__percent"><?= $discountPercent ?>%
                        </div>
                        <div class="b-account-bonus-progress__number">
                            <?php if ($discountSum === 0) {?>
                                <?= $discountSum ?>
                            <?php } else { ?>
                                <span><?= number_format($discountSum, 0, '.', ' ') ?> </span>
                                <span class="b-ruble b-ruble--progress">&nbsp;₽</span>
                            <?php } ?>
                        </div>
                    </li>
                <?php } ?>
            </ul>
            <div class="b-account-bonus-progress__progress-bg">
                <div class="b-account-bonus-progress__progress-bar" style="width: <?= $bonus->getProgress() ?>%;">
                </div>
            </div>
        </div>

        <ul class="b-account-bonus__list-info">
            <li class="b-account-bonus__item-info">
                <div class="b-account-bonus__title-info">Осталось до <?= $bonus->getNextDiscount() ?>% —</div>
                <div class="b-account-bonus__text"><?= $bonus->getSumToNext() ?>
                    <span class="b-ruble b-ruble--bonus">₽</span>
                </div>
            </li>
            <li class="b-account-bonus__item-info">
                <div class="b-account-bonus__title-info">Всего потрачено бонусов —</div>
                <div class="b-account-bonus__text"><?= $bonus->getCredit() ?></div>
            </li>
        </ul>
        <?php if (!$bonus->getCard()->isReal()) { ?>
            <div class="b-account-bonus__title b-account-bonus__title--bonus">Зачем бонусная карта, если есть виртуальная?</div>
            <ul class="b-account-bonus__list-bonus">
                <li class="b-account-bonus__item">Не надо записывать номер виртуальной карты, чтобы предъявить ее в магазине</li>
                <li class="b-account-bonus__item">Вы можете передавать друзьям и родственникам</li>
                <li class="b-account-bonus__item">После привязки, баланс и размер бонуса с виртуальной карты добавляются к физической</li>
                <li class="b-account-bonus__item">Ее можно потрогать</li>
            </ul>
        <?php } ?>
        <div class="b-account-bonus__title b-account-bonus__title--bonus">Не забывайте!</div>
        <ul class="b-account-bonus__list-bonus">
            <li class="b-account-bonus__item">1 бонус = 1 ₽</li>
            <li class="b-account-bonus__item">Вы можете передавать друзьям и родственникам</li>
            <li class="b-account-bonus__item">Оплатить можно до 90% стоимости заказа</li>
        </ul>
        <a class="b-link b-link--bonus-info b-link--bonus-info" href="/customer/bonus-program/" title="Подробнее о бонусной программе">
            <span class="b-link__text b-link__text--bonus-info">Подробнее о бонусной программе</span>
        </a>
    </div>
</div>