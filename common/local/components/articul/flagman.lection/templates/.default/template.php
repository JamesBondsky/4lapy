<?php
if (!empty($arResult['ITEMS'])) : ?>

    <section class="service-flagship-store" data-item-service-flagship-store="lectures">
        <div class="b-container">
            <div class="service-flagship-store__header service-flagship-store__header_lectures">
                <div class="service-flagship-store__inner-header">
                    <div class="service-flagship-store__header-title">Лекторий</div>
                </div>
            </div>
            <div class="service-flagship-store__content" data-content-service-flagship-store="true">
                <div class="service-flagship-store__title">
                    Лекторий - это современная аудитория для обучения
                </div>
                <div class="service-flagship-store__descr">
                    Вы&nbsp;сможете прослушать обучающие лекции и&nbsp;семинары на&nbsp;тему содержания и&nbsp;воспитания питомцев от&nbsp;ведущих зоопсихологов, блогеров и&nbsp;ветеринаров.
                </div>

                <div class="lectures-flagship-store">
                    <div class="lectures-flagship-store__list" data-list-lectures-flagship-store="true">
                        <?php foreach ($arResult['ITEMS'] as $key => $item) : ?>
                            <div class="item-wrap" data-wrap-item-lectures-flagship-store="true">
                                <div class="item" data-item-lectures-flagship-store="<?=$key?>">
                                    <div class="item__img" style="background-image: url('<?=($item['PICTURE']) ? ($item['PICTURE']) : "/static/build/images/inhtml/no_image_flagship.jpg"?>')"></div>
                                    <form class="item__content js-form-validation" data-form-signup-grooming-flagship="true">
                                        <div class="item__info <?php if ($item['ADDRESS']) : ?>item__info_top<?php endif; ?>">
                                            <?php if ($item['ADDRESS']) : ?><div class="item__city orange"><?=$item['ADDRESS'] ?></div><?php endif; ?>
                                            <div class="item__title <?php if ($item['DESCRIPTION']) : ?>link<?php endif; ?>" data-name-lectures-flagship="<?=$item['MAIN_SECTION_NAME']?>">
                                                <span><?=$item['MAIN_SECTION_NAME']?></span>
                                            </div>
                                        </div>

                                        <?php if ($item['DESCRIPTION']) : ?>
                                            <div class="item__mobile-descr" data-mobile-descr-lectures-flagship-store="true">
                                                <?=$item['DESCRIPTION']?>
                                            </div>
                                        <?php endif; ?>

                                        <div class="item__datetime">
                                            <div class="item__col-date">
                                                <div class="item__subtitle">Дата</div>
                                                <div class="item__text" data-date-lectures-flagship="<?=$item['SECTION_NAME']?>"><?=$item['SECTION_NAME']?></div>
                                            </div>
                                            <div class="item__col-time">
                                                <div class="item__subtitle">Время</div>

                                                <?php if ($item['AVAILABLE'] == 'Y') : ?>
                                                    <div class="b-select">
                                                        <select class="b-select__block" data-time-lectures-flagship="true">
                                                            <option value="" disabled="disabled" selected="selected">выберите</option>
                                                            <?php foreach ($item['DETAIL_INFO'] as $time) : ?>
                                                                <option value="<?=$time['NAME']?>" data-eventid="<?=$time['ID']?>"><?=$time['NAME']?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                        <div class="b-error"><span class="js-message"></span></div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="item__btn">
                                            <?php if ($item['AVAILABLE'] == 'Y') : ?>
                                                <button type="submit" class="b-button" data-popup-id="lectures-flagship-store" data-signup-lectures-flagship-store="true">Записаться</button>
                                            <?php else: ?>
                                                <div class="b-button disabled">Запись окончена</div>
                                            <?php endif; ?>
                                        </div>
                                    </form>
                                </div>
                                <?php if ($item['DESCRIPTION']) : ?>
                                <div class="item-description" data-descr-item-lectures-flagship-store="true">
                                    <?=$item['DESCRIPTION']?>
                                </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php endif; ?>
