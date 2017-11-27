<?php

namespace FourPaws\UserProps;

use Bitrix\Main\{
    Loader, LoaderException, Localization\Loc, UserField\TypeBase
};
use Bitrix\Sale\Location\Admin\LocationHelper;

Loc::loadMessages(__FILE__);

class UserPropLocation extends TypeBase
{
    const USER_TYPE = 'sale_location';
    
    /**
     * @return array
     */
    public static function getUserTypeDescription() : array
    {
        return [
            'USER_TYPE_ID' => self::USER_TYPE,
            'CLASS_NAME'   => __CLASS__,
            'DESCRIPTION'  => Loc::getMessage('UserPropLocationMess'),
            'BASE_TYPE'    => \CUserTypeManager::BASE_TYPE_INT,
            //"EDIT_CALLBACK" => array(__CLASS__, 'GetPublicEdit'),
            //"VIEW_CALLBACK" => array(__CLASS__, 'GetPublicView'),
        ];
    }
    
    /**
     * Return internal type for storing url_preview user type values
     *
     * @param array $userField Array containing parameters of the user field.
     *
     * @return string
     */
    public static function getDBColumnType(
        /** @noinspection PhpUnusedParameterInspection */
        $userField
    )
    {
        global $DB;
        switch (strtolower($DB->type)) {
            case 'oracle':
                return 'number(18)';
            case 'mssql':
                return 'int';
            case 'mysql':
            default:
                return 'int(11)';
        }
    }
    
    /**
     * @param array $userField
     *
     * @return array
     */
    public static function prepareSettings(
        /** @noinspection PhpUnusedParameterInspection */
        $userField
    ) : array
    {
        return [
            'DEFAULT_VALUE' => (int)$userField['SETTINGS']['DEFAULT_VALUE']
                               > 0 ? (int)$userField['SETTINGS']['DEFAULT_VALUE'] : '',
        ];
    }
    
    /**
     * @param array $userField Array containing parameters of the user field.
     * @param       $htmlControl
     * @param       $varsFromForm
     *
     * @return string
     */
    public static function getSettingsHTML(
        /** @noinspection PhpUnusedParameterInspection */
        $userField,
        $htmlControl,
        $varsFromForm
    ) : string
    {
        $result = '';
        $value  = '';
        if ($varsFromForm) {
            $value = $GLOBALS[$htmlControl['NAME']]['DEFAULT_VALUE'];
        } elseif (\is_array($userField)) {
            $value = $userField['SETTINGS']['DEFAULT_VALUE'];
        } elseif ((int)$value > 0) {
            $value = (int)$value;
        }
        $replacedName = str_replace([
                                        '[',
                                        ']',
                                    ],
                                    '_',
                                    $htmlControl['NAME']);
        ob_start();
        $type = 'search';
        global $APPLICATION;
        if ($type === 'search') {
            $APPLICATION->IncludeComponent('bitrix:sale.location.selector.search',
                                           '',
                                           [
                                               'CACHE_TIME'                 => '36000000',
                                               'CACHE_TYPE'                 => 'N',
                                               'CODE'                       => '',
                                               //"FILTER_BY_SITE" => "Y",
                                               //"FILTER_SITE_ID" => "current",
                                               'ID'                         => $value,
                                               'INITIALIZE_BY_GLOBAL_EVENT' => '',
                                               'INPUT_NAME'                 => $htmlControl['NAME'],
                                               'JS_CALLBACK'                => '',
                                               //'JS_CONTROL_GLOBAL_ID'       => 'locationSelectors_' . $replacedName,
                                               //'JS_CONTROL_DEFERRED_INIT'       => 'defered_'.$replacedName,
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
                                               'ID'                         => $htmlControl['VALUE'],
                                               'INITIALIZE_BY_GLOBAL_EVENT' => '',
                                               'INPUT_NAME'                 => $htmlControl['NAME'],
                                               'JS_CALLBACK'                => '',
                                               'JS_CONTROL_GLOBAL_ID'       => 'locationSelectors_' . $replacedName,
                                               //'JS_CONTROL_DEFERRED_INIT'       => 'defered_'.$replacedName,
                                               'PRECACHE_LAST_LEVEL'        => 'N',
                                               'PRESELECT_TREE_TRUNK'       => 'N',
                                               'PROVIDE_LINK_BY'            => 'id',
                                               //"SHOW_DEFAULT_LOCATIONS" => "Y",
                                               'SUPPRESS_ERRORS'            => 'N',
                                           ]);
        }
    
        $return = ob_get_clean();
        $result .= '
		<tr>
			<td>' . GetMessage('USER_TYPE_INTEGER_DEFAULT_VALUE') . ':</td>
			<td>
				' . $return . '
			</td>
		</tr>
		';
        
        return $result;
    }
    
    /**
     * @param $userField
     * @param $htmlControl
     *
     * @return string
     * @throws LoaderException
     */
    public static function getEditFormHTML($userField, $htmlControl) : string
    {
        //$fieldName = static::getFieldName($userField, []);
        //    $value = static::getFieldValue($userField, []);
        //return '<pre>' . print_r($htmlControl, true) . '</pre>';
        $return = '&nbsp;';
        //$htmlControl['NAME'] = $userField['FIELD_CODE'];
        $replacedName = str_replace([
                                        '[',
                                        ']',
                                    ],
                                    '_',
                                    $htmlControl['NAME']);
        if ($userField['EDIT_IN_LIST'] === 'Y') {
            if ($userField['ENTITY_VALUE_ID'] < 1 && !empty($userField['SETTINGS']['DEFAULT_VALUE'])) {
                $htmlControl['VALUE'] = $userField['SETTINGS']['DEFAULT_VALUE'];
            }
            /** @var \CMain $APPLICATION */
            global $APPLICATION;
            ob_start();
            $type               = 'search';
            $deferedControlName = 'defered_' . $replacedName;
            $globalControlName  = 'locationSelectors_' . $replacedName;
            if ($type === 'search') {
                $APPLICATION->IncludeComponent('bitrix:sale.location.selector.search',
                                               '',
                                               [
                                                   'CACHE_TIME'                 => '36000000',
                                                   'CACHE_TYPE'                 => 'N',
                                                   'CODE'                       => '',
                                                   //"FILTER_BY_SITE" => "Y",
                                                   //"FILTER_SITE_ID" => "current",
                                                   'ID'                         => $htmlControl['VALUE'],
                                                   'INITIALIZE_BY_GLOBAL_EVENT' => '',
                                                   'INPUT_NAME'                 => $htmlControl['NAME'],
                                                   'JS_CALLBACK'                => '',
                                                   'JS_CONTROL_GLOBAL_ID'       => $globalControlName,
                                                   'JS_CONTROL_DEFERRED_INIT'   => $deferedControlName,
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
                                                   'ID'                         => $htmlControl['VALUE'],
                                                   'INITIALIZE_BY_GLOBAL_EVENT' => '',
                                                   'INPUT_NAME'                 => $htmlControl['NAME'],
                                                   'JS_CALLBACK'                => '',
                                                   'JS_CONTROL_GLOBAL_ID'       => $globalControlName,
                                                   'JS_CONTROL_DEFERRED_INIT'   => $deferedControlName,
                                                   'PRECACHE_LAST_LEVEL'        => 'N',
                                                   'PRESELECT_TREE_TRUNK'       => 'N',
                                                   'PROVIDE_LINK_BY'            => 'id',
                                                   //"SHOW_DEFAULT_LOCATIONS" => "Y",
                                                   'SUPPRESS_ERRORS'            => 'N',
                                               ]);
            } ?>
            <script>
                if (!window.BX && top.BX) {
                    window.BX = top.BX;
                }
                BX.loadScript("/bitrix/components/bitrix/sale.location.selector.search/templates/.default/script.js", function () {
                    BX.ready(function () {
                        BX.locationsDeferred["<?=$deferedControlName?>"]();
                    });
                });
            </script>
            <? $return = '<div class="location_type_prop_html">' . ob_get_clean() . '</div>';
        } elseif (!empty($htmlControl['VALUE'])) {
            //$class  = new static();
            $return = static::getAdminListViewHTML($userField, $htmlControl);
        }
        
        return $return;
        
    }
    
    /**
     * @param $userField
     * @param $htmlControl
     *
     * @return string
     * @throws \Bitrix\Main\LoaderException
     */
    public static function getEditFormHTMLMulty(
        /** @noinspection PhpUnusedParameterInspection */
        $userField,
        $htmlControl
    ) : string
    {
        //return '<pre>' . print_r($htmlControl, true) . '</pre>';
        $return = '&nbsp;';
        if ($userField['EDIT_IN_LIST'] === 'Y') {
            $replacedName = str_replace([
                                            '[',
                                            ']',
                                        ],
                                        '_',
                                        $htmlControl['NAME']);
            //$settings = static::PrepareSettings($arProperty);
            
            ob_start();
            //echo '<pre>', print_r($htmlControl,true), '</pre>';
            Loader::includeModule('sale');
            global $APPLICATION;
            
            ob_start();
            
            $deferedControlName = 'defered_' . $replacedName;
            $tmpInputName       = $replacedName . '_TMP';
            $APPLICATION->IncludeComponent('adv:sale.location.selector.system',
                                           '',
                                           [
                                               'CACHE_TYPE'               => 'N',
                                               'CACHE_TIME'               => '0',
                                               'INPUT_NAME'               => $tmpInputName,
                                               'SELECTED_IN_REQUEST'      => ['L' => $htmlControl['VALUE']],
                                               'PROP_LOCATION'            => 'Y',
                                               'JS_CONTROL_DEFERRED_INIT' => $deferedControlName,
                                               'JS_CONTROL_GLOBAL_ID'     => 'locationSelectors_' . $replacedName,
                                           ],
                                           false);
            
            $result = ob_get_contents();
            $result = '<div class="location_type_prop_multi_html" data-realInputName="' . $htmlControl['NAME'] . '">
			<script type="text/javascript" data-skip-moving="true">
                if (!window.BX && top.BX) {
                    window.BX = top.BX;
                }
               
			    if(typeof window["LoadedLocationMultyScripts"] !== "boolean" || (typeof window["LoadedLocationMultyScripts"] === "boolean" && !window["LoadedLocationMultyScripts"])){
			        window["LoadedLocationMultyScripts"] = true;
                    var bxInputdeliveryLocMultiStep3 = function()
                    {
                        BX.loadScript("/local/templates/.default/components/bitrix/system.field.edit/sale_location/_script.js", function(){
                            window["LoadedLocationMultyScriptMain"] = true;
                            BX.ready(function() {
                                BX.onCustomEvent("deliveryGetRestrictionHtmlScriptsReady");
                                BX.locationsDeferred["' . $deferedControlName . '"]();
                                initPropLocationRealVals("' . $tmpInputName . '", "' . $htmlControl['NAME'] . '");
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
				}
				else{
			        if(typeof window["LoadedLocationMultyScriptMain"] !== "boolean" || (typeof window["LoadedLocationMultyScriptMain"] === "boolean" && !window["LoadedLocationMultyScriptMain"])){
			            BX.loadScript("/local/templates/.default/components/bitrix/system.field.edit/sale_location/_script.js", function(){
			                BX.ready(function() {
                                BX.onCustomEvent("deliveryGetRestrictionHtmlScriptsReady");
                                BX.locationsDeferred["' . $deferedControlName . '"]();
                                initPropLocationRealVals("' . $tmpInputName . '", "' . $htmlControl['NAME'] . '");
                            });
			            });
			        }
			        else{
			            BX.ready(function() {
                            BX.onCustomEvent("deliveryGetRestrictionHtmlScriptsReady");
                            BX.locationsDeferred["' . $deferedControlName . '"]();
                            initPropLocationRealVals("' . $tmpInputName . '", "' . $htmlControl['NAME'] . '");
                        });
			        }
				}
				if(typeof initPropLocationRealVals !== "function"){
                    function initPropLocationRealVals(name, realName){
                        var el = document.querySelector( "input[name=\'"+name+"[L]\']" );
                        if(!el || typeof el === "undefined"){
                            el = top.document.querySelector( "input[name=\'"+name+"[L]\']" );
                        }
                        if(!!el) {
                            setPropLocationRealVals(el, realName);
                            //setPropLocationRealVals($("input[name=\'"+name+"[L]\']"), realName);
                        }
                    }
                }
                if(typeof setPropLocationRealVals !== "function"){
                    function setPropLocationRealVals(el, realName){
                        if(!!el){
                            var firstVal = el.getAttribute("value");
                            if(firstVal.length > 0){
                                var items = firstVal.split(":");
                                var index, val;
                                var div = el.closest("div");
                                var delItems = div.querySelectorAll("input.real_inputs");
                                if(delItems.length>0){
                                    for(index in delItems){
                                        if(delItems.hasOwnProperty(index)){
                                            delItems[index].parentNode.removeChild(delItems[index]);
                                        }
                                    }
                                }
                                if(items.length > 0){
                                    for(index in items){
                                        if (items.hasOwnProperty(index)){
                                            val = items[index];
                                            if(val > 0){
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
			<link rel="stylesheet" type="text/css" href="/local/templates/.default/components/bitrix/system.field.edit/sale_location/_style.css">
		' . $result . '</div>';
            ob_end_clean();
            echo $result;
            
            $return = ob_get_clean();
        } elseif (!empty($htmlControl['VALUE'])) {
            //$class  = new static();
            $return = static::getAdminListViewHTMLMulty($userField, $htmlControl);
        }
        
        return $return;
    }
    
    /**
     * @param $userField
     * @param $htmlControl
     *
     * @return string
     */
    public static function getFilterHTML(
        /** @noinspection PhpUnusedParameterInspection */
        $userField,
        $htmlControl
    ) : string
    {
        $replacedName = str_replace([
                                        '[',
                                        ']',
                                    ],
                                    '_',
                                    $htmlControl['NAME']);
        
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
                                               'ID'                         => $htmlControl['VALUE'],
                                               'INITIALIZE_BY_GLOBAL_EVENT' => '',
                                               'INPUT_NAME'                 => $htmlControl['NAME'],
                                               'JS_CALLBACK'                => '',
                                               'JS_CONTROL_GLOBAL_ID'       => 'locationSelectors_' . $replacedName,
                                               //'JS_CONTROL_DEFERRED_INIT'       => 'defered_'.$replacedName,
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
                                               'ID'                         => $htmlControl['VALUE'],
                                               'INITIALIZE_BY_GLOBAL_EVENT' => '',
                                               'INPUT_NAME'                 => $htmlControl['NAME'],
                                               'JS_CALLBACK'                => '',
                                               'JS_CONTROL_GLOBAL_ID'       => 'locationSelectors_' . $replacedName,
                                               //'JS_CONTROL_DEFERRED_INIT'       => 'defered_'.$replacedName,
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
    /** @noinspection PhpUnusedParameterInspection */
    
    /**
     * @param $userField
     * @param $htmlControl
     *
     * @return string
     * @throws \Bitrix\Main\LoaderException
     */
    public static function getAdminListViewHTML(
        /** @noinspection PhpUnusedParameterInspection */
        $userField,
        $htmlControl
    ) : string
    {
        if (!empty($htmlControl['VALUE']) && (int)$htmlControl['VALUE'] > 0) {
            Loader::includeModule('sale');
            
            return '[' . $htmlControl['VALUE'] . ']' . LocationHelper::getLocationStringById($htmlControl['VALUE']);
        }
        
        return '&nbsp;';
    }
    
    /**
     * @param $userField
     * @param $htmlControl
     *
     * @return string
     * @throws \Bitrix\Main\LoaderException
     */
    public static function getAdminListViewHTMLMulty(
        /** @noinspection PhpUnusedParameterInspection */
        $userField,
        $htmlControl
    ) : string
    {
        if (!empty($htmlControl['VALUE'])) {
            Loader::includeModule('sale');
            $arPrint = [];
            if (\is_array($htmlControl['VALUE']) && !empty($htmlControl['VALUE'])) {
                foreach ($htmlControl['VALUE'] as $val) {
                    if (!empty($val) && (int)$val > 0) {
                        $arPrint[] = '[' . $val . ']' . LocationHelper::getLocationStringById($val);
                    }
                }
            }
            
            return implode(' / ', $arPrint);
        }
        
        return '&nbsp;';
    }
    
    /**
     * @param $userField
     * @param $htmlControl
     *
     * @return string
     * @throws \Bitrix\Main\LoaderException
     */
    public static function getAdminListEditHTML($userField, $htmlControl) : string
    {
        //$class = new static();
        
        return static::getEditFormHTML($userField, $htmlControl);
    }
    
    /**
     * @param $userField
     * @param $htmlControl
     *
     * @return mixed
     * @throws \Bitrix\Main\LoaderException
     */
    public static function getAdminListEditHTMLMulty($userField, $htmlControl) : string
    {
        //return '<pre>'. print_r($userField,true). '</pre>';
        //$class = new static();
        
        return static::getEditFormHTMLMulty($userField, $htmlControl);
    }
    
    /**
     * @param $userField
     *
     * @return string
     * @throws \Bitrix\Main\LoaderException
     */
    public static function onSearchIndex($userField) : string
    {
        //$class = new static();
        if (\is_array($userField['VALUE'])) {
            return static::getAdminListViewHTMLMulty($userField, ['VALUE' => $userField['VALUE']]);
        }
        
        return static::getAdminListViewHTML($userField, ['VALUE' => $userField['VALUE']]);
    }
    
    /**
     * @param array $userField Array containing parameters of the user field.
     * @param array $params
     * @param array $setting
     *
     * @return string
     */
    //public static function getPublicViewHTML($userField, $id, $params = "", $settings = array())
    //{
    //    return UrlPreview::showView($userField, $params, $cacheTag);
    //}
    /** @noinspection ArrayTypeOfParameterByDefaultValueInspection */
    
    /**
     * @param       $arUserField
     * @param array $arAdditionalParameters
     *
     * @return string
     */
    //public static function getPublicEdit($arUserField, $arAdditionalParameters = array()) : string
    //{
    //    $fieldName = static::getFieldName($arUserField, $arAdditionalParameters);
    //    $value = static::getFieldValue($arUserField, $arAdditionalParameters);
    //
    //    $html = '';
    //
    //    foreach($value as $res)
    //    {
    //        $attrList = array();
    //
    //        if($arUserField["EDIT_IN_LIST"] != "Y")
    //        {
    //            $attrList['disabled'] = 'disabled';
    //        }
    //
    //        if($arUserField["SETTINGS"]["SIZE"] > 0)
    //        {
    //            $attrList['size'] = intval($arUserField["SETTINGS"]["SIZE"]);
    //        }
    //
    //        if(array_key_exists('attribute', $arAdditionalParameters))
    //        {
    //            $attrList = array_merge($attrList, $arAdditionalParameters['attribute']);
    //        }
    //
    //        if(isset($attrList['class']) && is_array($attrList['class']))
    //        {
    //            $attrList['class'] = implode(' ', $attrList['class']);
    //        }
    //
    //        $attrList['class'] = static::getHelper()->getCssClassName().(isset($attrList['class']) ? ' '.$attrList['class'] : '');
    //
    //        $attrList['name'] = $fieldName;
    //
    //        $attrList['type'] = 'text';
    //        $attrList['value'] = $res;
    //        $attrList['tabindex'] = '0';
    //
    //        $html .= static::getHelper()->wrapSingleField('<input '.static::buildTagAttributes($attrList).'/>');
    //    }
    //
    //    if($arUserField["MULTIPLE"] == "Y" && $arAdditionalParameters["SHOW_BUTTON"] != "N")
    //    {
    //        $html .= static::getHelper()->getCloneButton($fieldName);
    //    }
    //
    //    static::initDisplay();
    //
    //    return static::getHelper()->wrapDisplayResult($html);
    //}
    
    /**
     * Checks for current user's access to $value.
     *
     * @param array $userField Array containing parameters of the user field.
     * @param int   $value
     *
     * @return array
     */
    //public static function checkfields($userField, $value)
    //{
    //    $value = (int)$value;
    //    $result = array();
    //    if($value === 0)
    //        return $result;
    //
    //    $metadata = UrlMetadataTable::getById($value)->fetch();
    //    if(!is_array($metadata))
    //    {
    //        $result[] = array(
    //            "id" => $userField["FIELD_NAME"],
    //            "text" => GetMessage("MAIN_URL_PREVIEW_VALUE_NOT_FOUND")
    //        );
    //    }
    //    else if($metadata['TYPE'] === UrlMetadataTable::TYPE_DYNAMIC
    //            && !UrlPreview::checkDynamicPreviewAccess($metadata['URL']))
    //    {
    //        $result[] = array(
    //            "id" => $userField["FIELD_NAME"],
    //            "text" => GetMessage("MAIN_URL_PREVIEW_VALUE_NO_ACCESS",
    //                                 array('#URL#' => $metadata['URL'])
    //            )
    //        );
    //    }
    //
    //    return $result;
    //}
    
    /**
     * Hook executed before saving url_preview user type value. Checks and removes signature of the $value.
     * If signature is correct, checks current user's access to $value.
     *
     * @param array  $userField Array containing parameters of the user field.
     * @param string $value     Signed value of the user field.
     *
     * @return int Unsigned value of the user field, or null in case of errors.
     */
    //public static function onBeforeSave($userField, $value)
    //{
    //    $imageUrl = null;
    //    if(strpos($value, ';') !== false)
    //    {
    //        list($value, $imageUrl) = explode(';', $value);
    //    }
    //
    //    $signer = new Signer();
    //    try
    //    {
    //        $value = $signer->unsign($value, UrlPreview::SIGN_SALT);
    //    }
    //    catch (SystemException $e)
    //    {
    //        return null;
    //    }
    //    $metadata = UrlMetadataTable::getById($value)->fetch();
    //    if(!is_array($metadata))
    //        return null;
    //
    //    if($metadata['TYPE'] === UrlMetadataTable::TYPE_STATIC)
    //    {
    //        if($imageUrl && is_array($metadata['EXTRA']['IMAGES']) && in_array($imageUrl, $metadata['EXTRA']['IMAGES']))
    //        {
    //            UrlPreview::setMetadataImage((int)$value, $imageUrl);
    //        }
    //        return $value;
    //    }
    //    else if($metadata['TYPE'] === UrlMetadataTable::TYPE_DYNAMIC
    //            && UrlPreview::checkDynamicPreviewAccess($metadata['URL']))
    //    {
    //        return $value;
    //    }
    //
    //    return null;
    //}
    
    /**
     * Hook executed after fetching value of the user type. Signs returned value.
     *
     * @param array $userField Array containing parameters of the user field.
     * @param array $value     Unsigned value of the user field.
     *
     * @return string Signed value of the user field.
     */
    //public static function onAfterFetch($userField, $value)
    //{
    //    $result = null;
    //    if(isset($value['VALUE']))
    //    {
    //        $result = UrlPreview::sign($value['VALUE']);
    //    }
    //
    //    return $result;
    //}
}