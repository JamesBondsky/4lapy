<?php
/**
 * Created by PhpStorm.
 * Date: 22.03.2018
 * Time: 14:00
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 */

namespace FourPaws\IblockProps;

use Bitrix\Sale\Internals\DiscountTable as BasketRulesTable;

/**
 * Class LinkToBasketRules
 * @package FourPaws\IblockProps
 */
class LinkToBasketRules
{
    public const TYPE_DISCOUNTS = 'discounts';
    public const TYPE_BASKET_RULES = 'basketRules';

    /**
     *
     *
     * @return array
     */
    public static function getUserTypeDescription(): array
    {
        return [
            'PROPERTY_TYPE' => 'S',
            'USER_TYPE' => 'LinkToBasketRules',
            'DESCRIPTION' => 'Привязка к правилам работы с корзиной',
            'GetPropertyFieldHtml' => [self::class, 'getPropertyFieldHtml'],
            'PrepareSettings' => [self::class, 'prepareSettings'],
            'GetSettingsHTML' => [self::class, 'getSettingsHTML'],
        ];
    }

    /**
     *
     *
     * @param $arProperty
     *
     * @return array
     */
    public static function prepareSettings($arProperty): array
    {
        $size = $arProperty['USER_TYPE_SETTINGS']['size'] ? (int)$arProperty['USER_TYPE_SETTINGS']['size'] : 1;
        $width = $arProperty['USER_TYPE_SETTINGS']['width'] ? (int)$arProperty['USER_TYPE_SETTINGS']['width'] : 0;

        return compact('size', 'width');
    }

    /**
     *
     *
     * @param $arProperty
     * @param $strHTMLControlName
     * @param $arPropertyFields
     *
     * @return string
     */
    public static function getSettingsHTML($arProperty, $strHTMLControlName, &$arPropertyFields): string
    {
        $settings = self::prepareSettings($arProperty);
        $arPropertyFields = [
            'HIDE' => ['ROW_COUNT', 'COL_COUNT'],
        ];

        ob_start() ?>
        <tr valign="top">
            <td>Высота списка:</td>
            <td><input type="text" size="5" name="<?= $strHTMLControlName['NAME'] ?>[size]"
                       value="<?= $settings['size'] ?>" title=""></td>
        </tr>
        <tr valign="top">
        <td>Ограничить по ширине (0 - не ограничивать):</td>
        <td><input type="text" size="5" name="<?= $strHTMLControlName['NAME'] ?>[width]"
                   value="<?= $settings['width'] ?>" title="">px
        </td>
        </tr><?php

        return ob_get_clean();
    }

    /**
     *
     *
     * @param $arProperty
     * @param $value
     * @param $strHTMLControlName
     *
     * @throws \Bitrix\Main\ArgumentException
     *
     * @return string
     */
    public static function getPropertyFieldHtml($arProperty, $value, $strHTMLControlName): string
    {
        $settings = self::prepareSettings($arProperty);
        $size = ($settings['size'] > 1) ? ' size="' . $settings['size'] . '"' : '';
        $width = ($settings['width'] > 0) ? ' style="width:' . $settings['width'] . 'px"' : '';

        $bWasSelect = false;
        $options = self::getOptionsHtml($arProperty, [$value['VALUE']], $bWasSelect);

        ob_start(); ?>
        <select name="<?= $strHTMLControlName['VALUE'] ?>"<?=
    $size . $width ?> title=""><?php
        if ($arProperty['IS_REQUIRED'] !== 'Y') { ?>
        <option value="" <?= (!$bWasSelect ? ' selected' : '') ?>>Не выбрано</option><?php
        } ?>
        <?= $options; ?>
        </select><?php

        return ob_get_clean();
    }

    /**
     *
     *
     * @param $arProperty
     * @param $values
     * @param $bWasSelect
     *
     * @throws \Bitrix\Main\ArgumentException
     *
     * @return string
     */
    public static function getOptionsHtml(
        /** @noinspection PhpUnusedParameterInspection */
        $arProperty,
        $values,
        &$bWasSelect
    ): string {
        $bWasSelect = false;
        ob_start();
        foreach (self::getElements() as $groupKey => $group) { ?>
            <optgroup label="<?= $group['GROUP_NAME'] ?>"><?php
            if (\is_array($group['ITEMS'])) {
                foreach ($group['ITEMS'] as $arItem) {
                    $value = $arItem['ID'];
                    $selected = \in_array($value, $values, true);
                    if ($selected) {
                        $bWasSelect = true;
                    } ?>
                    <option
                    value='<?= $value ?>' <?= $selected ? 'selected' : '' ?>><?= $arItem['NAME'] ?></option><?php
                }
            } ?>
            </optgroup><?php
        }

        return ob_get_clean();
    }

    /**
     *
     *
     * @throws \Bitrix\Main\ArgumentException
     *
     * @return array
     */
    public static function getElements(): array
    {
        \CModule::IncludeModule('catalog');
        \CModule::IncludeModule('sale');

        $result = [];
        /**
         * @todo перенести устаревшие каталожные скидки в отдельное свойство
         *
         * $discounts = [];
         * $discountsRes = $CD->GetList(['NAME' => 'ASC'], [], false, false, ['ID', 'NAME']);
         * while ($discount = $discountsRes->Fetch()) {
         * $discounts[] = $discount;
         * }
         * if($discounts) {
         * $result[self::TYPE_DISCOUNTS] = ['GROUP_NAME' => 'Скидки', 'ITEMS' => $discounts];
         * }
         */

        $basketRules = [];
        $basketRulesRes = BasketRulesTable::getList(['select' => ['ID', 'NAME'], 'filter' => []]);
        while ($basketRule = $basketRulesRes->fetch()) {
            $basketRules[] = $basketRule;
        }
        if ($basketRules) {
            $result[self::TYPE_BASKET_RULES] = ['GROUP_NAME' => 'Правила корзины', 'ITEMS' => $basketRules];
        }

        return $result;
    }
}