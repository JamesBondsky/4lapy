<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
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

if (!\is_array($arResult['ITEMS']) || empty($arResult['ITEMS'])) {
    return;
}
?>
<div class="fleas-protection-block__questions--block js-question-block active" data-type="faq">
    <?php foreach ($arResult['ITEMS'] as $item) {
        if (empty($item['PREVIEW_TEXT']) || empty($item['DETAIL_TEXT'])) {
            continue;
        } ?>
        <div class="fleas-protection-block__questions--item">
            <div class="fleas-protection-block__questions--item-title">
                <?= $item['PREVIEW_TEXT'] ?>
            </div>
            <div class="fleas-protection-block__questions--item-dropdown">
                <?= $item['DETAIL_TEXT'] ?>
            </div>
        </div>
    <?php } ?>
</div>