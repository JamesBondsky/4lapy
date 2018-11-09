<?php

namespace FourPaws\IblockProps;

use WebArch\BitrixIblockPropertyType\Abstraction\IblockPropertyTypeBase;
use Bitrix\Main\Page\Asset;

class ProductCategoriesProperty
{

    public function init()
    {
        AddEventHandler(
            'iblock',
            'OnIBlockPropertyBuildList',
            [$this, 'getUserTypeDescription']
        );
    }

    function GetUserTypeDescription()
    {
        return [
            'DESCRIPTION' => 'Товарные категории',
            'PROPERTY_TYPE' => 'S',
            'USER_TYPE' => 'ProductCategoriesProperty',
            'GetPropertyFieldHtml' => [__CLASS__, 'GetPropertyFieldHtml'],
        ];
    }

    public function GetPropertyFieldHtml(array $property, array $value, array $control)
    {
        Asset::getInstance()->addJs('/local/include/js/Sortable/Sortable.js');

        $template = file_get_contents(__DIR__ . '/templates/ProductCategoriesProperty.html');

        if ($value['VALUE'] != null) {
            $initValues = json_decode($value['VALUE'], true);
        } else {
            for ($i = 0; $i < 12; $i++) {
                $initValues[$i] = [
                    'title' => '',
                    'subtitle' => '',
                    'filters' => ''
                ];
            }
        }


        $template = str_replace(['#CONTROL_NAME#', '#CONTROL_NAME_VALUE#'],
            [$control['VALUE'], json_encode($initValues)], $template);

        return $template;
    }
}