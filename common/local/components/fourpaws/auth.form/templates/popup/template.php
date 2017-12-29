<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * @var array $arResult
 */

/** @noinspection PhpIncludeInspection */
if (empty($arResult['STEP'])) {
    return;
} ?>
<section class="b-popup-pick-city b-popup-pick-city--authorization js-popup-section"
         data-popup="authorization">
    <a class="b-popup-pick-city__close b-popup-pick-city__close--authorization js-close-popup"
       href="javascript:void(0);"
       title="Закрыть"></a>
    <?php require_once 'include/' . $arResult['STEP'] . '.php'; ?>
</section>
