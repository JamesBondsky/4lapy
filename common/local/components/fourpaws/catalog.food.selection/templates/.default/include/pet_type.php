<?php
use FourPaws\BitrixOrm\Model\IblockSect;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var array $sections
 * @var string $nextUrl
 * @var string $val
 * @var bool $required
 */
if (!\is_array($sections) || empty($sections)) {
    return;
} ?>
<div class="b-quest b-quest--step-1 js-quest js-quest--step-1" data-step="1">
    <h3 class="b-quest__title">Питомец</h3>
    <h4 class="b-quest__subtitle">Тип</h4>
    <?php /** @var IblockSect $item */
    foreach ($sections as $key => $item) {?>
        <div class="b-radio b-radio--q-food">
            <input class="b-radio__input"
                   type="radio"
                   name="pet_type"
                   value="<?= $item->getId() ?>"
                   id="id-quest-pet_type-<?= $key ?>"
                   data-url="<?=$nextUrl?>"
                <?=$required ? ' required="required"' : ''?>
                <?=$val === $item->getId() ? ' checked="checked"' : ''?>
            />
            <label class="b-radio__label b-radio__label--q-food"
                   for="id-quest-pet_type-<?= $key ?>">
                <span class="b-radio__text-label"><?= $item->getName() ?></span>
            </label>
        </div>
    <?php } ?>
</div>
