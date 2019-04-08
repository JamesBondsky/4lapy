<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\Decorators\SvgDecorator;

?>
<div class="subscribe-delivery-order">
    <div class="subscribe-delivery-order__fields">
        <div class="b-input-line b-input-line--delivery-frequency-subscribe">
            <div class="b-input-line__label-wrapper">
                <span class="b-input-line__label">Как часто доставлять</span>
            </div>
            <div class="b-select b-select--recall b-select--feedback-page">
                <select class="b-select__block b-select__block--recall b-select__block--feedback-page" name="subscribeFrequency" data-select="0">
                    <option value="" disabled="disabled">выберите</option>
                    <?php
                    foreach ($subscribeIntervals as $i => $interval) { ?>
                        <option value="<?= $interval['ID'] ?>">
                            <?= (string)$interval['VALUE'] ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <div class="b-input-line b-input-line--date-delivery-subscribe">
            <div class="b-input-line__label-wrapper">
                <span class="b-input-line__label">День доставки</span>
            </div>
            <div class="b-select b-select--recall b-select--feedback-page">
                <select class="b-select__block b-select__block--recall b-select__block--feedback-page" name="subscribeDay" data-select="0">
                    <option value="" disabled="disabled" selected="selected">выберите</option>
                    <?php
                    foreach ($daysOfWeek as $i => $day) { ?>
                        <option value="<?= ($i+1) ?>">
                            <?= $day ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <div class="subscribe-delivery-order__date-second-delivery">
            Дата второй доставки:<br/>
            <span class="bold js-date-second-delivery">понедельник, 8 апреля</span>
        </div>
    </div>
    <div class="subscribe-delivery-order__info">
        <span class="subscribe-delivery-order__icon">
            <?= new SvgDecorator('icon-info-contour', 18, 18) ?>
        </span>
        Для уточнения точной даты и&nbsp;времени доставки с&nbsp;вами будет связываться менеджер за&nbsp;несколько дней в&nbsp;момент формирования заказа
    </div>
</div>