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
    <h4 class="b-quest__subtitle">Возраст</h4>
    <?php /** @var IblockSect $item */
    foreach ($sections as $key => $item) {
        ?>
        <div class="b-radio b-radio--q-food">
            <input class="b-radio__input"
                   name="pet_age"
                   id="id-quest-age-<?= $key ?>"
                   data-radio="<?= ++$_SESSION['RADIO_NUMBER'] ?>"
                   type="radio"
                   value="<?= $item->getId() ?>"
                   data-url="<?=$nextUrl?>"
                <?=$required ? ' required="required"' : ''?>
                <?=$val === $item->getId() ? ' checked="checked"' : ''?>>
            <label class="b-radio__label b-radio__label--q-food" for="id-quest-age-<?= $key ?>">
                <span class="b-radio__text-label"><?= $item->getName() ?></span>
            </label>
        </div>
    <?php
    } ?>
</div>
