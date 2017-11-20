<?php
/**
 * Created by PhpStorm.
 * User: Vampi
 * Date: 16.11.2017
 * Time: 18:22
 */

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

\Bitrix\Main\EventManager::getInstance()->addEventHandler('main',
                                                          'OnUserTypeBuildList',
                                                          [
                                                              'UserPropLocation',
                                                              'GetUserTypeDescription',
                                                          ]);

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
     */
    public function GetEditFormHTML($arUserField, $arHtmlControl) : string
    {
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
                                               'CACHE_TYPE'                 => 'A',
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
                                               'CACHE_TYPE'                 => 'A',
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
    
    /**
     * @param $arUserField
     * @param $arHtmlControl
     *
     * @return string
     * @throws \Bitrix\Main\LoaderException
     */
    public function GetEditFormHTMLMulty($arUserField, $arHtmlControl) : string
    {
        $originalControlName   = $arHtmlControl['NAME'];
        $arHtmlControl['NAME'] = str_replace('[]', '', $arHtmlControl['NAME']);
        //$settings = static::PrepareSettings($arProperty);
        
        ob_start();
        //require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/sale/lib/delivery/inputs.php");
        \Bitrix\Main\Loader::includeModule('sale');
        global $APPLICATION;
        ob_start(); ?>
        <script>
            <?php
            include_once $_SERVER['DOCUMENT_ROOT']
                         . '/local/components/adv/sale.location.selector.system/templates/.default/script.js'; ?></script><?php
        $APPLICATION->IncludeComponent(//"adv:sale.location.selector.system",
            'bitrix:sale.location.selector.system',
            '',
            [
                'INPUT_NAME'          => $arHtmlControl['NAME'] . '_TMP',
                'SELECTED_IN_REQUEST' => ['L' => $arHtmlControl['VALUE']],
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
				//window["deliveryGetRestrictionHtmlScriptsLoadingStarted"] = true;
				
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
                            if (items.hasOwnProperty(index)){
                                val = items[index];
                                if(val > 0){
                                    html = "<input type=\'hidden\' name=\'' . $originalControlName . ' \' id=\''
                                        . $arHtmlControl['NAME'] . '_"+val+"\' class=\'real_inputs\' value=\'"+val+"\'>";
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
        </div>
        <?php
        // }
        ?>
        <?php
        return ob_get_clean();
    }
    
    /**
     * @param $arUserField
     * @param $arHtmlControl
     *
     * @return string
     */
    public function GetFilterHTML($arUserField, $arHtmlControl) : string
    {
        //if($GLOBALS['USER']->IsAdmin()){
        //}
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
                                               'CACHE_TYPE'                 => 'A',
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
        if (strlen($arHtmlControl['VALUE']) > 0) {
            Loader::includeModule('sale');
            
            return \Bitrix\Sale\Location\Admin\LocationHelper::getLocationStringById($arHtmlControl['VALUE']);
        }
        
        return ' ';
    }
    
    /**
     * @param $arUserField
     * @param $arHtmlControl
     *
     * @return string
     * @throws \Bitrix\Main\LoaderException
     */
    public function GetAdminListViewHTMLMulty($arUserField, $arHtmlControl) : string
    {
        if (!empty($arHtmlControl['VALUE'])) {
            Loader::includeModule('sale');
            $arPrint = [];
            foreach ($arHtmlControl['VALUE'] as $val) {
                $arPrint[] = \Bitrix\Sale\Location\Admin\LocationHelper::getLocationStringById($val);
            }
            
            return implode(' / ', $arPrint);
        }
        
        return ' ';
    }
    
    /**
     * @param $arUserField
     * @param $arHtmlControl
     *
     * @return string
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
     */
    public function GetAdminListEditHTMLMulty($arUserField, $arHtmlControl)
    {
        $class = new static();
        
        return $class->GetEditFormHTMLMulty($arUserField, $arHtmlControl);
    }
    
    /**
     * Эта функция вызывается перед сохранением значений в БД.
     *
     * <p>Вызывается из метода Update объекта $USER_FIELD_MANAGER.</p>
     * <p>Для множественных значений функция вызывается несколько раз.</p>
     *
     * @param array $arUserField Массив описывающий поле.
     * @param mixed $value       Значение.
     *
     * @return string значение для вставки в БД.
     * @static
     */
    //public static function OnBeforeSave($arUserField, $value)
    //{
    //    //if(!in_array($value, $arUserField['VALUE'])) {
    //    if ($arUserField['MULTIPLE'] === 'Y') {
    //        \Bitrix\Main\Diag\Debug::writeToFile([
    //                                                 'FIELD_NAME' => $arUserField['FIELD_NAME'],
    //                                                 'VALUE'      => $arUserField['VALUE'],
    //                                             ],
    //                                             '$arUserField',
    //                                             '22.log');
    //        if (isset($value['L'])) {
    //            $value = $value['L'];
    //        }
    //        if (isset($value['G'])) {
    //            $value = [];
    //        }
    //        $value = explode(':', $value);
    //        TrimArr($value);
    //        \Bitrix\Main\Diag\Debug::writeToFile($value, '$value', '22.log');
    //        if (is_array($value) && !empty($value) && count($value) > 1) {
    //            $firstValue = $value[0];
    //            $value      = array_merge($arUserField['VALUE'], $value);
    //            global $USER_FIELD_MANAGER;
    //            $USER_FIELD_MANAGER->Update($arUserField['ENTITY_ID'],
    //                                        $arUserField['ID'],
    //                                        [$arUserField['FIELD_NAME'] => $value]);
    //
    //            //return $firstValue;
    //            return null;
    //            //return $value;
    //            //return serialize($value);
    //        }
    //        if (is_array($value) && !empty($value) && count($value) === 1) {
    //            $value = current($value);
    //            if (!empty($value)) {
    //                //if (!in_array($value, $arUserField['VALUE'])) {
    //                return $value;
    //                //} else {
    //                //    return false;
    //                //}
    //            } else {
    //                return null;
    //            }
    //        } elseif (!is_array($value) && !empty($value)) {
    //            return $value;
    //        } else {
    //            return null;
    //        }
    //    } else {
    //        return $value;
    //    }
    //    //}
    //}
    
    //public static function GetPublicView($arUserField, $arAdditionalParameters = [])
    //{
    //    $value = static::getFieldValue($arUserField, $arAdditionalParameters);
    //
    //    $html  = '';
    //    $first = true;
    //    foreach ($value as $res) {
    //        if (!$first) {
    //            $html .= static::getHelper()->getMultipleValuesSeparator();
    //        }
    //        $first = false;
    //
    //        if (strlen($arUserField['PROPERTY_VALUE_LINK']) > 0) {
    //            $res =
    //                '<a href="' . htmlspecialcharsbx(str_replace('#VALUE#',
    //                                                             (int)$res,
    //                                                             $arUserField['PROPERTY_VALUE_LINK'])) . '">' . $res
    //                . '</a>';
    //        } else {
    //            $res = (int)$res;
    //        }
    //
    //        $html .= static::getHelper()->wrapSingleField($res);
    //    }
    //
    //    static::initDisplay();
    //
    //    return static::getHelper()->wrapDisplayResult($html);
    //}
    //
    //public function getPublicEdit($arUserField, $arAdditionalParameters = [])
    //{
    //    $fieldName = static::getFieldName($arUserField, $arAdditionalParameters);
    //    $value     = static::getFieldValue($arUserField, $arAdditionalParameters);
    //
    //    $html = '';
    //
    //    foreach ($value as $res) {
    //        $attrList = [];
    //
    //        if ($arUserField['EDIT_IN_LIST'] !== 'Y') {
    //            $attrList['disabled'] = 'disabled';
    //        }
    //
    //        if ($arUserField['SETTINGS']['SIZE'] > 0) {
    //            $attrList['size'] = (int)$arUserField['SETTINGS']['SIZE'];
    //        }
    //
    //        if (array_key_exists('attribute', $arAdditionalParameters)) {
    //            $attrList = array_merge($attrList, $arAdditionalParameters['attribute']);
    //        }
    //
    //        if (isset($attrList['class']) && is_array($attrList['class'])) {
    //            $attrList['class'] = implode(' ', $attrList['class']);
    //        }
    //
    //        $attrList['class'] =
    //            static::getHelper()->getCssClassName() . (isset($attrList['class']) ? ' ' . $attrList['class'] : '');
    //
    //        $attrList['name'] = $fieldName;
    //
    //        $attrList['type']  = 'text';
    //        $attrList['value'] = $res;
    //
    //        $html .= static::getHelper()->wrapSingleField('<input ' . static::buildTagAttributes($attrList) . '/>');
    //    }
    //
    //    if ($arUserField['MULTIPLE'] === 'Y' && $arAdditionalParameters['SHOW_BUTTON'] !== 'N') {
    //        $html .= static::getHelper()->getCloneButton($fieldName);
    //    }
    //
    //    static::initDisplay();
    //
    //    return static::getHelper()->wrapDisplayResult($html);
    //}
}