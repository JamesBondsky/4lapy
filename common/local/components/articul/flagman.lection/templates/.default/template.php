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
                <div class="lectures-flagship-store__list">
                    <?php foreach ($arResult['ITEMS'] as $item) : ?>
                        <div class="item">

                            <div class="item__content">
                                <div class="item__info">
                                    <div class="item__title"><?=$item['NAME']?></div>
                                </div>
                                <div class="item__datetime">
                                    <div class="item__col-date">
                                        <div class="item__subtitle">Дата</div>
                                        <div class="item__text"><?=$item['DATE']?></div>
                                    </div>
                                    <div class="item__col-time">
                                        <div class="item__subtitle">Время</div>
                                        <div class="item__text"><?=$item['TIME']?></div>
                                    </div>
                                </div>
                                <div class="item__btn">
                                    <?php if ($item['AVAILABLE'] == 'Y') : ?>
                                        <div class="b-button" data-popup-id="lectures-flagship-store" data-id-lectures-flagship-store="3" data-eventId="<?=$item['ID']?>">Записаться</div>
                                    <?php else: ?>
                                        <div class="b-button disabled" data-id-lectures-flagship-store="1">Запись окончена</div>
                                    <?php endif; ?>

                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>