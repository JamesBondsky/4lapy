<?php use FourPaws\Decorators\SvgDecorator; ?>
<section class="b-popup-pick-city b-popup-pick-city--add-pet js-popup-section" data-popup="edit-popup-pet">
    <a class="b-popup-pick-city__close b-popup-pick-city__close--add-pet js-close-popup"
       href="javascript:void(0);"
       title="Закрыть"></a>
    <div class="b-registration b-registration--add-pet">
        <header class="b-registration__header">
            <h1 class="b-title b-title--h1 b-title--registration"></h1>
        </header>
        <form class="b-registration__form js-form-validation js-add-pet-query"
              method="post"
              data-url="/ajax/personal/pets/add/"
              enctype="multipart/form-data">
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
            <div class="b-input-line b-input-line--popup-authorization b-input-line--popup-pet js-name js-small-input">
                <div class="b-input-line__label-wrapper">
                    <label class="b-input-line__label" for="name-pet">Имя питомца</label>
                </div>
                <div class="b-input b-input--registration-form">
                    <input class="b-input__input-field b-input__input-field--registration-form"
                           type="text"
                           id="name-pet"
                           placeholder=""
                           data-text="0"
                           name="UF_NAME" />
                    <div class="b-error"><span class="js-message"></span>
                    </div>
                </div>
            </div>
            <label class="b-registration__label b-registration__label--subscribe-delivery" for="type-pet">Вид животного</label>
            <div class="b-select b-select--subscribe-delivery js-pet-view">
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
            <div class="b-input-line b-input-line--popup-authorization b-input-line--popup-pet js-breed">
                <div class="b-input-line__label-wrapper">
                    <label class="b-input-line__label" for="breed-pet">Порода</label>
                </div>
                <div class="b-input b-input--registration-form">
                    <input class="b-input__input-field b-input__input-field--registration-form"
                           type="text"
                           id="breed-pet"
                           placeholder=""
                           data-text="1"
                           name="UF_BREED" />
                    <div class="b-error"><span class="js-message"></span>
                    </div>
                </div>
            </div>
            <div class="b-input-line b-input-line--popup-authorization b-input-line--popup-pet js-date js-date-valid">
                <div class="b-input-line__label-wrapper">
                    <label class="b-input-line__label" for="birthday-pet">Дата рождения</label>
                </div>
                <div class="b-input b-input--registration-form">
                    <input class="b-input__input-field b-input__input-field--registration-form"
                           type="text"
                           id="birthday-pet"
                           placeholder=""
                           data-text="2"
                           name="UF_BIRTHDAY" />
                    <div class="b-error"><span class="js-message"></span>
                    </div>
                </div>
            </div>
            <div class="b-registration__wrapper-radio js-male">
                <div class="b-radio b-radio--add-pet">
                    <input class="b-radio__input"
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
                    <input class="b-radio__input"
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
            <button class="b-button b-button--subscribe-delivery">Сохранить</button>
        </form>
    </div>
</section>