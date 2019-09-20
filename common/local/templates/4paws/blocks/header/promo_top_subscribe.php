<?php
use FourPaws\Decorators\SvgDecorator;
?>

<div class="b-promo-top-full b-promo-top-full--subscribe js-promo-top-full hide">
    <div class="b-promo-top-full__container">
        <div class="b-promo-top-full__title">
            Скидка до&nbsp;15% на&nbsp;вашу
            <span class="blue-subscribe">
                <nobr>
                    <span class="logo-subscr"><?= new SvgDecorator('icon-logo-subscription', 20, 18) ?></span>
                    Подписку
                </nobr>
            </span>
            <span class="show-mobile">на&nbsp;доставку</span>
        </div>
        <a href="#" target="_blank" class="b-promo-top-full__btn">Подробнее</a>
        <div class="b-promo-top-full__close js-close-promo-top-full"></div>
    </div>
</div>