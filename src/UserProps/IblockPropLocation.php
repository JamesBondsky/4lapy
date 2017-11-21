<?php
/**
 * Created by PhpStorm.
 * User: Vampi
 * Date: 16.11.2017
 * Time: 18:22
 */

use Bitrix\Iblock;
use Bitrix\Main\Localization\Loc;

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
     * @throws \Bitrix\Main\LoaderException
     */
    public static function GetPropertyFieldHtmlMulty($arProperty, $value, $strHTMLControlName) : string
    {
        $originalControlName         = $strHTMLControlName['VALUE'];
        $strHTMLControlName['VALUE'] = str_replace('[]', '', $strHTMLControlName['VALUE']);
        
        ob_start();
        \Bitrix\Main\Loader::includeModule('sale');
        global $APPLICATION;
        ob_start(); ?>
        <script>
            <?php
            include_once $_SERVER['DOCUMENT_ROOT']
                         . '/local/components/adv/sale.location.selector.system/templates/.default/script.js';
            ?></script><?php
        $APPLICATION->IncludeComponent(//"adv:sale.location.selector.system",
            'bitrix:sale.location.selector.system',
            '',
            [
                'INPUT_NAME'          => $strHTMLControlName['VALUE'],
                'SELECTED_IN_REQUEST' => ['L' => $value['VALUE']],
                'PROP_LOCATION'       => 'Y',
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
				    var inputName = "input[name=\'' . $strHTMLControlName['VALUE'] . '_TMP[L]\']";
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
                            if (items.hasOwnProperty(index)){
                                val = items[index];
                                if(val > 0){
                                    html = "<input type=\'hidden\' name=\'' . $originalControlName . ' \' id=\''
                  . $strHTMLControlName['VALUE'] . '_"+val+"\' class=\'real_inputs\' value=\'"+val+"\'>";
                                    div_jq.append(html);
                                }
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
     * @throws \Bitrix\Main\LoaderException
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
}