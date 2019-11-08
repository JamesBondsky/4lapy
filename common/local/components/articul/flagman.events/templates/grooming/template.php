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
            <form class="form-signup-grooming-flagship js-form-validation">
                <div class="form-signup-grooming-flagship__content">
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

                    <div class="b-input-line">
                        <div class="b-input-line__label-wrapper">
                            <span class="b-input-line__label">Время</span>
                        </div>
                        <div class="b-select">
                            <select class="b-select__block" disabled>
                                <option value="" disabled="disabled" selected="selected">выберите</option>
                                <option value="1">10:00 - 14:00</option>
                                <option value="2">14:00 - 18:00</option>
                                <option value="3">18:00 - 22:00</option>
                                <option value="4">20:00 - 00:00</option>
                                <option value="5">10:00 - 18:00</option>
                                <option value="6">18:00 - 00:00</option>
                            </select>
                            <div class="b-error"><span class="js-message"></span></div>
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

                    <div class="b-input-line">
                        <div class="b-input-line__label-wrapper">
                            <span class="b-input-line__label">Услуга</span>
                        </div>
                        <div class="b-select">
                            <select class="b-select__block">
                                <option value="" disabled="disabled" selected="selected">выберите</option>
                                <option value="0" data-date-option="Четверг, 2019-11-07">Мытье и сушка</option>
                            </select>
                        </div>
                    </div>

                    <div class="b-button disabled" data-popup-id="grooming-flagship-store">Записаться</div>
                </div>
            </form>
        </div>
    </div>
</section>
