<?php
/**
 * Created by PhpStorm.
 * User: Vampi
 * Date: 16.11.2017
 * Time: 18:22
 */

use Bitrix\Iblock;
use Bitrix\Main\Localization\Loc;

\Bitrix\Main\EventManager::getInstance()->addEventHandler('iblock',
                                                          'OnIBlockPropertyBuildList',
                                                          [
                                                              'IblockPropLocation',
                                                              'GetUserTypeDescription',
                                                          ]);

Loc::loadMessages(__FILE__);

class IblockPropLocation
{
    const USER_TYPE = 'sale_location';
    
    /**
     * @return array
     */
    public static function GetUserTypeDescription() : array
    {
        return [
            'PROPERTY_TYPE'             => Iblock\PropertyTable::TYPE_STRING,
            'USER_TYPE'                 => self::USER_TYPE,
            'DESCRIPTION'               => Loc::getMessage('IBLOCK_PROP_SALE_LOCATION_DESC'),
            'GetPublicViewHTML'         => [
                __CLASS__,
                'GetPublicViewHTML',
            ],
            'GetPublicEditHTML'         => [
                __CLASS__,
                'GetPublicEditHTML',
            ],
            'GetAdminListViewHTML'      => [
                __CLASS__,
                'GetAdminListViewHTML',
            ],
            'GetPropertyFieldHtml'      => [
                __CLASS__,
                'GetPropertyFieldHtml',
            ],
            'GetPropertyFieldHtmlMulty' => [
                __CLASS__,
                'GetPropertyFieldHtmlMulty',
            ],
            'ConvertFromDB'             => [
                __CLASS__,
                'ConvertFromDB',
            ],
            'PrepareSettings'           => [
                __CLASS__,
                'PrepareSettings',
            ],
            //"GetSettingsHTML"      => [
            //    __CLASS__,
            //    "GetSettingsHTML",
            //],
        ];
    }
    
    /**
     * @param $arProperty
     * @param $value
     * @param $strHTMLControlName
     *
     * @return string
     */
    public static function GetPublicViewHTML($arProperty, $value, $strHTMLControlName) : string
    {
        if (!is_array($value['VALUE'])) {
            $value = static::ConvertFromDB($arProperty, $value);
        }
        if (!empty($value) && is_array($value)) {
            return '[' . $value['VALUE'] . '] ' . $value['TEXT'];
        }
        
        return '';
    }
    
    /**
     * @param $arProperty
     * @param $value
     * @param $strHTMLControlName
     *
     * @return string
     */
    public static function GetPublicEditHTML($arProperty, $value, $strHTMLControlName) : string
    {
        if (!is_array($value['VALUE'])) {
            $value = static::ConvertFromDB($arProperty, $value);
        }
        
        //$settings = static::PrepareSettings($arProperty);
        
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
                                               'ID'                         => $value['VALUE'],
                                               'INITIALIZE_BY_GLOBAL_EVENT' => '',
                                               'INPUT_NAME'                 => $strHTMLControlName['VALUE'],
                                               'JS_CALLBACK'                => '',
                                               'JS_CONTROL_GLOBAL_ID'       => '',
                                               'PROVIDE_LINK_BY'            => 'id',
                                               //"SHOW_DEFAULT_LOCATIONS" => "Y",
                                               'SUPPRESS_ERRORS'            => 'N',
                                           ]);
        } else {
            $APPLICATION->IncludeComponent('bitrix:sale.location.selector.steps',
                                           '',
                                           [
                                               'CACHE_TIME'                 => '36000000',
                                               'CACHE_TYPE'                 => 'A',
                                               'CODE'                       => '',
                                               'DISABLE_KEYBOARD_INPUT'     => 'N',
                                               //"FILTER_BY_SITE" => "Y",
                                               //"FILTER_SITE_ID" => "current",
                                               'ID'                         => $value['VALUE'],
                                               'INITIALIZE_BY_GLOBAL_EVENT' => '',
                                               'INPUT_NAME'                 => $strHTMLControlName['VALUE'],
                                               'JS_CALLBACK'                => '',
                                               'JS_CONTROL_GLOBAL_ID'       => '',
                                               'PRECACHE_LAST_LEVEL'        => 'N',
                                               'PRESELECT_TREE_TRUNK'       => 'N',
                                               'PROVIDE_LINK_BY'            => 'id',
                                               //"SHOW_DEFAULT_LOCATIONS" => "Y",
                                               'SUPPRESS_ERRORS'            => 'N',
                                           ]);
        }
        
        return ob_get_clean();
    }
    
    /**
     * @param $arProperty
     * @param $value
     * @param $strHTMLControlName
     *
     * @return string
     */
    public static function GetAdminListViewHTML($arProperty, $value, $strHTMLControlName) : string
    {
        if (!is_array($value['VALUE'])) {
            $value = static::ConvertFromDB($arProperty, $value);
        }
        if ($value) {
            return '[' . $value['VALUE'] . '] ' . $value['TEXT'];
        }
        
        return '&nbsp;';
    }
    
    /**
     * @param $arProperty
     * @param $value
     * @param $strHTMLControlName
     *
     * @return string
     */
    public static function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName) : string
    {
        //echo '<pre>', print_r($arProperty, true), '</pre>';
        //echo '<pre>', print_r($value, true), '</pre>';
        //echo '<pre>', print_r($strHTMLControlName, true), '</pre>';
        
        //$settings = static::PrepareSettings($arProperty);
        
        ob_start();
        ?>
        <div>
            <?php
            $type = 'search';
            /** @var \CMain $APPLICATION */
            global $APPLICATION;
            ob_start();
            if ($type === 'search') {
                $APPLICATION->IncludeComponent('bitrix:sale.location.selector.search',
                                               '',
                                               [
                                                   'CACHE_TIME'                 => '36000000',
                                                   'CACHE_TYPE'                 => 'A',
                                                   'CODE'                       => '',
                                                   //"FILTER_BY_SITE" => "Y",
                                                   //"FILTER_SITE_ID" => "current",
                                                   'ID'                         => $value['VALUE'],
                                                   'INITIALIZE_BY_GLOBAL_EVENT' => '',
                                                   'INPUT_NAME'                 => $strHTMLControlName['VALUE'],
                                                   'JS_CALLBACK'                => '',
                                                   'JS_CONTROL_GLOBAL_ID'       => '',
                                                   'PROVIDE_LINK_BY'            => 'id',
                                                   //"SHOW_DEFAULT_LOCATIONS" => "Y",
                                                   'SUPPRESS_ERRORS'            => 'N',
                                               ]);
            } else {
                $APPLICATION->IncludeComponent('bitrix:sale.location.selector.steps',
                                               '',
                                               [
                                                   'CACHE_TIME'                 => '36000000',
                                                   'CACHE_TYPE'                 => 'A',
                                                   'CODE'                       => '',
                                                   'DISABLE_KEYBOARD_INPUT'     => 'N',
                                                   //"FILTER_BY_SITE" => "Y",
                                                   //"FILTER_SITE_ID" => "current",
                                                   'ID'                         => $value['VALUE'],
                                                   'INITIALIZE_BY_GLOBAL_EVENT' => '',
                                                   'INPUT_NAME'                 => $strHTMLControlName['VALUE'],
                                                   'JS_CALLBACK'                => '',
                                                   'JS_CONTROL_GLOBAL_ID'       => '',
                                                   'PRECACHE_LAST_LEVEL'        => 'N',
                                                   'PRESELECT_TREE_TRUNK'       => 'N',
                                                   'PROVIDE_LINK_BY'            => 'id',
                                                   //"SHOW_DEFAULT_LOCATIONS" => "Y",
                                                   'SUPPRESS_ERRORS'            => 'N',
                                               ]);
            }
            
            ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * @param $arProperty
     * @param $value
     * @param $strHTMLControlName
     *
     * @return string
     */
    public static function GetPropertyFieldHtmlMulty($arProperty, $value, $strHTMLControlName) : string
    {
        //echo '<pre>', print_r($arProperty, true), '</pre>';
        //echo '<pre>', print_r($value, true), '</pre>';
        //echo '<pre>', print_r($strHTMLControlName, true), '</pre>';
        
        //$settings = static::PrepareSettings($arProperty);
        
        ob_start();
        //require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/sale/lib/delivery/inputs.php");
        \Bitrix\Main\Loader::includeModule('sale');
        global $APPLICATION;
        ob_start();
        //if (!is_array($value['VALUE'])) {
        //    $value['VALUE'] = [92];
        //} ?>
        <script>
            <?
            include_once $_SERVER['DOCUMENT_ROOT']
                         . '/local/components/adv/sale.location.selector.system/templates/.default/script.js';
            ?></script><?
        $APPLICATION->IncludeComponent(//"adv:sale.location.selector.system",
            'bitrix:sale.location.selector.system',
            '',
            [
                'INPUT_NAME'          => $strHTMLControlName['VALUE'],
                'SELECTED_IN_REQUEST' => ['L' => $value['VALUE']],
                'PROP_LOCATION' => 'Y'
            ],
            false);
        
        $result = ob_get_contents();
        $result = '
			<script type="text/javascript">
				var bxInputdeliveryLocMultiStep3 = function()
				{
					BX.loadScript("/local/components/adv/sale.location.selector.system/templates/.default/script.js", function(){
						BX.onCustomEvent("deliveryGetRestrictionHtmlScriptsReady");
					});
				};
    
				var bxInputdeliveryLocMultiStep2 = function()
				{
					BX.load([
						"/bitrix/js/sale/core_ui_etc.js",
						"/bitrix/js/sale/core_ui_autocomplete.js",
						"/bitrix/js/sale/core_ui_itemtree.js"
						],
						bxInputdeliveryLocMultiStep3
					);
				};
    
				BX.loadScript("/bitrix/js/sale/core_ui_widget.js", bxInputdeliveryLocMultiStep2);
    
				//at first we must load some scripts in the right order
				window["deliveryGetRestrictionHtmlScriptsLoadingStarted"] = true;
				
				BX.ready(function() {
				    var inputName = "input[name=\'' . $arHtmlControl['NAME'] . '_TMP[L]\']";
				    var inputElJq = $(inputName);
				    setPropLocationRealVals(inputElJq);
				    $("body").on("change", inputName, function(){
				        setPropLocationRealVals(this);
				    });
				});
				  
                function setPropLocationRealVals(el){
                    var firstVal = $(el).val();
                    if(firstVal.length > 0){
                        var items = $(el).val().split(":");
                        var index, val, html;
                        var div_jq = $(el).closest("div");
                        div_jq.find(".real_inputs").remove();
                        for(index in items){
                            val = items[index];
                            if(val > 0){
                                html = "<input type=\'hidden\' name=\'' . $originalControlName . ' \' id=\''
                  . $arHtmlControl['NAME'] . '_"+val+"\' class=\'real_inputs\' value=\'"+val+"\'>";
                                div_jq.append(html);
                            }
                        }
                    }
                }
    
			</script>
   
			<link rel="stylesheet" type="text/css" href="/bitrix/panel/main/adminstyles_fixed.css">
			<link rel="stylesheet" type="text/css" href="/bitrix/panel/main/admin.css">
			<link rel="stylesheet" type="text/css" href="/bitrix/panel/main/admin-public.css">
			<link rel="stylesheet" type="text/css" href="/bitrix/components/bitrix/sale.location.selector.system/templates/.default/style.css">
		' . $result;
        ob_end_clean();
        echo $result; ?>
        </div>
        <?php
        // }
        ?>
        <?php
        return ob_get_clean();
    }
    
    /**
     * @param $arProperty
     * @param $value
     *
     * @return array|bool
     */
    public static function ConvertFromDB($arProperty, $value)
    {
        $return = false;
        if (!is_array($value['VALUE'])) {
            \Bitrix\Main\Loader::includeModule('sale');
            
            $return = [
                'TEXT'  => \Bitrix\Sale\Location\Admin\LocationHelper::getLocationStringById($value['VALUE']),
                'VALUE' => $value['VALUE'],
            ];
            if ($value['DESCRIPTION']) {
                $return['DESCRIPTION'] = trim($value['DESCRIPTION']);
            }
        }
        
        return $return;
    }
    
    /**
     * @param $arProperty
     */
    public static function PrepareSettings($arProperty)
    {
        //$height = 0;
        //if (isset($arProperty["USER_TYPE_SETTINGS"]["height"])) {
        //    $height = (int)$arProperty["USER_TYPE_SETTINGS"]["height"];
        //}
        //if ($height <= 0) {
        //    $height = 200;
        //}
        //
        //return [
        //    "height" => $height,
        //];
    }
    
    //public static function GetSettingsHTML($arProperty, $strHTMLControlName, &$arPropertyFields)
    //{
    //    $arPropertyFields = [
    //        "HIDE" => [
    //            "ROW_COUNT",
    //            "COL_COUNT",
    //        ],
    //    ];
    //
    //    $height = 0;
    //    if (isset($arProperty["USER_TYPE_SETTINGS"]["height"])) {
    //        $height = (int)$arProperty["USER_TYPE_SETTINGS"]["height"];
    //    }
    //    if ($height <= 0) {
    //        $height = 200;
    //    }
    //
    //    return '
    //<tr valign="top">
    //	<td>' . Loc::getMessage("IBLOCK_PROP_HTML_SETTING_HEIGHT") . ':</td>
    //	<td><input type="text" size="5" name="' . $strHTMLControlName["NAME"] . '[height]" value="' . $height . '">px</td>
    //</tr>
    //';
    //}
}