<?php
/**
 * Created by PhpStorm.
 * Date: 22.01.2018
 * Time: 17:51
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Discount;


/**
 * Class Gifter
 * @package FourPaws\SaleBundle\Discount
 */
class Gifter extends \CGlobalCondCtrlAtoms
{
    /**
     *
     *
     * @param $params
     *
     * @return array
     */
    public static function GetShowIn($params): array
    {
        return [];
    }

//    /**
//     *
//     * @param $condition
//     *
//     * @return array|bool|mixed|string
//     */
//    public static function Parse($condition)
//    {
//        if (!isset($condition['controlId'])) {
//            return false;
//        }
//        $atoms = static::GetAtomsEx($condition['controlId'], true);
//        if (empty($atoms)) {
//            return false;
//        }
//        $control = [
//            'ID' => $condition['controlId'],
//            'ATOMS' => $atoms
//        ];
//        unset($atoms);
//        return static::CheckAtoms($condition, $condition, $control, false);
//    }

    /**
     *
     * @param $arOneCondition
     * @param $arParams
     * @param $arControl
     * @param bool $arSubs
     *
     * @return string
     */
    public static function Generate($arOneCondition, $arParams, $arControl, $arSubs = false): string
    {
        $result = '';
        if (\is_string($arControl)) {
            $arControl = static::GetControls($arControl);
        }
        $boolError = !\is_array($arControl);

        if (!$boolError) {
            $arControl['ATOMS'] = static::GetAtomsEx($arControl['ID'], true);
            $arValues = static::CheckAtoms($arOneCondition, $arOneCondition, $arControl, false);
            $boolError = ($arValues === false);
        }

        $boolError = !$boolError && (!isset($arOneCondition['list']) or empty($arOneCondition['list']));

        if (!$boolError) {
            if ($arControl['ID'] === 'GifterElement') {
                $result = json_encode($arOneCondition);
            }
        }

        return $result;
    }

    /**
     *
     *
     * @return array|void
     */
    public static function GetControlShow($params)
    {
        //todo вставить пояснительный текст
        $res = parent::GetControlShow($params);
//        dump($res);
        return $res;
    }

    /**
     *
     * @param string|bool $strControlID
     *
     * @return array|bool|mixed
     */
    public static function GetControls($strControlID = false)
    {
        $arAtoms = static::GetAtomsEx();
//        dump($arAtoms);
        /** @noinspection OffsetOperationsInspection */
        $arControlList = [
            'GifterElement' => [
                'ID' => 'GifterElement',
                'PARENT' => true,
                'FIELD' => 'ID',
                'FIELD_TYPE' => 'int',
                'LABEL' => 'Из группы товаров',
                'PREFIX' => 'В количестве:',
                'ATOMS' => \is_array($arAtoms) && isset($arAtoms['GifterElement']) ? $arAtoms['GifterElement'] : false,
            ]
        ];

        foreach ($arControlList as &$control) {
            $control['EXIST_HANDLER'] = 'Y';
            $control['MODULE_ID'] = 'catalog';
            $control['MODULE_ENTITY'] = 'iblock';
            $control['ENTITY'] = 'ELEMENT';
        }
        unset($control);

        $result = false;
        if ($strControlID === false) {
            $result = $arControlList;
        } elseif (\is_string($strControlID) and isset($arControlList[$strControlID])) {
            $result = $arControlList[$strControlID];
        }

        return $result;
    }

    /**
     *
     * @param bool $strControlID
     * @param bool $boolEx
     *
     * @return array|bool
     */
    public static function GetAtomsEx($strControlID = false, $boolEx = false)
    {
        $atomList = [
            'GifterElement' => [
                'count' => [
                    'JS' => [
                        'id' => 'count',
                        'name' => 'count',
                        'type' => 'input',
                        'defaultText' => 1,
                        'defaultValue' => 1,
                    ],
                    'ATOM' => [
                        'ID' => 'count',
                        'FIELD_TYPE' => 'int',
                        'MULTIPLE' => 'N',
                        'VALIDATE' => ''
                    ]
                ],
                'list' => [
                    'JS' => [
                        'id' => 'list',
                        'name' => 'list',
                        'type' => 'multiDialog',
                        'popup_url' => '/bitrix/admin/cat_product_search_dialog.php',
                        'popup_params' => [
                            'lang' => LANGUAGE_ID,
                            'caller' => 'discount_rules',
                            'allow_select_parent' => 'Y',
                        ],
                        'param_id' => 'n',
                        'show_value' => 'Y'
                    ],
                    'ATOM' => [
                        'ID' => 'list',
                        'FIELD_TYPE' => 'int',
                        'MULTIPLE' => 'Y',
                        'VALIDATE' => 'element'
                    ]
                ]
            ]
        ];

        return static::searchControlAtoms($atomList, $strControlID, $boolEx);
    }
}