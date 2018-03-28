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
    <h4 class="b-quest__subtitle">Размер</h4>
    <?php /** @var IblockSect $item */
    foreach ($sections as $key => $item) {
        ?>
        <div class="b-radio b-radio--q-food">
            <input class="b-radio__input"
                   name="pet_size"
                   id="id-quest-size-<?= $key ?>"
                   data-radio="<?= ++$_SESSION['RADIO_NUMBER'] ?>"
                   type="radio"
                   value="<?= $item->getId() ?>"
                   data-url="<?=$nextUrl?>"
                <?=$required ? ' required="required"' : ''?>
                <?=$val === $item->getId() ? ' checked="checked"' : ''?>>
            <label class="b-radio__label b-radio__label--q-food" for="id-quest-size-<?= $key ?>">
                <span class="b-radio__text-label"><?= $item->getName() ?></span>
            </label>
        </div>
    <?php
    } ?>
</div>
