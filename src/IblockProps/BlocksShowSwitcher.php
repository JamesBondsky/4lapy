<?php

namespace FourPaws\IblockProps;

use WebArch\BitrixIblockPropertyType\Abstraction\IblockPropertyTypeBase;
use Bitrix\Main\Page\Asset;

class BlocksShowSwitcher extends IblockPropertyTypeBase
{
    const PROPS_CODE_TO_SWITCH = [
        'SLIDER_IMAGES' => 'Показывать слайдер',
        'VIDEO' => 'Показывать видео',
        'VIDEO_DESCRIPTION' => null,
        'SECTIONS' => 'Показывать разделы товаров'
    ];

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return 'Переключение отображаемых блоков';
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
            'DESCRIPTION' => 'Переключение отображаемых блоков',
            'PROPERTY_TYPE' => 'S',
            'USER_TYPE' => 'BlocksShowSwitcher',
            'GetPropertyFieldHtml' => [__CLASS__, 'GetPropertyFieldHtml']
        ];
    }

    static function GetPropsToSwitch($iblockID)
    {
        $propsToSwitch = [];
        $dbSwitchBlockProperties = \CIBlockProperty::GetList(
            [],
            [
                'IBLOCK_ID' => $iblockID
            ]
        );
        while ($switchBlockProperty = $dbSwitchBlockProperties->Fetch()) {
            if (in_array($switchBlockProperty['CODE'], array_keys(self::PROPS_CODE_TO_SWITCH))) {
                $propsToSwitch[$switchBlockProperty['CODE']] = $switchBlockProperty['ID'];
            }
        }

        return $propsToSwitch;
    }

    public function GetPropertyFieldHtml(array $property, array $value, array $control)
    {
        Asset::getInstance()->addJs('/local/include/js/Sortable/Sortable.js');

        $propsToSwitch = self::GetPropsToSwitch($property['IBLOCK_ID']);

        $template = file_get_contents(__DIR__ . '/templates/BlocksShowSwitcher.html');

        if ($value['VALUE'] != null) {
            $initValues = json_decode($value['VALUE'], true);
        } else {
            $initValues = [
                'SLIDER_IMAGES' => true,
                'VIDEO' => true,
                'SECTIONS' => true
            ];
        }

        $sortableBlosk = '';
        foreach ($initValues as $code => $checked) {
            if (self::PROPS_CODE_TO_SWITCH[$code] == null) {
                continue;
            }
            $sortableBlosk .= '
            <li class="blocks-show-switcher-item">
                <input 
                class="input-switcher" 
                id="' . $code . '" 
                type="checkbox" 
                value="' . $code . '" 
                data-block="' . $propsToSwitch[$code] . '" ' .
                (($code == 'VIDEO') ? 'data-block-second="' . $propsToSwitch['VIDEO_DESCRIPTION'] . '"' : '') .
                (($initValues[$code]) ? 'checked="checked"' : '') . '/>
                <label for="' . $code . '">' . self::PROPS_CODE_TO_SWITCH[$code] . '</label>
            </li>';
        }

        $template = str_replace(['#SORTABLE_BLOCKS#', '#CONTROL_NAME#', '#CONTROL_NAME_VALUE#'], [$sortableBlosk, $control['VALUE'], $value['VALUE']], $template);

        return $template;
    }
}