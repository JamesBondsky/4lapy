<?php use FourPaws\Decorators\SvgDecorator;?>
<section class="b-popup-pick-city b-popup-pick-city--add-pet js-popup-section" data-popup="collector-pet">
    <div class="js-tab-account" style="display: none;" data-tab-content="my-pet">
        <!-- обманка вынесена сюда -->
        <a class="js-add-query js-open-popup js-open-popup--account-tab js-add-query" id="data_collect" data-popup-id="collector-pet" data-url="/ajax/personal/pets/add/"></a>
    </div>
    <a class="b-popup-pick-city__close b-popup-pick-city__close--add-pet js-close-popup"
       href="javascript:void(0);"
       title="Закрыть"></a>
    <div class="b-registration b-registration--add-pet">
        <header class="b-registration__header">
            <div class="b-title b-title--h1 b-title--registration"></div>
        </header>
        <form class="b-registration__form js-form-validation js-add-pet-query"
              method="post"
              data-url="/ajax/personal/pets/add/"
              enctype="multipart/form-data">
            <p style="font-size: 14px;">
                <b>
                    Вы видите эту форму, так как не все данные в Вашем профиле заполнены.
                    Дополнительная информация позволит нам сообщать о распродажах, новинках и подарках, которые будут интересны именно Вам и Вашему питомцу.
                </b>
            </p>
            <input class="js-data-id js-no-valid" name="ID" value="" type="hidden">
            <div class="b-registration__wrapper-avatar">
                <div class="b-registration__add-photos js-img">
                    <input class="b-registration__load js-no-valid js-drag-n-drop"
                           type="file"
                           name="UF_PHOTO"
                           accept="image/*,image/jpeg" />
                    <span class="b-icon b-icon--upload">
                        <?= new SvgDecorator('icon-upload', 69, 57) ?>
                    </span>
                    <div class="b-registration__text b-registration__text--upload">Перетащите картинку сюда или
                                                                                   нажмите на область для выбора
                                                                                   файла
                    </div>
                </div>
                <a class="b-registration__link-pet js-drop-edit" href="javascript:void(0);" title="">
                    <span class="b-icon b-icon--pet-edit">
                        <?= new SvgDecorator('icon-edit', 25, 25) ?>
                    </span>
                    <img class="b-registration__image js-image-wrapper"
                            src=""
                            alt=""
                            title="" />
                </a>
            </div>
            <label class="b-registration__label b-registration__label--subscribe-delivery" for="type-pet">Вид животного</label>
            <div class="b-select b-select--subscribe-delivery">
                <select class="b-select__block b-select__block--subscribe-delivery js-pet-view"
                        id="type-pet"
                        name="UF_TYPE">
                    <option value="" disabled="disabled" selected="selected">Выберите вид</option>
                    <?php if (\is_array($arResult['PET_TYPES']) && !empty($arResult['PET_TYPES'])) {
                        foreach ($arResult['PET_TYPES'] as $item) {?>
                            <option value="<?= $item['ID'] ?>"><?= $item['UF_NAME'] ?></option>
                            <?php
                        }
                    } ?>
                </select>
                <div class="b-error"><span class="js-message"></span>
                </div>
            </div>
            <div class="b-input-line b-input-line--popup-authorization b-input-line--popup-pet b-input-line--name-pet js-name js-small-input">
                <div class="b-input-line__label-wrapper">
                    <label class="b-input-line__label" for="name-pet">Имя питомца</label>
                </div>
                <div class="b-input b-input--registration-form">
                    <input class="b-input__input-field b-input__input-field--registration-form"
                           type="text"
                           id="name-pet"
                           placeholder=""
                           data-text="0"
                           data-name-pet-form="true"
                           name="UF_NAME" />
                    <div class="b-error"><span class="js-message"></span>
                    </div>
                </div>
            </div>
            <div class="b-input-line b-input-line--popup-authorization b-input-line--popup-pet js-breed">
                <div class="b-input-line__label-wrapper">
                    <label class="b-input-line__label" for="breed-pet">Порода</label>
                </div>
                <div class="b-input b-input--registration-form">
                    <input class="js-id-breed-pet-form-add-pet js-no-valid" name="UF_BREED_ID" value="" type="hidden">
                    <div class="b-select b-select--select2" data-wrap-breed-pet-form-add-pet="true" data-id="breed-pet" data-name="UF_BREED">
                      <select class="b-select__block" id="breed-pet" name="UF_BREED">
                        <option value="" disabled="disabled" selected="selected">Выберите породу</option>
                      </select>
                    </div>
                    <div class="b-error"><span class="js-message"></span>
                    </div>
                </div>
            </div>
            <div class="b-input-line b-input-line--popup-authorization b-input-line--popup-pet js-date js-date-valid">
                <div class="b-input-line__label-wrapper">
                    <label class="b-input-line__label" for="birthday-pet">Дата рождения</label>
                </div>
                <div class="b-input b-input--registration-form">
                    <input class="b-input__input-field b-input__input-field--registration-form js-no-valid"
                           type="text"
                           id="birthday-pet"
                           placeholder=""
                           data-text="2"
                           autocomplete="off"
                           name="UF_BIRTHDAY" />
                    <div class="b-error"><span class="js-message"></span>
                    </div>
                </div>
            </div>
            <div class="b-registration__wrapper-radio js-male">
                <div class="b-radio b-radio--add-pet">
                    <input class="b-radio__input js-no-valid"
                           type="radio"
                           name="UF_GENDER"
                           id="male"
                           data-radio="0"
                           value="<?= $arResult['GENDER']['M']['ID'] ?>" />
                    <label class="b-radio__label b-radio__label--add-pet"
                           for="male"><span class="b-radio__text-label">Мальчик</span>
                    </label>
                </div>
                <div class="b-radio b-radio--add-pet">
                    <input class="b-radio__input js-no-valid"
                           type="radio"
                           name="UF_GENDER"
                           id="female"
                           data-radio="1"
                           value="<?= $arResult['GENDER']['F']['ID'] ?>" />
                    <label class="b-radio__label b-radio__label--add-pet"
                           for="female"><span class="b-radio__text-label">Девочка</span>
                    </label>
                </div>
                <div class="b-error"><span class="js-message"></span>
                </div>
            </div>

            <div class="b-size-select" style="display: none; " data-lk-pets-breed="size-select">
                <div class="b-input-line b-input-line--popup-authorization b-input-line--popup-pet js-size">
                    <div class="b-input-line__label-wrapper">
                        <label class="b-input-line__label" for="size-pet">Размер</label>
                    </div>
                    <div class="b-select b-select--size-pet">
                        <select class="b-select__block b-select__block--size-pet js-pet-size js-no-valid"
                                id="size-pet"
                                name="UF_SIZE">
                            <option value="" disabled="disabled" selected="selected">Выберите из списка</option>
                            <?php if (\is_array($arResult['PET_SIZES']) && !empty($arResult['PET_SIZES'])) {
                                foreach ($arResult['PET_SIZES'] as $item) {?>
                                    <option value="<?= $item['ID'] ?>"><?= $item['VALUE'] ?></option>
                                    <?php
                                }
                            } ?>
                            <option value="UNKNOWN">не знаю</option>
                        </select>
                        <div class="b-error"><span class="js-message"></span>
                        </div>
                    </div>
                </div>

                <div style="display: none;" data-lk-pets-breed="size-select-measure">
                    <div class="b-input-line b-input-line--popup-authorization b-input-line--popup-pet js-date js-date-valid">
                        <div class="b-input-line__label-wrapper">
                            <div class="b-input-line__label">
                                Измерьте свою собаку и укажите параметры:
                            </div>
                        </div>
                    </div>

                    <img src="/static/build/images/inhtml/icons/size-dog_violet.svg" style="display: block; margin: 0 auto 20px auto; max-width: 200px;" />

                    <div class="b-input-line b-input-line--popup-authorization b-input-line--popup-pet js-back">
                        <div class="b-input-line__label-wrapper">
                            <label class="b-input-line__label" for="back-pet">1. Длина спины, см</label>
                        </div>
                        <div class="b-input b-input--registration-form">
                            <input class="b-input__input-field b-input__input-field--registration-form js-no-valid"
                                   type="text"
                                   id="back-pet"
                                   placeholder=""
                                   data-text="0"
                                   name="UF_BACK" />
                            <div class="b-error"><span class="js-message"></span>
                            </div>
                        </div>
                    </div>

                    <div class="b-input-line b-input-line--popup-authorization b-input-line--popup-pet js-chest">
                        <div class="b-input-line__label-wrapper">
                            <label class="b-input-line__label" for="chest-pet">2. Обхват груди, см</label>
                        </div>
                        <div class="b-input b-input--registration-form">
                            <input class="b-input__input-field b-input__input-field--registration-form js-no-valid"
                                   type="text"
                                   id="chest-pet"
                                   placeholder=""
                                   data-text="0"
                                   name="UF_CHEST" />
                            <div class="b-error"><span class="js-message"></span>
                            </div>
                        </div>
                    </div>

                    <div class="b-input-line b-input-line--popup-authorization b-input-line--popup-pet js-neck">
                        <div class="b-input-line__label-wrapper">
                            <label class="b-input-line__label" for="neck-pet">3. Обхват шеи, см</label>
                        </div>
                        <div class="b-input b-input--registration-form">
                            <input class="b-input__input-field b-input__input-field--registration-form js-no-valid"
                                   type="text"
                                   id="neck-pet"
                                   placeholder=""
                                   data-text="0"
                                   name="UF_NECK" />
                            <div class="b-error"><span class="js-message"></span>
                            </div>
                        </div>
                    </div>

                    <div class="b-input-line b-input-line--popup-authorization b-input-line--popup-pet" style="display: none;" data-lk-pets-breed="size-select-measure-concrete-block">
                        <div class="b-input-line__label-wrapper">
                            <div class="b-input-line__label" style="font-weight: 400;">
                                Скорее всего, у Вашего питомца <b data-lk-pets-breed="size-select-measure-concrete-size"></b> размер.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <button class="b-button b-button--subscribe-delivery">Добавить</button>
        </form>
    </div>
</section>
