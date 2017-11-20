<?php
/**
 * Bitrix Framework
 *
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2015 Bitrix
 */

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

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$index  = 0;
$fIndex = $arResult['RANDOM'];
echo '<pre>', print_r($arResult,true), '</pre>';
?>
    <div id="sale_location_container_<?= $fIndex ?>">
        <?php
        foreach ((array)$arResult['VALUE'] as $res):
            $name = $arParams['arUserField']['FIELD_NAME'];
            if ($arParams['arUserField']['MULTIPLE'] === 'Y') {
                $name = $arParams['arUserField']['~FIELD_NAME'] . '[' . $index . ']';
            }
            
            ?>
            <div class="fields">
            <?php
            /** @var \CMain $APPLICATION */
            global $APPLICATION;
            ob_start();
            $type = 'search';
            if ($type === 'search') {
                $APPLICATION->IncludeComponent('bitrix:sale.location.selector.search',
                                               '',
                                               [
                                                   'CACHE_TIME'                 => '36000000',
                                                   'CACHE_TYPE'                 => 'A',
                                                   'CODE'                       => '',
                                                   //"FILTER_BY_SITE" => "Y",
                                                   //"FILTER_SITE_ID" => "current",
                                                   'ID'                         => $res,
                                                   'INITIALIZE_BY_GLOBAL_EVENT' => '',
                                                   'INPUT_NAME'                 => $name,
                                                   'JS_CALLBACK'                => '',
                                                   'JS_CONTROL_GLOBAL_ID'       => '',
                                                   'PROVIDE_LINK_BY'            => 'id',
                                                   //"SHOW_DEFAULT_LOCATIONS" => "Y",
                                                   'SUPPRESS_ERRORS'            => 'N',
                                               ]);
            } ?>
            </div><?php
            $index++;
        endforeach;
        ?>
    </div>

<?php if ($arParams['arUserField']['EDIT_IN_LIST'] === 'Y' && $arParams['arUserField']['MULTIPLE'] === 'Y'
          && $arParams['SHOW_BUTTON'] !== 'N'): ?>
    <script type="text/javascript">
        if (!window.bxDateInputs) {
            var bxDateInputs = {};
        }
        bxSaleLocationInputs['<?=$fIndex?>'] = {
            'fieldName': '<?=$arParams['arUserField']['~FIELD_NAME']?>',
            'index':     '<?=$index?>'
        };
    </script>
    
    <input type="button"
           value="11<?= GetMessage('USER_TYPE_PROP_ADD') ?>"
           onclick="$('#hidden_<?=$fIndex?>').clone(true, true).appendTo('#sale_location_container_<?=$fIndex?>');">
    
    <div id="hidden_<?= $fIndex ?>" style="display:none;">
        <div class="fields">
            <?php /** @var \CMain $APPLICATION */
            global $APPLICATION;
            ob_start();
            $type = 'search';
            if ($type === 'search') {
                $APPLICATION->IncludeComponent('bitrix:sale.location.selector.search',
                                               '',
                                               [
                                                   'CACHE_TIME'                 => '36000000',
                                                   'CACHE_TYPE'                 => 'A',
                                                   'CODE'                       => '',
                                                   //"FILTER_BY_SITE" => "Y",
                                                   //"FILTER_SITE_ID" => "current",
                                                   'ID'                         => '',
                                                   'INITIALIZE_BY_GLOBAL_EVENT' => '',
                                                   'INPUT_NAME'                 => '#FIELD_NAME#',
                                                   'JS_CALLBACK'                => '',
                                                   'JS_CONTROL_GLOBAL_ID'       => '',
                                                   'PROVIDE_LINK_BY'            => 'id',
                                                   //"SHOW_DEFAULT_LOCATIONS" => "Y",
                                                   'SUPPRESS_ERRORS'            => 'N',
                                               ]);
            } ?>
        </div>
    </div>
<? endif; ?>