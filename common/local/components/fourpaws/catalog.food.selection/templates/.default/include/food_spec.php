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
<div class="b-quest b-quest--step-<?= $nextStep ?> js-quest js-quest--step-<?= $nextStep ?> <?=$required ? ' js-block-required' : ''?>" style="display: block">
    <h3 class="b-quest__title">Корм</h3>
    <h4 class="b-quest__subtitle">Специализация</h4>
    <div class="b-select b-select--recall b-select--q-food">
        <select class="b-select__block b-select__block--recall b-select__block--q-food"
                name="food_spec"
                data-select="<?= ++$_SESSION['SELECT_NUMBER'] ?>"
                data-url="<?=$nextUrl?>"
            <?=$required ? ' required="required"' : ''?>>
            <option disabled="disabled" selected="selected">--Не выбрано--</option>
            <option value="0">Любой</option>
            <?php /** @var IblockSect $item */
            foreach ($sections as $key => $item) { ?>
                <option value="<?= $item->getId() ?>" <?=$val === $item->getId() ? ' selected="selected"' : ''?>><?= $item->getName() ?></option>
            <?php
            } ?>
        </select>
    </div>
</div>
