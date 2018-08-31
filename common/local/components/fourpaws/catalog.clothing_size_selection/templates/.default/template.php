<?php
/**
 * @var array $arParams
 * @var array $arResult
 */
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var array $item
 */
$items = [];
foreach ($arResult['ITEMS'] as $item) {
    $items[] = [
        'name'      => $item['UF_CODE'],
        'back_min'  => $item['UF_BACK_MIN'],
        'back_max'  => $item['UF_BACK_MAX'],
        'chest_min' => $item['UF_CHEST_MIN'],
        'chest_max' => $item['UF_CHEST_MAX'],
        'neck_min'  => $item['UF_NECK_MIN'],
        'neck_max'  => $item['UF_NECK_MAX'],
    ];
}
?>
<script data-skip-moving="true">
    window.clothingSizeSelection = <?= CUtil::PhpToJSObject($items) ?>;
</script>
