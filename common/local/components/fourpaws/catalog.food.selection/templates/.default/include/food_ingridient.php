<?php

use FourPaws\BitrixOrm\Model\IblockSect;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var int $nextStep
 * @var array $sections
 */
if (!\is_array($sections) || empty($sections)) {
    return;
} ?>
<div class="b-quest b-quest--step-<?= $nextStep ?> js-quest js-quest--step-<?= $nextStep ?>" style="display: block">
    <h4 class="b-quest__subtitle">Особенности</h4>
    <div class="b-select b-select--recall b-select--q-food">
        <select class="b-select__block b-select__block--recall b-select__block--q-food"
                name="food_ingridient"
                data-select="1">
            <option selected="selected">Не важно</option>
            <?php /** @var IblockSect $item */
            foreach ($sections as $key => $item) {
                ?>
                <option value="<?= $item->getId() ?>"><?= $item->getName() ?></option>
            <?php
            } ?>
        </select>
    </div>
</div>
