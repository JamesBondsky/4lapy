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
$fIndex = $arResult['RANDOM']; ?>
<div id="sale_location_container_<?= $fIndex ?>">
    <?php if ($arParams['arUserField']['MULTIPLE'] === 'Y') {
        $name               = $arParams['arUserField']['FIELD_NAME'] . '[]';
        $replacedName       = str_replace(
            [
                '[',
                ']',
            ],
            '_',
            $name
        );
        $deferedControlName = 'defered_' . $replacedName;
        $tmpInputName       = $replacedName . '_TMP';
        $APPLICATION->IncludeComponent(
            'adv:sale.location.selector.system',
            '',
            [
                'CACHE_TYPE'               => 'N',
                'CACHE_TIME'               => '0',
                'INPUT_NAME'               => $tmpInputName,
                'SELECTED_IN_REQUEST'      => ['L' => $arResult['VALUE']],
                'PROP_LOCATION'            => 'Y',
                'JS_CONTROL_DEFERRED_INIT' => $deferedControlName,
                'JS_CONTROL_GLOBAL_ID'     => 'locationSelectors_' . $replacedName,
            ],
            false
        ); ?>
        <script type="text/javascript" data-skip-moving="true">
            if (!window.BX && top.BX) {
                window.BX = top.BX;
            }
            
            if (typeof window["LoadedLocationMultyScripts"] !== "boolean" || (typeof window["LoadedLocationMultyScripts"] === "boolean" && !window["LoadedLocationMultyScripts"])) {
                window["LoadedLocationMultyScripts"] = true;
                var bxInputdeliveryLocMultiStep3     = function () {
                    BX.loadScript("/local/templates/.default/components/bitrix/system.field.edit/sale_location/_script.js", function () {
                        window["LoadedLocationMultyScriptMain"] = true;
                        BX.ready(function () {
                            BX.onCustomEvent("deliveryGetRestrictionHtmlScriptsReady");
                            BX.locationsDeferred["<?=$deferedControlName?>"]();
                            initPropLocationRealVals("<?=$tmpInputName?>", "<?=$name?>");
                        });
                    });
                };
                
                var bxInputdeliveryLocMultiStep2 = function () {
                    BX.load([
                                "/bitrix/js/sale/core_ui_etc.js",
                                "/bitrix/js/sale/core_ui_autocomplete.js",
                                "/bitrix/js/sale/core_ui_itemtree.js"
                            ],
                            bxInputdeliveryLocMultiStep3
                    );
                };
                
                BX.loadScript("/bitrix/js/sale/core_ui_widget.js", bxInputdeliveryLocMultiStep2);
            }
            else {
                if (typeof window["LoadedLocationMultyScriptMain"] !== "boolean" || (typeof window["LoadedLocationMultyScriptMain"] === "boolean" && !window["LoadedLocationMultyScriptMain"])) {
                    BX.loadScript("/local/templates/.default/components/bitrix/system.field.edit/sale_location/_script.js", function () {
                        BX.ready(function () {
                            BX.onCustomEvent("deliveryGetRestrictionHtmlScriptsReady");
                            BX.locationsDeferred["<?=$deferedControlName?>"]();
                            initPropLocationRealVals("<?=$tmpInputName?>", "<?=$name?>");
                        });
                    });
                }
                else {
                    BX.ready(function () {
                        BX.onCustomEvent("deliveryGetRestrictionHtmlScriptsReady");
                        BX.locationsDeferred["<?=$deferedControlName?>"]();
                        initPropLocationRealVals("<?=$tmpInputName?>", "<?=$name?>");
                    });
                }
            }
            if (typeof initPropLocationRealVals !== "function") {
                function initPropLocationRealVals(name, realName) {
                    var el = document.querySelector("input[name=\'" + name + "[L]\']");
                    if (!el || typeof el === "undefined") {
                        el = top.document.querySelector("input[name=\'" + name + "[L]\']");
                    }
                    if (!!el) {
                        setPropLocationRealVals(el, realName);
                    }
                }
            }
            if (typeof setPropLocationRealVals !== "function") {
                function setPropLocationRealVals(el, realName) {
                    if (!!el) {
                        var firstVal = el.getAttribute("value");
                        if (firstVal.length > 0) {
                            var items    = firstVal.split(":");
                            var index, val;
                            var div      = el.closest("div");
                            var delItems = div.querySelectorAll("input.real_inputs");
                            if (delItems.length > 0) {
                                for (index in delItems) {
                                    if (delItems.hasOwnProperty(index)) {
                                        delItems[index].parentNode.removeChild(delItems[index]);
                                    }
                                }
                            }
                            if (items.length > 0) {
                                for (index in items) {
                                    if (items.hasOwnProperty(index)) {
                                        val = items[index];
                                        if (val > 0) {
                                            var newInput = document.createElement("input");
                                            newInput.setAttribute("name", realName);
                                            newInput.setAttribute("value", val);
                                            newInput.setAttribute("type", "hidden");
                                            newInput.className = "real_inputs";
                                            
                                            div.appendChild(newInput);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        </script>
        
        <!--suppress HtmlUnknownTarget -->
    <link rel="stylesheet" type="text/css" href="/bitrix/panel/main/adminstyles_fixed.css">
        <!--suppress HtmlUnknownTarget -->
    <link rel="stylesheet" type="text/css" href="/bitrix/panel/main/admin.css">
        <!--suppress HtmlUnknownTarget -->
    <link rel="stylesheet" type="text/css" href="/bitrix/panel/main/admin-public.css">
        <!--suppress HtmlUnknownTarget -->
    <link rel="stylesheet"
          type="text/css"
          href="/local/templates/.default/components/bitrix/system.field.edit/sale_location/_style.css">
    <?php } else {
    ?>
    <?php
    /** @var \CMain $APPLICATION */
    global $APPLICATION;
    foreach ((array)$arResult['VALUE'] as $res): ?>
    <div class="fields">
        <?php
        ob_start();
        $APPLICATION->IncludeComponent(
            'bitrix:sale.location.selector.search',
            '',
            [
                'CACHE_TIME'                 => '36000000',
                'CACHE_TYPE'                 => 'A',
                'CODE'                       => '',
                'ID'                         => $res,
                'INITIALIZE_BY_GLOBAL_EVENT' => '',
                'INPUT_NAME'                 => $name,
                'JS_CALLBACK'                => '',
                'JS_CONTROL_GLOBAL_ID'       => '',
                'PROVIDE_LINK_BY'            => 'id',
                'SUPPRESS_ERRORS'            => 'N',
            ]
        );
        ?>
    </div><?php
        $index++;
    endforeach;
        ?>
    <?php } ?>
</div>
