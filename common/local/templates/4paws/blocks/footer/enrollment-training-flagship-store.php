<section class="popup-service-flagship-store js-popup-section" data-popup="walking-flagship-store">
    <a class="popup-service-flagship-store__close js-close-popup" href="javascript:void(0);" title="Закрыть"></a>
    <div class="popup-service-flagship-store__content">
        <div class="popup-service-flagship-store__title">Запись на тренировку</div>

        <form class="popup-service-flagship-store__form js-form-validation">
            <input type="hidden" name="id" value="">

            <div class="b-input-line">
                <div class="b-input-line__label-wrapper">
                    <label class="b-input-line__label" for="data-first-name">Имя</label>
                </div>
                <div class="b-input">
                    <input class="b-input__input-field b-input__input-field--registration-form js-small-input"
                           type="text"
                           id="data-first-name"
                           name="NAME"
                           value=""
                           data-text="1"
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
                    <input class="b-input__input-field b-input__input-field--registration-form"
                           type="tel"
                           id="edit-phone"
                           name="phone"
                           value=""
                           placeholder="" />
                    <div class="b-error"><span class="js-message"></span>
                    </div>
                </div>
            </div>

            <div class="popup-service-flagship-store__btn">
                <button class="b-button">Записаться</button>
            </div>
        </form>
    </div>
</section>