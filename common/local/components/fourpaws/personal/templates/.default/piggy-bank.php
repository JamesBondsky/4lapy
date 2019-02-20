<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$activeMarks = 8;
$typeSale = 'small'; //small-10, middle-20, large-30
$isActiveNextType = true; // возможно ли получить следующую скидку

?>


<div class="b-kopilka">
    <h2 class="b-title b-kopilka__title">Копилка-собиралка. Копи марки, покупай со скидкой до -30%!</h2>

    <div class="b-coupon-kopilka <?php if($typeSale) { ?>b-coupon-kopilka--<?= $typeSale ?><?php } ?> <?php if($isActiveNextType && ($typeSale != 'large')) { ?>b-coupon-kopilka--next-sale<?php } ?>">
        <div class="b-coupon-kopilka__marks">
            <div class="top-marks-mobile">
                <div class="top-marks-mobile__logo"></div>
                <div class="top-marks-mobile__title">
                    Мои марки 25/<span class="top-marks-mobile__title-all-count">25</span>
                </div>
                <div class="top-marks-mobile__btn" data-toggle-marks-mobile-kopilka="true"></div>
            </div>
            <div class="b-coupon-kopilka__marks-content" data-content-marks-mobile-kopilka="true">
                <div class="list-coupon-marks__wrap">
                    <div class="list-coupon-marks">
                        <?php for ($i = 1; $i <= 25; $i++) { ?>
                            <div class="b-mark-kopilka__wrap">
                                <div class="b-mark-kopilka
                                <?php if(($i == 7)||($i == 15)||($i == 25)) { ?>b-mark-kopilka--sale<?php } ?>
                                <?php if($i <= $activeMarks) { ?>active<?php } ?>"
                                >
                                    <span class="b-mark-kopilka__number"><?= $i ?></span>
                                    <?php if(($i == 7)||($i == 15)||($i == 25)) { ?>
                                        марок
                                    <?php } ?>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <div class="legend-coupon-marks">
                    <div class="legend-coupon-marks__title">Марок<br/> для скидки</div>
                    <div class="legend-coupon-marks__item">
                        <div class="b-mark-kopilka b-mark-kopilka--sale active">
                            <span class="b-mark-kopilka__number">7</span> марок
                        </div>
                        <div class="legend-coupon-marks__persent">
                            <span>10%</span> Скидка
                        </div>
                    </div>
                    <div class="legend-coupon-marks__item">
                        <div class="b-mark-kopilka b-mark-kopilka--sale">
                            <span class="b-mark-kopilka__number">15</span> марок
                        </div>
                        <div class="legend-coupon-marks__persent">
                            <span>20%</span> Скидка
                        </div>
                    </div>
                    <div class="legend-coupon-marks__item">
                        <div class="b-mark-kopilka b-mark-kopilka--sale">
                            <span class="b-mark-kopilka__number">25</span> марок
                        </div>
                        <div class="legend-coupon-marks__persent">
                            <span>30%</span> Скидка
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="b-coupon-kopilka__sale">
            <div class="b-sale-coupon-kopilka <?php if($typeSale) { ?>b-sale-coupon-kopilka--<?= $typeSale ?><?php } ?> <?php if($isActiveNextType && ($typeSale != 'large')) { ?>b-sale-coupon-kopilka--next-sale<?php } ?>">
                <?php if($typeSale) { ?>
                    <div class="b-sale-coupon-kopilka__top">
                        <div class="b-sale-coupon-kopilka__title">
                            <span class="persent">10%</span>
                            <span>Ваша скидка</span>
                        </div>
                        <div class="b-sale-coupon-kopilka__digital-code">
                            <span class="text">ABC123DFE4567</span>
                            <a href="#" class="link">Скопировать</a>
                        </div>
                        <div class="b-sale-coupon-kopilka__barcode">
                            <div class="b-sale-coupon-kopilka__barcode-img">
                                <img src="/static/build/images/content/barcode-kopilka.png" alt="" />
                            </div>
                            <a href="#"class="link">Отправить мне на Email</a>
                        </div>
                        <?php if(!$isActiveNextType && ($typeSale != 'large')) { ?>
                            <div class="b-sale-coupon-kopilka__info">
                                Осталось 8 марок до&nbsp;скидки 20%
                            </div>
                        <?php } ?>
                    </div>
                <?php }else { ?>
                    <div class="b-sale-coupon-kopilka__default">
                        <span class="hide-mobile">До скидки</span>
                        <span class="b-sale-coupon-kopilka__default-persent">10% <span class="show-mobile">скидка</span></span>
                        <span>осталось</span>
                        <span class="b-sale-coupon-kopilka__default-count">6 марок</span>
                    </div>
                <?php } ?>
                <?php if($isActiveNextType && ($typeSale != 'large')) { ?>
                    <div class="b-sale-coupon-kopilka__bottom">
                        <div class="b-sale-coupon-kopilka__title">
                            <span class="persent">20%</span>
                            <span>Получить скидку</span>
                        </div>
                        <div class="b-sale-coupon-kopilka__btn-wrap">
                            <div class="b-sale-coupon-kopilka__btn">Обменять 15 марок</div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <div class="b-kopilka__info">
        <h3>Условия акции</h3>
        <p>За каждые 400 рублей в чеке получайте:</p>
        <ul>
            <li>2 марки на товары из категории “Ветаптека”;</li>
            <li>1 марку на все остальные товары. Марки будут начислены после завершения заказа.</li>
        </ul>
        <p>Меняйте марки на купоны на скидку:</p>
        <p><b>7 марок</b> - <span class="orange">скидка 10%</span>;</p>
        <p><b>15 марок</b> - <span class="orange">скидка 20%</span>;</p>
        <p><b>25 марок</b> - <span class="orange">скидка 30%</span>.</p>
        <p>Купоны не имеют срока действия. Для использования купона скопируйте промокод и вставьте во время оформления заказа.</p>
        <p>Купон можно использовать в наших магазинах. Для этого сообщите промокод на кассе.</p>
        <p>Период начисления марок: 1.03.2019 - 30.03.2019</p>
        <a href="#">Подробные условия акции</a>
    </div>
</div>