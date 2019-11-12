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
                    
                    <div class="form-signup-grooming-flagship__btn-wrap">
                        <button type="submit" class="b-button" data-popup-id="grooming-flagship-store" data-btn-grooming-flagship-store="true">Записаться</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
