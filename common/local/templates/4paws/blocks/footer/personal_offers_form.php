<section class="b-popup-email-coupon js-popup-section" data-popup="send-email-personal-offers">
    <a class="b-popup-email-coupon__close js-close-popup"
       href="javascript:void(0);"
       title="Закрыть"></a>
    <div class="b-popup-email-coupon__content">
        <header class="b-popup-email-coupon__header">
            <div class="b-title b-title--h1 b-title--email-coupon-popup">На эту почту мы отправим вам купон на&nbsp;скидку</div>
        </header>
        <form class="b-popup-email-coupon__form js-form-validation js-email-coupon-personal-offers" data-url="" method="post">
            <input type="hidden" name="ID_COUPON_PERSONAL_OFFERS" value="" data-input-coupon-personal-offers="true">
            <div class="b-input-line">
                <div class="b-input-line__label-wrapper">
                    <label class="b-input-line__label" for="send-email-coupon-personal-offers">Email</label>
                </div>
                <div class="b-input b-input--coupon-form">
                    <input class="b-input__input-field b-input__input-field--coupon-form" type="emailMask" id="send-email-coupon-personal-offers" name="email" value="" placeholder="">
                    <div class="b-error"><span class="js-message"></span>
                    </div>
                </div>
            </div>
            <button class="b-button b-button--email-coupon">Отправить</button>
        </form>
    </div>
</section>