<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\Decorators\SvgDecorator;
use FourPaws\PersonalBundle\Entity\OrderSubscribe;
use FourPaws\PersonalBundle\Service\OrderSubscribeService;

/** @var OrderSubscribeService $orderSubscribeService */
$orderSubscribeService = $component->getOrderSubscribeService();

/** @var OrderSubscribe $orderSubscribe */
$orderSubscribe = $arResult['ORDER_SUBSCRIBE'];
if($orderSubscribe){
    $selectedFrequency = $orderSubscribe->getFrequency();
    $selectedDay = $orderSubscribe->getDeliveryDay();
}
?>
<div class="subscribe-delivery-order" data-subscribe-delivery-order="true">
    <div class="subscribe-delivery-order__fields">
        <div class="b-input-line b-input-line--delivery-frequency-subscribe" data-select-wrap-delivery-order="subscribeFrequency">
            <div class="b-input-line__label-wrapper">
                <span class="b-input-line__label">Как часто доставлять</span>
            </div>
            <div class="b-select b-select--recall b-select--feedback-page">
                <select class="b-select__block b-select__block--recall b-select__block--feedback-page" name="subscribeFrequency" data-select="0" data-select-delivery-order="subscribeFrequency">
                    <option value="" disabled="disabled">выберите</option>
                    <?php
                    foreach ($subscribeIntervals as $i => $frequency) { ?>
                        <option value="<?= $frequency['ID'] ?>"
                                <?=($selectedFrequency == $frequency) ? 'selected' : ''?>
                                data-freq-type="<?=$orderSubscribeService->getFrequencyType($frequency)?>"
                                data-freq-value="<?=$orderSubscribeService->getFrequencyValue($frequency)?>">
                            <?= (string)$frequency['VALUE'] ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <div class="b-input-line b-input-line--date-delivery-subscribe" data-select-wrap-delivery-order="subscribeDay">
            <div class="b-input-line__label-wrapper">
                <span class="b-input-line__label">День доставки</span>
            </div>
            <div class="b-select b-select--recall b-select--feedback-page">
                <select class="b-select__block b-select__block--recall b-select__block--feedback-page" name="subscribeDay" data-select="0" data-select-delivery-order="subscribeDay">
                    <option value="" disabled="disabled" selected="selected">выберите</option>
                    <?php
                    foreach ($daysOfWeek as $i => $day) { ?>
                        <option value="<?= ($i+1) ?>" <?=($selectedDay == ($i+1)) ? 'selected' : ''?>>
                            <?= $day ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <div class="subscribe-delivery-order__date-second-delivery" data-wrap-date-second-delivery-subscribe="true" style="display:none">
            Дата следующей доставки:<br/>
            <span class="bold js-date-second-delivery">понедельник, 8 апреля</span>
        </div>
    </div>
    <div class="subscribe-delivery-order__info js-info-subscribe-delivery-order">
        <span class="subscribe-delivery-order__icon">
            <?= new SvgDecorator('icon-info-contour', 18, 18) ?>
        </span>
        Для уточнения точной даты и&nbsp;времени доставки с&nbsp;вами будет связываться менеджер за&nbsp;несколько дней в&nbsp;момент формирования заказа
    </div>
</div>