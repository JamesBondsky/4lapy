<section class="service-flagship-store service-flagship-store_walking" data-item-service-flagship-store="walking">
    <div class="b-container">
        <div class="service-flagship-store__header service-flagship-store__header_walking">
            <div class="service-flagship-store__inner-header">
                <div class="service-flagship-store__title">
                    <nobr>Выгул-тренировка</nobr>
                    <br/> собак
                </div>
                <div class="service-flagship-store__btn" data-toggle-service-flagship-store="true"></div>
            </div>
        </div>
        <div class="service-flagship-store__content" data-content-service-flagship-store="true">
            <div class="steps-walking-flagship-store">
                <div class="steps-walking-flagship-store__title">Как проходит тренировка?</div>
                <div class="steps-walking-flagship-store__list">
                    <div class="item">
                        <div class="item__number">1</div>
                        <div class="item__descr">
                            Вы&nbsp;приводите своего пса к&nbsp;нам в&nbsp;магазин в&nbsp;выбраный день и&nbsp;время, сытым и&nbsp;после прогулки.
                        </div>
                    </div>
                    <div class="item">
                        <div class="item__number">2</div>
                        <div class="item__descr">
                            Проводим вам краткую лекцию по&nbsp;обращению с&nbsp;псом, показываем приемы дресссировки
                        </div>
                    </div>
                    <div class="item">
                        <div class="item__number">3</div>
                        <div class="item__descr">
                            Гуляем и&nbsp;развлекаем<br/> вашего любимца оговоренное время
                        </div>
                    </div>
                </div>
            </div>

            <div class="timetable-walking-flagship-store">
                <div class="timetable-walking-flagship-store__title">Расписание</div>

                <div class="timetable-walking-flagship-store__list">
                    <?php foreach ($arResult['SCHEDULE'] as $day) : ?>
                        <div class="item" data-group-interval-walking-flagship="true">
                            <div class="item__date"><?=$day['day']?></div>

                            <div class="item__interval item__interval_mobile">
                                <div class="b-input-line">
                                    <div class="b-input-line__label-wrapper">
                                        <span class="b-input-line__label">Интервал</span>
                                    </div>
                                    <div class="b-select">
                                        <select class="b-select__block" <?php if ($day['end'] == 'Y') : ?>disabled<?php endif; ?>>
                                            <option value="" <?php if ($day['end'] == 'Y') : ?>disabled="disabled"<?php endif; ?> selected="selected">выберите</option>
                                            <?php foreach ($day['times'] as $time) : ?>
                                                <?php if ($time['status'] != 'N') : ?>
                                                    <option value="<?=$time['id']?>" data-id-interval-walking-flagship="<?=$time['id']?>"><?=$time['interval']?></option>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="b-error"><span class="js-message"></span></div>
                                    </div>
                                </div>
                            </div>

                            <div class="item__interval">
                                <?php foreach ($day['times'] as $time) : ?>
                                    <div class="item__btn-interval <?php if ($time['status'] == 'N') : ?>disabled<?php endif; ?>"
                                         data-id-interval-walking-flagship="<?=$time['id']?>"><?=$time['interval']?></div>
                                <?php endforeach; ?>
                            </div>
    
                            <?php if ($day['end'] == 'Y') : ?>
                                <div class="b-button disabled" disabled data-select-interval-walking-flagship="true">Запись окончена</div>
                            <?php else : ?>
                                <div class="b-button js-open-popup" data-popup-id="walking-flagship-store" data-select-interval-walking-flagship="true">Выберите интервал
                                </div>
                            <?php endif; ?>
                            
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>