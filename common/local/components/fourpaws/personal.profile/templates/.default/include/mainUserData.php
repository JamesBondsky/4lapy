<?php

use FourPaws\Decorators\SvgDecorator;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var array $arResult */ ?>
<div class="b-account-profile__personal-data js-set-edit-data">
    <div class="b-account-profile__column b-account-profile__column--data">
        <div class="b-account-data">
            <div class="b-account-data__title">
                ФИО
            </div>
            <div class="b-account-data__value">
                <div class="b-account-data__text js-fio">
                    <?= $arResult['CUR_USER']['FULL_NAME'] ?>
                </div>
            </div>
        </div>
        <div class="b-account-data">
            <div class="b-account-data__title">
                Телефон
            </div>
            <div class="b-account-data__value">
                <div class="b-account-data__text js-profile-phone">
                    <?= $arResult['CUR_USER']['PERSONAL_PHONE'] ?>
                </div>
                <span class="b-icon b-icon--account-profile<?= $arResult['CUR_USER']['PHONE_CONFIRMED'] ? ' active' : '' ?>">
                    <?= new SvgDecorator('icon-check-account', 21, 17) ?>
                </span>
            </div>
        </div>
    </div>
    <div class="b-account-profile__column b-account-profile__column--data">
        <div class="b-account-data">
            <div class="b-account-data__title">
                День рождения
            </div>
            <div class="b-account-data__value">
                <div class="b-account-data__text js-profile-date">
                    <?= $arResult['CUR_USER']['BIRTHDAY'] ?>
                </div>
            </div>
        </div>
        <div class="b-account-data">
            <div class="b-account-data__title">
                Почта
            </div>
            <div class="b-account-data__value">
                <div class="b-account-data__text js-profile-email">
                    <a href="mailto:<?= $arResult['CUR_USER']['EMAIL'] ?>"><?= $arResult['CUR_USER']['EMAIL'] ?></a>
                </div>
                <span class="b-icon b-icon--account-profile<?= $arResult['CUR_USER']['EMAIL_CONFIRMED'] ? ' active' : '' ?>">
                    <?= new SvgDecorator('icon-check-account', 21, 17) ?>
                </span>
            </div>
        </div>
    </div>
    <div class="b-account-profile__column b-account-profile__column--data">
        <div class="b-account-data">
            <div class="b-account-data__title">
                Пол
            </div>
            <div class="b-account-data__value">
                <div class="b-account-data__text js-profile-male">
                    <?= $arResult['CUR_USER']['GENDER_TEXT'] ?>
                </div>
            </div>
        </div>
    </div>
    <div class="js-hidden-block"
         style="display: none;"
         data-first-name="<?= $arResult['CUR_USER']['NAME'] ?>"
         data-last-name="<?= $arResult['CUR_USER']['LAST_NAME'] ?>"
         data-name="<?= $arResult['CUR_USER']['SECOND_NAME'] ?>"
         data-phone="<?= $arResult['CUR_USER']['PERSONAL_PHONE'] ?>"
         data-date="<?= $arResult['CUR_USER']['DATE_POPUP'] ?>"
         data-email="<?= $arResult['CUR_USER']['EMAIL'] ?>"
         data-male="<?= $arResult['CUR_USER']['GENDER'] === 'M' ? 1 : 0 ?>"
         data-female="<?= $arResult['CUR_USER']['GENDER'] === 'F' ? 1 : 0 ?>"
         data-id="<?= $arResult['CUR_USER']['ID'] ?>">
    </div>
</div>
