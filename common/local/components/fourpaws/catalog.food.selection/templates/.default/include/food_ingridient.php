<?php

use FourPaws\BitrixOrm\Model\IblockSect;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var int $nextStep
 * @var array $sections
 * @var string $nextUrl
 * @var string $val
 * @var bool $required
 */
if (!\is_array($sections) || empty($sections)) {
    return;
} ?>
<div class="b-quest js-quest <?=$required ? ' js-block-required' : ''?>" style="display: block">
    <h4 class="b-quest__subtitle">Особенности</h4>
    <div class="b-select b-select--recall b-select--q-food">
        <select class="b-select__block b-select__block--recall b-select__block--q-food"
                name="food_ingridient"
                data-url="<?=$nextUrl?>"
            <?=$required ? ' required="required"' : ''?>>
            <option value="0" <?=$val === 0 ? ' selected="selected"' : ''?>>Не важно</option>
            <?php /** @var IblockSect $item */
            foreach ($sections as $key => $item) {
                ?>
                <option value="<?= $item->getId() ?>" <?=$val === $item->getId() ? ' selected="selected"' : ''?>><?= $item->getName() ?></option>
            <?php
            } ?>
        </select>
    </div>
</div>
