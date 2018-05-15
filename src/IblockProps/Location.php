<?php

namespace FourPaws\IblockProps;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Location\Admin\LocationHelper;

Loc::loadMessages(__FILE__);//  ЗАЧЕМ?

/**
 * Class Location
 * @package FourPaws\IblockProps
 */
class Location
{
    /**
     *
     *
     * @return array
     */
    public static function GetUserTypeDescription(): array
    {
        return [
            'PROPERTY_TYPE' => 'S',
            'USER_TYPE' => 'sale_location',
            'DESCRIPTION' => GetMessage('IBLOCK_PROP_SALE_LOCATION_DESC'), //  ЗАЧЕМ?
            'GetPropertyFieldHtml' => [self::class, 'GetPropertyFieldHtml'],
            'GetAdminListViewHTML' => [self::class, 'GetAdminListViewHTML'],
            'GetPublicViewHTML' => [self::class, 'GetAdminListViewHTML'],
        ];
    }

    /**
     *
     *
     * @param $arProperty
     * @param $value
     * @param $strHTMLControlName
     *
     * @throws \Bitrix\Main\LoaderException
     * @return bool|string
     */
    public static function GetPropertyFieldHtml(
        /** @noinspection PhpUnusedParameterInspection */
        $arProperty,
        $value,
        $strHTMLControlName
    ) {
        $result = false;
        if (Loader::includeModule('sale')) {
            global $APPLICATION;
            ob_start();
            $APPLICATION->IncludeComponent(
                'bitrix:sale.location.selector.search',
                '',
                [
                    'COMPONENT_TEMPLATE' => 'search',
                    'ID' => '',
                    'CODE' => htmlspecialcharsbx($value['VALUE']),
                    'INPUT_NAME' => htmlspecialcharsbx($strHTMLControlName['VALUE']),
                    'PROVIDE_LINK_BY' => 'code',
                    'JSCONTROL_GLOBAL_ID' => '',
                    'JS_CALLBACK' => '',
                    'SEARCH_BY_PRIMARY' => 'Y',
                    'EXCLUDE_SUBTREE' => '',
                    'FILTER_BY_SITE' => 'Y',
                    'SHOW_DEFAULT_LOCATIONS' => 'Y',
                    'CACHE_TYPE' => 'A',
                    'CACHE_TIME' => '36000000',
                ],
                false
            );
            $result = ob_get_clean();
        }
        return $result;
    }

    /**
     *
     *
     * @param $arProperty
     * @param $value
     * @param $strHTMLControlName
     *
     * @throws \Bitrix\Main\LoaderException
     * @return bool|string
     */
    public static function GetAdminListViewHTML(
        $arProperty,
        /** @noinspection PhpUnusedParameterInspection */
        $value,
        /** @noinspection PhpUnusedParameterInspection */
        $strHTMLControlName
    ) {
        $result = false;
        if (Loader::includeModule('sale')) {
            $result = LocationHelper::getLocationStringByCode($arProperty['VALUE']);
        }
        return $result;
    }
}

