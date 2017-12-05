<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * Bitrix vars
 *
 * @global CMain                 $APPLICATION
 * @global CUser                 $USER
 * @var array                    $arParams
 * @var array                    $arResult
 * @var CBitrixComponentTemplate $this
 * @var CBitrixComponent         $component
 */
?>
<?php
$first = true;
if (!empty($arResult['VALUE'])) { ?>
    <span class="fields">
    <?php
    if(is_array($arResult['VALUE']) && !empty($arResult['VALUE'])) {
        foreach ($arResult['VALUE'] as $res):
            if (!$first):
                ?><span class="fields separator"></span><?
            else:
                $first = false;
            endif;
        
            if (strlen($arParams['arUserField']['PROPERTY_VALUE_LINK']) > 0) {
                $res =
                    '<a href="' . str_replace('#VALUE#',
                                              urlencode($res),
                                              $arParams['arUserField']['PROPERTY_VALUE_LINK']) . '">' . $res . '</a>';
            }
            ?><span class="fields"><?= $res ?></span><?
        endforeach;
    }?>
    </span>
    <?php
}
?>