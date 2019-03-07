<?php

namespace FourPaws\IblockProps;

class OfferRegionDiscountsProperty
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
            'DESCRIPTION' => 'Региональные простые скидки',
            'PROPERTY_TYPE' => 'S',
            'USER_TYPE' => 'OfferRegionDiscountsProperty',
            'GetPropertyFieldHtml' => [__CLASS__, 'GetPropertyFieldHtml'],
            'ConvertToDB' => [__CLASS__, 'ConvertToDB'],
        ];
    }

    public static function ConvertToDB($arProperty, $value)
    {
        return $value;
    }

    public function GetPropertyFieldHtml(array $property, array $value, array $control)
    {
        if ($value['VALUE'] != null && $value['VALUE'] != 'null' && $value['VALUE'] != '') {
            $values = json_decode($value['VALUE'], true);
            if (!is_array($values)) {
                $values = [
                    [
                        'id' => '',
                        'cond_for_action' => '',
                        'price_action' => '',
                        'cond_value' => ''
                    ]
                ];
            }
        } else {
            $values = [
                [
                    'id' => '',
                    'cond_for_action' => '',
                    'price_action' => '',
                    'cond_value' => ''
                ]
            ];
        }

        $template = file_get_contents(__DIR__ . '/templates/OfferRegionDiscountsProperty.html');

        $template = str_replace(
            [
                '#CONTROL_NAME#',
                '#CONTROL_NAME_VALUE#'
            ],
            [
                $control['VALUE'],
                json_encode($values, JSON_UNESCAPED_UNICODE),
            ],
            $template
        );

        $prices = [];
        $dbRes = \CCatalogGroup::GetList(['NAME_LANG' => 'ASC'], ['BASE' => 'N']);
        while ($arPrice = $dbRes->Fetch()) {
            $prices[] = [
                'region_id' => $arPrice['ID'],
                'name' => $arPrice['NAME_LANG']
            ];
        }
        $template .= "<div class='property-wrapper'>";
        foreach ($values as $key => $arValue) {
            $template .= "<div class='select-block' data-select-block='" . $arValue['id'] . "' data-number='" . $key . "'><div class='left-col'><select class='changedFieldSelect code' name='region_" . $key . "' size='6'>";
            foreach ($prices as $price) {
                $template .= "<option value='" . $price['region_id'] . "' " . (($arValue['id'] == $price['region_id']) ? "selected" : "") . ">" . $price['name'] . "</option>";
            }
            $template .= "</select></div>";
            $template .= "<div class='right-col'><label class='right-col-label' for='cond_for_action_" . $key . "'>Тип цены по акции</label>";
            $template .= "<input type='text' class='changedField cond_for_action' name='cond_for_action_" . $key . "' value='" . $arValue['cond_for_action'] . "'></div>";
            $template .= "<div class='right-col'><label class='right-col-label' for='price_action_" . $key . "'>Цена по акции</label>";
            $template .= "<input type='text' class='changedField price_action' name='price_action_" . $key . "' value='" . $arValue['price_action'] . "'></div>";
            $template .= "<div class='right-col'><label class='right-col-label' for='cond_value_" . $key . "'>Размер скидки на товар</label>";
            $template .= "<input type='text' class='changedField cond_value' name='cond_value_" . $key . "' value='" . $arValue['cond_value'] . "'></div>";
            $template .= "<div><input type='button' class='remove-region' data-remove-region='" . $key . "' onclick='javascript:void(0);' value='Удалить'/></div>";
            $template .= "</div>";
        }
        $template .= "</div>";

        $template .= "<input type='button' id='add-new-region' onclick='javascript:void(0);' value='Добавить региональные простые скидки'/>";

        return $template;
    }
}