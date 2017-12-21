<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
} ?>
<div class="b-account-profile__personal-data">
    <div class="b-account-profile__column b-account-profile__column--data">
        <div class="b-account-data">
            <div class="b-account-data__title">
                ФИО
            </div>
            <div class="b-account-data__value">
                <div class="b-account-data__text">
                    <?= $arResult['CUR_USER']['FULL_NAME'] ?>
                </div>
            </div>
        </div>
        <div class="b-account-data">
            <div class="b-account-data__title">
                Телефон
            </div>
            <div class="b-account-data__value">
                <div class="b-account-data__text">
                    <?= $arResult['CUR_USER']['PERSONAL_PHONE'] ?>
                </div>
                <span class="b-icon b-icon--account-profile<?= $arResult['CUR_USER']['PHONE_CONFIRMED'] ? ' active' : '' ?>"></span>
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
                    <?= $arResult['CUR_USER']['BIRTHDAY'] ?>
                </div>
            </div>
        </div>
        <div class="b-account-data">
            <div class="b-account-data__title">
                Почта
            </div>
            <div class="b-account-data__value">
                <div class="b-account-data__text">
                    <a href="mailto:<?= $arResult['CUR_USER']['EMAIL'] ?>"><?= $arResult['CUR_USER']['EMAIL'] ?></a>
                </div>
                <span class="b-icon b-icon--account-profile<?= $arResult['CUR_USER']['EMAIL_CONFIRMED'] ? ' active' : '' ?>"></span>
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
                    <?= $arResult['CUR_USER']['GENDER_TEXT'] ?>
                </div>
            </div>
        </div>
    </div>
</div>
