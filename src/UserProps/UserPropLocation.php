<?php
/**
 * Created by PhpStorm.
 * User: Vampi
 * Date: 16.11.2017
 * Time: 18:22
 */

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class UserPropLocation extends CUserTypeInteger
{
    const USER_TYPE = 'sale_location';
    
    /**
     * @return array
     */
    public function GetUserTypeDescription() : array
    {
        return [
            'USER_TYPE_ID' => self::USER_TYPE,
            'CLASS_NAME'   => __CLASS__,
            'DESCRIPTION'  => Loc::getMessage('UserPropLocationMess'),
            'BASE_TYPE'    => 'int',
            //"EDIT_CALLBACK" => array(__CLASS__, 'GetPublicEdit'),
            //"VIEW_CALLBACK" => array(__CLASS__, 'GetPublicView'),
        ];
    }
    
    /**
     * @param $arUserField
     * @param $arHtmlControl
     *
     * @return string
     * @throws \Bitrix\Main\LoaderException
     */
    public function GetEditFormHTML($arUserField, $arHtmlControl) : string
    {
        //return '<pre>' . print_r($arHtmlControl, true) . '</pre>';
        $return = '&nbsp;';
        if ($arUserField['EDIT_IN_LIST'] === 'Y') {
            if ($arUserField['ENTITY_VALUE_ID'] < 1 && strlen($arUserField['SETTINGS']['DEFAULT_VALUE']) > 0) {
                $arHtmlControl['VALUE'] = $arUserField['SETTINGS']['DEFAULT_VALUE'];
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
                                                   'CACHE_TYPE'                 => 'N',
                                                   'CODE'                       => '',
                                                   //"FILTER_BY_SITE" => "Y",
                                                   //"FILTER_SITE_ID" => "current",
                                                   'ID'                         => $arHtmlControl['VALUE'],
                                                   'INITIALIZE_BY_GLOBAL_EVENT' => '',
                                                   'INPUT_NAME'                 => $arHtmlControl['NAME'],
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
                                                   'CACHE_TYPE'                 => 'N',
                                                   'CODE'                       => '',
                                                   'DISABLE_KEYBOARD_INPUT'     => 'N',
                                                   //"FILTER_BY_SITE" => "Y",
                                                   //"FILTER_SITE_ID" => "current",
                                                   'ID'                         => $arHtmlControl['VALUE'],
                                                   'INITIALIZE_BY_GLOBAL_EVENT' => '',
                                                   'INPUT_NAME'                 => $arHtmlControl['NAME'],
                                                   'JS_CALLBACK'                => '',
                                                   'JS_CONTROL_GLOBAL_ID'       => '',
                                                   'PRECACHE_LAST_LEVEL'        => 'N',
                                                   'PRESELECT_TREE_TRUNK'       => 'N',
                                                   'PROVIDE_LINK_BY'            => 'id',
                                                   //"SHOW_DEFAULT_LOCATIONS" => "Y",
                                                   'SUPPRESS_ERRORS'            => 'N',
                                               ]);
            }
            
            $return = '<div class="location_type_prop_html">' . ob_get_clean() . '</div>';
        } elseif (!empty($arHtmlControl['VALUE'])) {
            $class  = new static();
            $return = $class->GetAdminListViewHTML($arUserField, $arHtmlControl);
        }
        
        return $return;
        
    }
    
    /**
     * @param $arUserField
     * @param $arHtmlControl
     *
     * @return string
     * @throws \Bitrix\Main\LoaderException
     */
    public function GetEditFormHTMLMulty(
        /** @noinspection PhpUnusedParameterInspection */
        $arUserField,
        $arHtmlControl
    ) : string
    {
        //return '<pre>' . print_r($arHtmlControl, true) . '</pre>';
        $return = '&nbsp;';
        if ($arUserField['EDIT_IN_LIST'] === 'Y') {
            $originalControlName   = $arHtmlControl['NAME'];
            $arHtmlControl['NAME'] =
                str_replace([
                                '[',
                                ']',
                            ],
                            '_',
                            $arHtmlControl['NAME']);
            //$settings = static::PrepareSettings($arProperty);
            
            ob_start();
            \Bitrix\Main\Loader::includeModule('sale');
            global $APPLICATION;
            
            ob_start();
            
            $deferedControlName = 'DEFERED_LOAD_LOCATION_PROP_' . $arHtmlControl['NAME'];
            $tmpInputName       = $arHtmlControl['NAME'] . '_TMP';
            $APPLICATION->IncludeComponent('adv:sale.location.selector.system',
                                           '',
                                           [
                                               'CACHE_TYPE'               => 'N',
                                               'CACHE_TIME'               => '0',
                                               'INPUT_NAME'               => $tmpInputName,
                                               'SELECTED_IN_REQUEST'      => ['L' => $arHtmlControl['VALUE']],
                                               'PROP_LOCATION'            => 'Y',
                                               'JS_CONTROL_DEFERRED_INIT' => $deferedControlName,
                                           ],
                                           false);
            
            $result = ob_get_contents();
            $result = '<div class="location_type_prop_multi_html">
			<script type="text/javascript" data-skip-moving="true">
				var bxInputdeliveryLocMultiStep3 = function()
				{
                    BX.loadScript("/local/templates/.default/components/bitrix/system.field.edit/sale_location/_script.js", function(){
						BX.ready(function() {
						    BX.onCustomEvent("deliveryGetRestrictionHtmlScriptsReady");
						    BX.locationsDeferred["' . $deferedControlName . '"]();
						});
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
				//window["deliveryGetRestrictionHtmlScriptsLoadingStarted"] = true;
				  
                if(typeof initPropLocationRealVals !== "function"){
                    function initPropLocationRealVals(){
                        var inputName = "input[name=\'' . $tmpInputName . '[L]\']";
                        var inputElJq = $(inputName);
                        setPropLocationRealVals(inputElJq);
                        $("body").on("change", inputName, function(){
                            setPropLocationRealVals(this);
                        });
                    }
                }
                if(typeof setPropLocationRealVals !== "function"){
                    function setPropLocationRealVals(el){
                        if($(el).length > 0){
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
                                            html = "<input type=\'hidden\' name=\'' . $originalControlName . ' \'" +
                                             " class=\'real_inputs\' value=\'"+val+"\'>";
                                            div_jq.append(html);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                
                BX.ready(function() {
				   initPropLocationRealVals();
				});
			</script>
   
			<link rel="stylesheet" type="text/css" href="/bitrix/panel/main/adminstyles_fixed.css">
			<link rel="stylesheet" type="text/css" href="/bitrix/panel/main/admin.css">
			<link rel="stylesheet" type="text/css" href="/bitrix/panel/main/admin-public.css">
			<link rel="stylesheet" type="text/css" href="/local/templates/.default/components/bitrix/system.field.edit/sale_location/_style.css">
		' . $result . '</div>';
            ob_end_clean();
            echo $result;
            
            $return = ob_get_clean();
        } elseif (!empty($arHtmlControl['VALUE'])) {
            $class  = new static();
            $return = $class->GetAdminListViewHTMLMulty($arUserField, $arHtmlControl);
        }
        
        return $return;
    }
    
    /**
     * @param $arUserField
     * @param $arHtmlControl
     *
     * @return string
     */
    public function GetFilterHTML($arUserField, $arHtmlControl) : string
    {
        /** @var \CMain $APPLICATION */
        global $APPLICATION;
        ob_start();
        $type = 'search';
        if ($type === 'search') {
            $APPLICATION->IncludeComponent('bitrix:sale.location.selector.search',
                                           '',
                                           [
                                               'CACHE_TIME'                 => '36000000',
                                               'CACHE_TYPE'                 => 'N',
                                               'CODE'                       => '',
                                               //"FILTER_BY_SITE" => "Y",
                                               //"FILTER_SITE_ID" => "current",
                                               'ID'                         => $arHtmlControl['VALUE'],
                                               'INITIALIZE_BY_GLOBAL_EVENT' => '',
                                               'INPUT_NAME'                 => $arHtmlControl['NAME'],
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
                                               'CACHE_TYPE'                 => 'N',
                                               'CODE'                       => '',
                                               'DISABLE_KEYBOARD_INPUT'     => 'N',
                                               //"FILTER_BY_SITE" => "Y",
                                               //"FILTER_SITE_ID" => "current",
                                               'ID'                         => $arHtmlControl['VALUE'],
                                               'INITIALIZE_BY_GLOBAL_EVENT' => '',
                                               'INPUT_NAME'                 => $arHtmlControl['NAME'],
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
    
    //Этот метод вызывается для показа значений в списке
    
    /**
     * @param $arUserField
     * @param $arHtmlControl
     *
     * @return string
     * @throws \Bitrix\Main\LoaderException
     */
    public function GetAdminListViewHTML($arUserField, $arHtmlControl) : string
    {
        if (!empty($arHtmlControl['VALUE']) && (int)$arHtmlControl['VALUE'] > 0) {
            Loader::includeModule('sale');
            
            return '[' . $arHtmlControl['VALUE'] . ']'
                   . \Bitrix\Sale\Location\Admin\LocationHelper::getLocationStringById($arHtmlControl['VALUE']);
        }
        
        return '&nbsp;';
    }
    
    /**
     * @param $arUserField
     * @param $arHtmlControl
     *
     * @return string
     * @throws \Bitrix\Main\LoaderException
     */
    public function GetAdminListViewHTMLMulty(
        /** @noinspection PhpUnusedParameterInspection */
        $arUserField,
        $arHtmlControl
    ) : string
    {
        if (!empty($arHtmlControl['VALUE'])) {
            Loader::includeModule('sale');
            $arPrint = [];
            if (is_array($arHtmlControl['VALUE']) && !empty($arHtmlControl['VALUE'])) {
                foreach ($arHtmlControl['VALUE'] as $val) {
                    if (!empty($val) && (int)$val > 0) {
                        $arPrint[] =
                            '[' . $val . ']' . \Bitrix\Sale\Location\Admin\LocationHelper::getLocationStringById($val);
                    }
                }
            }
            
            return implode(' / ', $arPrint);
        }
        
        return '&nbsp;';
    }
    
    /**
     * @param $arUserField
     * @param $arHtmlControl
     *
     * @return string
     * @throws \Bitrix\Main\LoaderException
     */
    public function GetAdminListEditHTML($arUserField, $arHtmlControl) : string
    {
        $class = new static();
        
        return $class->GetEditFormHTML($arUserField, $arHtmlControl);
    }
    
    /**
     * @param $arUserField
     * @param $arHtmlControl
     *
     * @return mixed
     * @throws \Bitrix\Main\LoaderException
     */
    public function GetAdminListEditHTMLMulty($arUserField, $arHtmlControl) : string
    {
        $class = new static();
        
        return $class->GetEditFormHTMLMulty($arUserField, $arHtmlControl);
    }
    
    /**
     * @param $arUserField
     *
     * @return string
     * @throws \Bitrix\Main\LoaderException
     */
    public function OnSearchIndex($arUserField) : string
    {
        $class = new static();
        if (is_array($arUserField['VALUE'])) {
            return $class->GetAdminListViewHTMLMulty($arUserField, ['VALUE' => $arUserField['VALUE']]);
        }
        
        return $class->GetAdminListViewHTML($arUserField, ['VALUE' => $arUserField['VALUE']]);
    }
}