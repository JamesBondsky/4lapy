<?php
global $USER;

if ($USER->IsAuthorized()) {
    $user = \CUser::GetList(($by="personal_country"), ($order="desc"), ['ID' => $USER->GetID()])->Fetch();
}
?>
<section class="popup-service-flagship-store js-popup-section" data-popup="training-flagship-store">
    <a class="popup-service-flagship-store__close js-close-popup" href="javascript:void(0);" title="Закрыть"></a>
    <div class="popup-service-flagship-store__content">
        <div class="popup-service-flagship-store__title">Запись на тренировку</div>

        <form class="popup-service-flagship-store__form js-form-validation" data-url="/flagman/bookthetimelocal/" method="post">
            <input type="hidden" name="id" value="" data-id-training-flagship-store-popup="true">
            <input type="hidden" name="date" value="" data-date-training-flagship-store-popup="true">
            <input type="hidden" name="time" value="" data-time-training-flagship-store-popup="true">

            <div class="b-input-line">
                <div class="b-input-line__label-wrapper">
                    <label class="b-input-line__label" for="data-first-name">Имя</label>
                </div>
                <div class="b-input">
                    <input class="b-input__input-field js-small-input"
                           type="text"
                           id="data-first-name"
                           name="name"
                           value="<?=($user['NAME']) ? $user['NAME'] : ''?>"
                           data-text="1"
                           data-name-service-flagship-store-popup="true"
                           placeholder="" />
                    <div class="b-error"><span class="js-message"></span>
                    </div>
                </div>
            </div>

            <div class="b-input-line">
                <div class="b-input-line__label-wrapper">
                    <label class="b-input-line__label" for="edit-phone">Телефон</label>
                </div>
                <div class="b-input">
                    <input class="b-input__input-field"
                           type="tel"
                           id="edit-phone"
                           name="phone"
                           value="<?=($user['PERSONAL_PHONE']) ? $user['PERSONAL_PHONE'] : ''?>"
                           data-phone-service-flagship-store-popup="true"
                           placeholder="" />
                    <div class="b-error"><span class="js-message"></span>
                    </div>
                </div>
            </div>

            <div class="b-input-line">
                <div class="b-input-line__label-wrapper">
                    <label class="b-input-line__label" for="data-email">Эл. почта</label>
                </div>
                <div class="b-input">
                    <input class="b-input__input-field"
                           type="email"
                           id="data-email"
                           name="email"
                           value="<?=($user['EMAIL']) ? $user['EMAIL'] : ''?>"
                           data-email-service-flagship-store-popup="true"
                           placeholder="" />
                    <div class="b-error"><span class="js-message"></span></div>
                </div>
            </div>

            <div class="popup-service-flagship-store__btn">
                <button type="submit" class="b-button" data-submit-training-flagship-store-popup="true">Записаться</button>
            </div>
        </form>
    </div>
</section>