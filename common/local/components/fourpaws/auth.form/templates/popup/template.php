<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * @var \CBitrixComponentTemplate $this
 *
 * @var array                     $arParams
 * @var array                     $arResult
 * @var array                     $templateData
 *
 * @var string                    $componentPath
 * @var string                    $templateName
 * @var string                    $templateFile
 * @var string                    $templateFolder
 *
 * @global CUser                  $USER
 * @global CMain                  $APPLICATION
 * @global CDatabase              $DB
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
