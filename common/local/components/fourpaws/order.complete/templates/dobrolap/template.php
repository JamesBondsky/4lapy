<?php
    if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
        die();
    }
?>

<div class="b-container">
    <h1 class="b-title b-title--h1 b-title--order">
        Спасибо, что вы творите добро вместе с нами!
    </h1>

    <div class="b-order">
        <div class="b-order__block b-order__block--no-border b-order__block--no-flex">
            <div class="b-order__content b-order__content--no-border b-order__content--no-padding b-order__content--no-flex">
                <hr class="b-hr b-hr--order b-hr--top-line" />

                <h2 class="b-title b-title--order-heading b-title--block">
                    Ваш заказ №11020041 оформлен
                </h2>

                <div class="b-order__text-block">
                    и&nbsp;будет&nbsp;доставлен&nbsp;в

                    <b>Приют «Искра»</b>

                    по&nbsp;адресу

                    <b>Москва, ул. Сходненская, д. 25.</b>
                </div>

                <hr class="b-hr b-hr--order b-hr--top-line" />

                <div data-b-dobrolap-prizes data-order-id="<ORDERID>">
                    <?/*
                        1. Показываем choose-section, если пользователь не выбрал приз.

                        После выбора приза, choose-section скрывается,
                        а в coupon-section проставляется HTML из AJAX.

                        2. Не показываем choose-section, если пользователь уже выбрал приз.
                    */?>
                    <div data-b-dobrolap-prizes="choose-section">
                        <div class="b-order__text-block">
                            <strong>Мы говорим спасибо</strong>
                            <br /><br />

                            В знак благодарности мы приготовили небольшой сюрприз — <br />  фанты «Добролап» с приятными презентами.
                            <br /><br />

                            Также мы вложим в ваш следующий заказ подарок — памятный магнит.
                        </div>

                        <hr class="b-hr b-hr--order b-hr--top-line" />

                        <div class="b-order__text-block">
                            <b>А сейчас выберите для себя один из шести сюрпризов, кликнув на любой из них</b>
                        </div>

                        <div class="b-dobrolap-prizes">
                            <?php for ($i = 1; $i <= 6; $i++): ?>
                                <input
                                        type="radio"
                                        name="prize"
                                        value="<?=$i?>"
                                        class="b-dobrolap-prizes__radio"
                                        id="dobrolap-prize-<?=$i?>"
                                />

                                <label for="dobrolap-prize-<?=$i?>" class="b-dobrolap-prizes__label" data-b-dobrolap-prizes="choose-section-item">
                                    <img src="/static/build/images/content/dobrolap/dobrolap-logo@3x.png" alt="" class="b-dobrolap-prizes__label-img" />
                                </label>
                            <?php endfor ?>
                        </div>
                    </div>

                    <div data-b-dobrolap-prizes="coupon-section">
                        <?/*
                            1. Если пользователь не выбрал приз, то оставляем пустым div coupon-section.
                            2. Если пользователь выбрал приз, то вставляем в coupon-section HTML c купоном.
                        */?>

<?/*
                        <div class="b-order__text-block">
                            <strong>А вот и сюрприз для Вас!</strong>
                            <br /><br />

                            <div class="b-dobrolap-coupon" data-b-dobrolap-coupon data-coupon="ABC123DFE4567">
                                <div class="b-dobrolap-coupon__item b-dobrolap-coupon__item--info">
                                    <div class="b-dobrolap-coupon__discount">
                                        <span class="b-dobrolap-coupon__discount-big">15%</span>

                                        <span class="b-dobrolap-coupon__discount-text b-dobrolap-coupon__discount-text--desktop">
                                            на лакомства для кошек&nbsp;и&nbsp;собак
                                        </span>

                                        <span class="b-dobrolap-coupon__discount-text b-dobrolap-coupon__discount-text--mobile">
                                            Лакомства
                                        </span>
                                    </div>

                                    <div class="b-dobrolap-coupon__deadline">
                                        скидка действует  по&nbsp;промо-коду  до&nbsp;31.09.2019
                                    </div>
                                </div>

                                <div class="b-dobrolap-coupon__item b-dobrolap-coupon__item--promo">
                                    <div class="b-dobrolap-coupon__code">
                                        <span class="b-dobrolap-coupon__code-text">Промо-код</span>
                                        <strong>ABC123DFE4567</strong>

                                        <button class="b-button b-button--outline-white b-dobrolap-coupon__code-copy" data-b-dobrolap-coupon="copy-btn">Скопировать</button>
                                    </div>

                                    <div class="b-dobrolap-coupon__barcode">
                                        <img src="https://placehold.it/192x68" alt="" class="b-dobrolap-coupon__barcode-image" />
                                    </div>

                                    <button class="b-button b-button--outline-grey b-button--full-width b-dobrolap-coupon__email-me" data-b-dobrolap-coupon="email-btn">
                                        Отправить мне на email
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="b-order__text-block">
                            Это ваш подарок за участие в акции. <br />
                            Он доступен в разделе <a href="" class="b-link">Персональные предложения</a>.
                        </div>

                        <hr class="b-hr b-hr--order b-hr--top-line" />

                        <div class="b-order__text-block">
                            <strong>Как использовать промо-код:</strong><br /><br />

                            1. На сайте или в мобильном приложении положите акционный товар в корзину и введите промо-код в специальное поле в корзине.
                            <br />
                            2. В магазине на кассе перед оплатой акционного товара покажите промо-код кассиру.
                            <br />
                            3. Промо-код можно использовать 1 раз до окончанчания его срока действия.
                        </div>
*/?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
