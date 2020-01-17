<?php if (!empty($arResult['SECTIONS'])): ?>
    <section class="service-flagship-store" data-item-service-flagship-store="training">
        <div class="b-container">
            <div class="service-flagship-store__header service-flagship-store__header_training">
                <div class="service-flagship-store__inner-header">
                    <div class="service-flagship-store__header-title">Тренировочный клуб</div>
                </div>
            </div>
            <div class="service-flagship-store__content" data-content-service-flagship-store="true" style="display: block">
                <div class="service-flagship-store__title">
                    В&nbsp;тренировочном клубе вы&nbsp;можете пройти занятия по&nbsp;послушанию вместе со&nbsp;своей собакой
                </div>
                <div class="service-flagship-store__descr">
                    Занятия направлены на&nbsp;обучение хозяина питомца грамотно взаимодействовать с&nbsp;ним. Обучить питомца необходимым, элементарным командам
                    (&laquo;рядом&raquo;, &laquo;ко&nbsp;мне&raquo;, &laquo;место&raquo;, &laquo;сидеть&raquo;, &laquo;лежать&raquo;, &laquo;стоять&raquo;, &laquo;фу&raquo;, &laquo;нет&raquo;)
                    питомца. Занятия можно начинать с&nbsp;4&nbsp;месяцев. Время 1 занятия 60&nbsp;минут. Требуемое количество занятий 5&ndash;10.<br /><br />
                    Возможно записаться как на&nbsp;групповое занятие (группа до&nbsp;5 человек), так и&nbsp;на&nbsp;индивидуальное.
                </div>
                <a class="link-walking-flagship-store" href="/events/Правила_тренировочного_клуба.pdf" target="_blank">Правила тренировочного клуба</a>

                <form class="form-signup-training-flagship js-form-validation" data-form-signup-training-flagship="true">
                    <div class="form-signup-training-flagship__content">
                        <div class="b-input-line">
                            <div class="b-input-line__label-wrapper">
                                <span class="b-input-line__label">Дата</span>
                            </div>
                            <div class="b-select">
                                <select class="b-select__block" data-date-training-flagship="true">
                                    <option value="" disabled="disabled" selected="selected">выберите</option>
                                    <?php foreach ($arResult['SECTIONS'] as $section) : ?>
                                        <option value="<?=$section['ID']?>" data-url="/flagman/getlocalschedule/<?=$section['ID']?>/"
                                                data-date-option="<?=$section['NAME']?>"><?=$section['NAME']?></option>
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
                                <select class="b-select__block" data-time-training-flagship="true">
                                    <option value="" disabled="disabled" selected="selected">выберите</option>
                                </select>
                                <div class="b-error"><span class="js-message"></span></div>
                            </div>
                        </div>

                        <div class="form-signup-training-flagship__btn-wrap">
                            <button type="submit" class="b-button" data-popup-id="training-flagship-store" data-btn-training-flagship-store="true">Записаться</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
<?php endif; ?>