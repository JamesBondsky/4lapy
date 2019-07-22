<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\Decorators\SvgDecorator;

?>

<section class="b-popup-pick-shelter js-popup-section" data-popup="popup-order-shelters" data-b-popup-pick-shelter>
    <div class="b-popup-pick-shelter__inner">
        <div class="b-popup-pick-shelter__header">
            <div class="b-popup-pick-shelter__title">
                <span class="b-popup-pick-shelter__title-desktop">Все приюты</span>
                <span class="b-popup-pick-shelter__title-mobile">Выберите приют</span>
                <span data-b-popup-pick-shelter="shelters-count">(всего 20)</span>
            </div>

            <a href="javascript:;" class="b-popup-shelter__close-popup js-close-popup">
                <?= new SvgDecorator('icon-close-baloon', 28, 28) ?>
            </a>
        </div>

        <div class="b-popup-pick-shelter__actions">
            <input class="b-popup-pick-shelter__actions-search b-input__input-field b-input__input-field--stores-search" placeholder="Поиск по адресу или названию" data-b-popup-pick-shelter="search-input">

            <button class="b-button b-popup-pick-shelter__actions-button" data-b-popup-pick-shelter="pick-random">
                Выбрать на усмотрение организатора
            </button>
        </div>

        <div class="b-popup-pick-shelter__content">
            <div class="b-popup-pick-shelter__content-shelters">
                <? for ($i = 1; $i <= 20; $i++):  ?>
                    <button
                        class="b-popup-pick-shelter__shelter"
                        data-b-popup-pick-shelter="shelter"
                        data-shelter='<?=json_encode(['id' => $i, 'title' => "Приют $i",  'location' => 'Москва', 'text' => 'Главная помощь, если Вы решите взять животное домой. Больше всего они нуждаются в доме, любви  и заботе.'], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES)?>'
                    >
                        <div class="b-popup-pick-shelter__shelter-title">Приют <?= $i ?></div>
                        <div class="b-popup-pick-shelter__shelter-location">Москва</div>
                    </button>
                <? endfor ?>
            </div>

            <div class="b-popup-pick-shelter__content-info" data-b-popup-pick-shelter="content-info">
                <div style="margin: 0 15px;">
                    <a class="b-link b-link--popup-back b-link--order b-link--desktop" href="javascript:void(0);" data-b-popup-pick-shelter="content-info-close">
                        <span class="b-icon b-icon--back-long b-icon--balloon">
                            <?= new SvgDecorator('icon-back-form', 13, 11) ?>
                        </span>

                        Вернуться к списку
                    </a>
                </div>

                <div class="b-popup-pick-shelter__shelter b-popup-pick-shelter__shelter--in-info">
                    <div class="b-popup-pick-shelter__shelter-title" data-b-popup-pick-shelter="content-info-title"></div>
                    <div class="b-popup-pick-shelter__shelter-location" data-b-popup-pick-shelter="content-info-location"></div>
                    <div class="b-popup-pick-shelter__shelter-text" data-b-popup-pick-shelter="content-info-text"></div>
                </div>

                <button class="b-button b-popup-pick-shelter__content-pick-btn" data-b-popup-pick-shelter="pick">Выбрать этот приют</button>
            </div>
        </div>
    </div>
</section>
