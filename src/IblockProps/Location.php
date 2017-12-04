<?php

namespace FourPaws\IblockProps;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Location\Admin\LocationHelper;

Loc::loadMessages(__FILE__);

class Location
{
    public static function GetUserTypeDescription()
    {
        $result = [
            'PROPERTY_TYPE'        => 'S',
            'USER_TYPE'            => 'sale_location',
            'DESCRIPTION'          => GetMessage('IBLOCK_PROP_SALE_LOCATION_DESC'),
            'GetPropertyFieldHtml' => ['\FourPaws\IblockProps\Location', 'GetPropertyFieldHtml'],
            'GetAdminListViewHTML' => ['\FourPaws\IblockProps\Location', 'GetAdminListViewHTML'],
            'GetPublicViewHTML'    => ['\FourPaws\IblockProps\Location', 'GetAdminListViewHTML'],
        ];
        
        return $result;
    }

    public static function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
    {
        if (!Loader::includeModule('sale')) {
            return false;
        }

        global $APPLICATION;

        ob_start();
        ?><div><?php
        $APPLICATION->IncludeComponent(
            'bitrix:sale.location.selector.search',
            '',
            [
                'COMPONENT_TEMPLATE'     => 'search',
                'ID'                     => '',
                'CODE'                   => htmlspecialcharsbx($value['VALUE']),
                'INPUT_NAME'             => htmlspecialcharsbx($strHTMLControlName['VALUE']),
                'PROVIDE_LINK_BY'        => 'id',
                'JSCONTROL_GLOBAL_ID'    => '',
                'JS_CALLBACK'            => '',
                'SEARCH_BY_PRIMARY'      => 'Y',
                'EXCLUDE_SUBTREE'        => '',
                'FILTER_BY_SITE'         => 'Y',
                'SHOW_DEFAULT_LOCATIONS' => 'Y',
                'CACHE_TYPE'             => 'A',
                'CACHE_TIME'             => '36000000',
            ],
            false
        );
        ?></div><?php

        $output = ob_get_clean();
        
        return $output;
    }

    public static function GetAdminListViewHTML($arProperty, $value, $strHTMLControlName)
    {
        if (!Loader::IncludeModule('sale')) {
            return false;
        }

        return LocationHelper::getLocationStringByCode($arProperty['VALUE']);
    }
}

