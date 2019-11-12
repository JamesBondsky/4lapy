<section class="service-flagship-store" data-item-service-flagship-store="grooming">
    <div class="b-container">
        <div class="service-flagship-store__header service-flagship-store__header_grooming">
            <div class="service-flagship-store__inner-header">
                <div class="service-flagship-store__title">Груминг</div>
                <div class="service-flagship-store__btn active" data-toggle-service-flagship-store="true"></div>
            </div>
        </div>
        <div class="service-flagship-store__content" data-content-service-flagship-store="true" style="display: block">
            <div class="service-flagship-store__descr">
                Груминг&nbsp;&mdash; это уход за&nbsp;внешностью животного, его кожей и&nbsp;шерстью, когтями и&nbsp;ушами.
                Первоначально этот термин означал совокупность гигиенических процедур по&nbsp;уходу за&nbsp;домашними питомцами.
            </div>
            <form class="form-signup-grooming-flagship js-form-validation" data-form-signup-grooming-flagship="true">
                <div class="form-signup-grooming-flagship__content">
                    <div class="form-signup-grooming-flagship__top">
                        <div class="b-input-line">
                            <div class="b-input-line__label-wrapper">
                                <span class="b-input-line__label">Дата</span>
                            </div>
                            <div class="b-select">
                                <select class="b-select__block" data-date-grooming-flagship="true">
                                    <option value="" disabled="disabled" selected="selected">выберите</option>
                                    <?php foreach ($arResult['SCHEDULE'] as $key => $day) : ?>
                                        <?php if ($day['end'] != 'Y') : ?>
                                            <option value="<?=$key?>" data-url="/flagman/getschedule/grooming/<?=$key?>" data-date-option="<?=$day['day']?>"><?=$day['day']?></option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                                <div class="b-error"><span class="js-message"></span></div>
                            </div>
                        </div>

                        <div class="b-input-line b-input-line--time">
                            <div class="b-input-line__label-wrapper">
                                <label class="b-input-line__label">Время</label>
                            </div>
                            <div class="b-select">
                                <select class="b-select__block" data-time-grooming-flagship="true">
                                    <option value="" disabled="disabled" selected="selected">выберите</option>
                                </select>
                                <div class="b-error"><span class="js-message"></span></div>
                            </div>
                        </div>
                    </div>

                    <div class="b-input-line">
                        <div class="b-input-line__label-wrapper">
                            <label class="b-input-line__label"  for="type-pet">Вид животного</label>
                        </div>
                        <div class="b-select">
                            <select class="b-select__block js-pet-view" id="type-pet" name="UF_TYPE">
                                <option value="" disabled="disabled" selected="selected">Выберите вид</option>
                                <option value="13" data-code="koshki">Кошки</option>
                                <option value="14" data-code="sobaki">Собаки</option>
                            </select>
                            <div class="b-error"><span class="js-message"></span></div>
                        </div>
                    </div>

                    <div class="b-input-line js-breed">
                        <div class="b-input-line__label-wrapper">
                            <label class="b-input-line__label" for="breed-pet">Порода</label>
                        </div>
                        <div class="b-input">
                            <input class="js-id-breed-pet-form js-no-valid" name="UF_BREED_ID" value="" type="hidden">
                            <div class="b-select b-select--select2" data-wrap-breed-pet-form="true" data-id="breed-pet" data-name="UF_BREED">
                                <select class="b-select__block" id="breed-pet" name="UF_BREED">
                                    <option value="" disabled="disabled" selected="selected">Выберите породу</option>
                                </select>
                            </div>
                            <div class="b-error"><span class="js-message"></span>
                            </div>
                        </div>
                    </div>

                    <div class="b-input-line">
                        <div class="b-input-line__label-wrapper">
                            <span class="b-input-line__label">Услуга</span>
                        </div>
                        <div class="b-select">
                            <select class="b-select__block" data-service-grooming-flagship="true">
                                <option value="" disabled="disabled" selected="selected">выберите</option>
                                <option value="0">Гигиеническая стрижка мелкие/средние породы - 1800р.</option>
                                <option value="1">Гигиеническая стрижка крупные породы - 2500р.</option>
                                <option value="2">Модельная стрижка Американский кокер-спаниель - 3500р.</option>
                                <option value="3">Йоркширский терьер - 2500р.</option>
                                <option value="4">Мальтийская болонка (Мальтезе) - 2500р.</option>
                                <option value="5">Чихуахуа длиношерстный - 2300р.</option>
                                <option value="6">Ши-тсу - 2700р.</option>
                                <option value="7">Той терьер длиношерстный - 2300р.</option>
                                <option value="8">Такса длиношерстная - 2500р.</option>
                                <option value="9">Голден ретривер - 5000р.</option>
                                <option value="10">Афганская борзая - 5500р.</option>
                                <option value="11">Колли - 5000р.</option>
                                <option value="12">Немецкая овчарка - 5000р.</option>
                                <option value="13">Ньюфаундленд - 6000р.</option>
                                <option value="14">Самоедская собака - 5500р.</option>
                                <option value="15">Шпиц - 2800р.</option>
                                <option value="16">Вест-хайленд-уайт терьер - 3000р.</option>
                                <option value="17">Бордер терьер - 2800р.</option>
                                <option value="18">Гриффон - 2600р.</option>
                                <option value="19">Джек-рассел-терьер ж/ш - 2500р.</option>
                                <option value="20">Норвич-терьер - 2800р.</option>
                                <option value="21">Такса ж/ш карликовая - 2700р.</option>
                                <option value="22">Такса ж/ш стандартная - 3000р.</option>
                                <option value="23">Эрдельтерьер - 4500р.</option>
                                <option value="24">Цвергшнауцер - 2900р.</option>
                                <option value="25">Миттельшнауцер - 3800р.</option>
                                <option value="26">Ризеншнауцер - 6300р.</option>
                            </select>
                            <div class="b-error"><span class="js-message"></span></div>
                        </div>
                    </div>

                    <div class="form-signup-grooming-flagship__btn-wrap">
                        <button type="submit" class="b-button" data-popup-id="grooming-flagship-store" data-btn-grooming-flagship-store="true">Записаться</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
