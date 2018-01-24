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
<div class="b-quest b-quest--step-<?= $nextStep ?> js-quest js-quest--step-<?= $nextStep ?>">
    <h4 class="b-quest__subtitle">Вкус</h4>
    <div class="b-select b-select--recall b-select--q-food">
        <select class="b-select__block b-select__block--recall b-select__block--q-food"
                name="quest-taste"
                data-select="2">
            <option selected="selected">Любой</option>
            <?php /** @var IblockSect $item */
            foreach ($sections as $key => $item) {
                ?>
                <option value="<?= $item->getId() ?>"><?= $item->getName() ?></option>
            <?php
            } ?>
        </select>
    </div>
</div>
