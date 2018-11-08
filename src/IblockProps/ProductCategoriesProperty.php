<?php

namespace FourPaws\IblockProps;

use WebArch\BitrixIblockPropertyType\Abstraction\IblockPropertyTypeBase;
use Bitrix\Main\Page\Asset;

class ProductCategoriesProperty extends IblockPropertyTypeBase
{

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return 'Товарные категории';
    }

    /**
     * @inheritdoc
     */
    public function getPropertyType()
    {
        return self::PROPERTY_TYPE_NUMBER;
    }

    /**
     * @inheritdoc
     */
    public function getCallbacksMapping()
    {
        return [
            'GetPropertyFieldHtml' => [$this, 'getPropertyFieldHtml']
        ];
    }

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
            'GetPropertyFieldHtml' => [__CLASS__, 'GetPropertyFieldHtml']
        ];
    }

    public function GetPropertyFieldHtml(array $property, array $value, array $control)
    {
        Asset::getInstance()->addJs('/local/include/js/Sortable/Sortable.js');

        $template = file_get_contents(__DIR__ . '/templates/ProductCategoriesProperty.html');

        if ($value['VALUE'] != null) {
            $initValues = json_decode($value['VALUE'], true);
        } else {
            $initValues = [
                'SLIDER_IMAGES' => true,
                'VIDEO' => true,
                'SECTIONS' => true
            ];
        }


        $template = str_replace(['#CONTROL_NAME#', '#CONTROL_NAME_VALUE#'],
            [$control['VALUE'], $value['VALUE']], $template);

        return $template;
    }
}