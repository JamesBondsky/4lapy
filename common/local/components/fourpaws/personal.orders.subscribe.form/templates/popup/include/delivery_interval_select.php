<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use FourPaws\DeliveryBundle\Entity\CalculationResult\DeliveryResultInterface;
use FourPaws\DeliveryBundle\Entity\Interval;

/** @var DeliveryResultInterface $tmpDelivery */
if ($tmpDelivery = $nextDeliveries[0]) {
    $availableIntervals = $tmpDelivery->getAvailableIntervals();
    ?>
    <div class="b-input-line b-input-line--interval">
        <div class="b-input-line__label-wrapper b-input-line__label-wrapper--interval">
            <span class="b-input-line__label">интервал</span>
        </div>
        <div class="b-select b-select--recall b-select--feedback-page b-select--interval">
            <select class="b-select__block b-select__block--recall b-select__block--feedback-page b-select__block--interval js-select-recovery <?=($isHidden) ? 'js-no-valid' : ''?>" data-edit-subscribe="1"
                    <?=($isHidden) ? 'disabled' : ''?>
                    name="<?= $selectorName ?>">
                <option value="" disabled="disabled" selected="selected">
                    выберите
                </option>
                <?php
                /** @var Interval $interval */
                foreach ($availableIntervals as $i => $interval) { ?>
                    <option value="<?= $interval ?>" <?= (str_replace(" ", "", (string)$selectedInterval) == str_replace(" ", "", (string)$interval)) ? 'selected' : ''?>>
                        <?= (string)$interval ?>
                    </option>
                <?php } ?>
            </select>
        </div>
    </div>
<?php } ?>
