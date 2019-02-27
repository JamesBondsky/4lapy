<section class="b-popup-email-kopilka js-popup-section" data-popup="send-email-coupon-kopilka">
    <a class="b-popup-email-kopilka__close js-close-popup"
       href="javascript:void(0);"
       title="Закрыть"></a>
    <div class="b-popup-email-kopilka__content">
        <header class="b-popup-email-kopilka__header">
            <div class="b-title b-title--h1 b-title--email-kopilka-popup">На эту почту мы отправим вам купон на&nbsp;скидку</div>
        </header>
        <form class="b-popup-email-kopilka__form js-form-validation js-email-kopilka" data-url="/ajax/piggy-bank/email/send/" method="post">
            <? $token = \FourPaws\Helpers\ProtectorHelper::generateToken(\FourPaws\Helpers\ProtectorHelper::TYPE_PIGGY_BANK_EMAIL_SEND); ?>
            <input type="hidden" name="<?=$token['field']?>" value="<?=$token['token']?>">
            <div class="b-input-line">
                <div class="b-input-line__label-wrapper">
                    <label class="b-input-line__label" for="send-email-coupon-kopilka">Email</label>
                </div>
                <div class="b-input b-input--kopilka-form">
                    <input class="b-input__input-field b-input__input-field--kopilka-form" type="emailMask" id="send-email-coupon-kopilka" name="email" value="<?=$USER->GetEmail()?:''?>" placeholder="">
                    <div class="b-error"><span class="js-message"></span>
                    </div>
                </div>
            </div>
            <button class="b-button b-button--email-kopilka">Отправить</button>
        </form>
    </div>
</section>