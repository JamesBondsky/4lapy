<?php

namespace FourPaws\IblockProps;

use WebArch\BitrixIblockPropertyType\Abstraction\IblockPropertyTypeBase;
use Bitrix\Main\Page\Asset;

class BlocksShowSwitcher extends IblockPropertyTypeBase
{
    const PROPS_CODE_TO_SWITCH = [
        'BANNER_IMAGES_DESKTOP' => 'Показывать баннер',
        'BANNER_IMAGES_NOTEBOOK' => null,
        'BANNER_IMAGES_MOBILE' => null,
        'BANNER_LINK' => null,
        'VIDEO_MP4' => 'Показывать видео',
        'VIDEO_WEBM' => null,
        'VIDEO_OGG' => null,
        'VIDEO_TITLE' => null,
        'VIDEO_DESCRIPTION' => null,
        'VIDEO_PREVIEW_PICTURE' => null,
        'PRODUCT_CATEGORIES' => 'Показывать товарные категории'
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
                'BANNER_IMAGES_DESKTOP' => true,
                'VIDEO_MP4' => true,
                'PRODUCT_CATEGORIES' => true
            ];
        }

        $sortableBlosk = '';

        $arInputValues = [];
        foreach ($initValues as $code => $checked) {
            if (self::PROPS_CODE_TO_SWITCH[$code] == null) {
                continue;
            }
            switch ($code) {
                case 'BANNER_IMAGES_DESKTOP':
                    $arInputValues['BANNER_IMAGES_DESKTOP'] = $propsToSwitch[$code] .
                        ',' . $propsToSwitch['BANNER_IMAGES_NOTEBOOK'] .
                        ',' . $propsToSwitch['BANNER_IMAGES_MOBILE'] .
                        ',' . $propsToSwitch['BANNER_LINK'];
                    break;
                case 'VIDEO_MP4':
                    $arInputValues['VIDEO_MP4'] = $propsToSwitch[$code] .
                        ',' . $propsToSwitch['VIDEO_WEBM'] .
                        ',' . $propsToSwitch['VIDEO_OGG'] .
                        ',' . $propsToSwitch['VIDEO_TITLE'] .
                        ',' . $propsToSwitch['VIDEO_DESCRIPTION'];
                    break;
                case 'PRODUCT_CATEGORIES':
                    $arInputValues['PRODUCT_CATEGORIES'] = $propsToSwitch[$code];
            }
        }

        foreach ($arInputValues as $code => $ids) {
            $sortableBlosk .= '
            <li class="blocks-show-switcher-item">
                <input 
                class="input-switcher"
                id="' . $code . '"
                type="checkbox"
                value="' . $code . '"
                data-block="' . $ids . '" ' .
                (($initValues[$code]) ? 'checked="checked"' : '') . '/>
                <label for="' . $code . '">' . self::PROPS_CODE_TO_SWITCH[$code] . '</label>
            </li>';
        }


        $template = str_replace(['#SORTABLE_BLOCKS#', '#CONTROL_NAME#', '#CONTROL_NAME_VALUE#'],
            [$sortableBlosk, $control['VALUE'], json_encode($initValues)], $template);

        return $template;
    }
}