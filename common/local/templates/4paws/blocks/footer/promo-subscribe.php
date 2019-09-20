<?php
use FourPaws\Decorators\SvgDecorator;
?>

<section class="b-popup-promo-subscribe js-popup-section" data-popup="promo-subscribe">
    <a class="b-popup-promo-subscribe__close js-close-popup" href="javascript:void(0);" title="Закрыть"></a>
    <div class="b-popup-promo-subscribe__content">
        <div class="b-popup-promo-subscribe__title">
            Наслаждайся экономией денег и&nbsp;времени! Оформи
            <span class="blue-subscribe">
                <nobr>
                    <span class="logo-subscr"><?= new SvgDecorator('icon-logo-subscription', 20, 18) ?></span>
                    Подписку
                </nobr>
            </span>
            на&nbsp;доставку со&nbsp;скидкой
        </div>
        <div class="b-popup-promo-subscribe__subtitle">Это легко, выбери подписку при оформлении заказа!</div>
        <ul class="b-popup-promo-subscribe__list">
            <li class="b-popup-promo-subscribe__item">Установи свое расписание</li>
            <li class="b-popup-promo-subscribe__item">Переноси или отменяй в&nbsp;любое время</li>
            <li class="b-popup-promo-subscribe__item">Наслаждайся скидками</li>
        </ul>
    </div>
</section>