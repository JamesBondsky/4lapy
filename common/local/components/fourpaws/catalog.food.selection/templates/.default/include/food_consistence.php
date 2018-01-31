<?php

use FourPaws\BitrixOrm\Model\IblockSect;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var int $nextStep
 * @var array $sections
 * @var string $nextUrl
 */
if (!\is_array($sections) || empty($sections)) {
    return;
} ?>
<div class="b-quest b-quest--step-<?= $nextStep?> js-quest js-quest--step-<?=$nextStep?>" style="display: block">
    <h4 class="b-quest__subtitle">Тип</h4>
    <div class="b-radio b-radio--q-food">
        <input class="b-radio__input"
               name="food_consistence"
               id="id-quest-type-food-default"
               data-radio="<?= ++$_SESSION['RADIO_NUMBER'] ?>"
               type="radio"
               value="0"
               data-url="<?=$nextUrl?>"
        >
        <label class="b-radio__label b-radio__label--q-food" for="id-quest-type-food-default">
            <span class="b-radio__text-label">Любой</span>
        </label>
    </div>
    <?php /** @var IblockSect $item */
    foreach ($sections as $key => $item) {
        ?>
        <div class="b-radio b-radio--q-food">
            <input class="b-radio__input"
                   name="food_consistence"
                   id="id-quest-type-food-<?= $key ?>"
                   data-radio="<?= ++$_SESSION['RADIO_NUMBER'] ?>"
                   type="radio"
                   value="<?= $item->getId() ?>"
                   data-url="<?=$nextUrl?>">
            <label class="b-radio__label b-radio__label--q-food" for="id-quest-type-food-<?= $key ?>">
                <span class="b-radio__text-label"><?= $item->getName() ?></span>
            </label>
        </div>
    <?php
    } ?>
</div>
