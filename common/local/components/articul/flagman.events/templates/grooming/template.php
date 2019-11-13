<section class="service-flagship-store" data-item-service-flagship-store="grooming">
    <div class="b-container">
        <div class="service-flagship-store__header service-flagship-store__header_grooming">
            <div class="service-flagship-store__inner-header">
                <div class="service-flagship-store__title">Груминг</div>
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
                                            <option value="<?=$key?>" data-url="/flagman/getschedule/grooming/<?=$key?>/" data-date-option="<?=$day['day']?>"><?=$day['day']?></option>
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

<script>
    window.typeServiceGrooming = {
        'dog': ['Стрижка от 1800р.', 'Мытьё и сушка от 1100р.', 'Стрижка когтей - 350р.', 'Обработка ушей - 350р.', 'Обработка лап (стрижка между пальцами) - 350р.', 'Чистка паранальных желез - 300р.', 'Выбривание узоров на шерсти (1 простой узор) - 500р.'],
        'cat': ['Вычесывание - 1000р.', 'Стрижка от 2300р.', 'Стрижка когтей - 350р.', 'Обработка ушей - 350р.', 'Обработка лап - 350р.', 'Выбривание узоров на шерсти (1 простой узор) - 500р.', 'Антицарапки - 500р.', 'Окантовка лап - 600р.']
    }
</script>