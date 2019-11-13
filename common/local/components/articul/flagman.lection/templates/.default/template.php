<section class="service-flagship-store" data-item-service-flagship-store="lectures">
    <div class="b-container">
        <div class="service-flagship-store__header service-flagship-store__header_lectures">
            <div class="service-flagship-store__inner-header">
                <div class="service-flagship-store__title">Лекции</div>
            </div>
        </div>
        <div class="service-flagship-store__content" data-content-service-flagship-store="true">
            <div class="service-flagship-store__descr">
                Cтартовал курс тематических лекций, посвящённых здоровью кошек и&nbsp;собак. Вам расскажут, как правильно ухаживать
                за&nbsp;четвероногими любимцами, купать, кормить и&nbsp;лечить их, как путешествовать с&nbsp;животными.
            </div>

            <div class="lectures-flagship-store">
                <div class="lectures-flagship-store__list">
                    <?php foreach ($arResult['ITEMS'] as $item) : ?>
                        <div class="item">

                            <div class="item__img" style="background-image: url('<?= ($item['PREVIEW_PICTURE']) ? ($item['PREVIEW_PICTURE']): "/static/build/images/inhtml/no_image_flagship.jpg" ?>')"></div>
                            <div class="item__content">
                                <div class="item__info">
                                    <?php if ($item['AVAILABLE'] == 'Y') : ?>
                                        <div class="item__count" data-label-lectures-flagship-store="true"><?=$item['FREE_SITS']?> из <?=$item['SITS']?> свободных мест</div>
                                    <?php else: ?>
                                        <div class="item__count disabled" data-label-lectures-flagship-store="true"><?=$item['SITS']?> из <?=$item['SITS']?> мест заняты</div>
                                    <?php endif; ?>
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