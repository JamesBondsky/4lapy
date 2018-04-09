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
    <h4 class="b-quest__subtitle">Тип</h4>
    <?php /** @var IblockSect $item */
    foreach ($sections as $key => $item) {
        ?>
        <div class="b-radio b-radio--q-food">
            <input class="b-radio__input"
                   name="food_consistence"
                   id="id-quest-food_consistence-<?= $key ?>"
                   type="radio"
                   value="<?= $item->getId() ?>"
                   data-url="<?=$nextUrl?>"
            <?=$required ? ' required="required"' : ''?>
                <?=$val === $item->getId() ? ' checked="checked"' : ''?>>
            <label class="b-radio__label b-radio__label--q-food" for="id-quest-food_consistence-<?= $key ?>">
                <span class="b-radio__text-label"><?= $item->getName() ?></span>
            </label>
        </div>
    <?php
    } ?>
</div>
