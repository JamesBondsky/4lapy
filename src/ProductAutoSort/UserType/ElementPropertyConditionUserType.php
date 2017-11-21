<?php

namespace FourPaws\ProductAutoSort\UserType;

use Bitrix\Catalog\CatalogIblockTable;
use Bitrix\Main\Page\Asset;
use CIBlock;
use CIBlockProperty;
use CUserTypeString;
use FourPaws\App\Application;
use FourPaws\ProductAutoSort\ProductAutoSortService;

class ElementPropertyConditionUserType extends CUserTypeString
{
    const USER_TYPE_ID = 'element_property_condition';

    const SETTING_PRODUCTS_IBLOCK_ID = 'PRODUCTS_IBLOCK_ID';

    const SETTING_OFFERS_IBLOCK_ID = 'OFFERS_IBLOCK_ID';

    const VALUE_PROP_ID = 'PROP_ID';

    const VALUE_PROP_VALUE = 'PROP_VALUE';

    /**
     * @var array
     */
    protected static $jsEngaged = [];

    /**
     * @var ProductAutoSortService
     */
    protected static $autosortService;

    /**
     * Возвращает сервис автосортировки.
     *
     * @internal Из-за статичности пользовательского свойства приходится делать вот такой ленивый getter.
     *
     * @return ProductAutoSortService|object
     */
    protected static function getAutosortService()
    {
        if (is_null(self::$autosortService)) {
            self::$autosortService = Application::getInstance()->getContainer()->get('product.autosort.service');
        }

        return self::$autosortService;
    }

    /**
     * @return array
     */
    private static function getDefaultValue(): array
    {
        return [
            self::VALUE_PROP_ID    => 0,
            self::VALUE_PROP_VALUE => null,
        ];
    }

    /**
     * @inheritdoc
     */
    public function getUserTypeDescription()
    {
        return [
            "USER_TYPE_ID"  => static::USER_TYPE_ID,
            "CLASS_NAME"    => __CLASS__,
            "DESCRIPTION"   => "Условие для свойств элемента",
            "BASE_TYPE"     => \CUserTypeManager::BASE_TYPE_STRING,
            "EDIT_CALLBACK" => [__CLASS__, 'GetPublicEdit'],
            "VIEW_CALLBACK" => [__CLASS__, 'GetPublicView'],
            //Можно задать компонент для отображения значений свойства в публичной части.
            //"VIEW_COMPONENT_NAME" => "my:system.field.view",
            //"VIEW_COMPONENT_TEMPLATE" => "string",
            //и для редактирования
            //"EDIT_COMPONENT_NAME" => "my:system.field.view",
            //"EDIT_COMPONENT_TEMPLATE" => "string",
            // также можно задать callback для отображения значений
            // "VIEW_CALLBACK" => callable
            // и для редактирования
            // "EDIT_CALLBACK" => callable
        ];
    }

    /**
     * @inheritdoc
     */
    public function PrepareSettings($arUserField)
    {
        //Нет настроек
        return [];
    }

    /**
     * @inheritdoc
     */
    public function GetSettingsHTML($arUserField = false, $arHtmlControl, $bVarsFromForm)
    {
        //Нет настроек.
        return '<tr><td>Свойство не содержит никаких настроек</td></tr>';
    }

    public static function onBeforeSaveAll($arUserField, array $rawValueList)
    {
        $valueList = [];

        foreach ($rawValueList as $key => $rawValue) {
            $valueList[$key] = self::onBeforeSave($arUserField, $rawValue);
        }

        self::getAutosortService()->syncValueMulti(
            (int)$arUserField['ID'],
            (int)$arUserField['VALUE_ID'],
            $valueList
        );

        return $valueList;
    }

    /**
     * Эта функция вызывается перед сохранением значений в БД.
     *
     * <p>Вызывается из метода Update объекта $USER_FIELD_MANAGER.</p>
     * <p>Для множественных значений функция вызывается несколько раз.</p>
     *
     * @param array $arUserField Массив описывающий поле.
     * @param mixed $value Значение.
     *
     * @return string|null значение для вставки в БД.
     * @static
     */
    public function onBeforeSave($arUserField, $value)
    {

        if (
            !isset($value[self::VALUE_PROP_ID], $value[self::VALUE_PROP_VALUE])
            || $value[self::VALUE_PROP_ID] <= 0
        ) {
            return null;
        }

        /*
         * Предотвращение попадания посторонних данных
         */
        $realValue = [
            self::VALUE_PROP_ID    => $value[self::VALUE_PROP_ID],
            self::VALUE_PROP_VALUE => $value[self::VALUE_PROP_VALUE],
        ];

        //Только если не множественное
        if ($arUserField['MULTIPLE'] == 'N') {

            self::getAutosortService()->syncValue(
                (int)$arUserField['ID'],
                (int)$arUserField['VALUE_ID'],
                (int)$realValue[self::VALUE_PROP_ID],
                $realValue[self::VALUE_PROP_VALUE]
            );

        }

        return serialize($realValue);
    }

    /**
     * @param $rawValue
     *
     * @return array
     */
    protected static function normalizeValue($rawValue)
    {
        $realValue = self::getDefaultValue();

        if (!is_null($rawValue) && $rawValue != '') {
            $value = unserialize(html_entity_decode($rawValue));
            if (is_array($value)) {
                $realValue = array_merge($realValue, $value);
            }
        }

        return $realValue;
    }

    /**
     * @inheritdoc
     */
    public function GetEditFormHTML($arUserField, $arHtmlControl)
    {
        $productsIblockId = self::getIblockId($arUserField);
        $offersIblockId = self::getOffersIblockId($productsIblockId);

        $currentValue = self::normalizeValue($arHtmlControl['VALUE']);
        $propertyId = $currentValue[self::VALUE_PROP_ID];
        $propertyValue = $currentValue[self::VALUE_PROP_VALUE];

        $optListHtml = '';
        $optListHtml .= self::getPropOptListHtml($productsIblockId, $propertyId);
        $optListHtml .= self::getPropOptListHtml($offersIblockId, $propertyId);

        if ($optListHtml == '') {
            return 'Ошибка: свойство совместимо только с разделом инфоблока.';
        }

        $optListHtml = '<option value="0" >(не выбрано)</option>' . $optListHtml;

        $uniqId = uniqid('propCond_');

        $selectHtml = sprintf(
            '<select class="PropertySelect" data-input-id="%s" name="%s[%s]" data-uf-id="%s" >%s</select>',
            $uniqId,
            $arHtmlControl['NAME'],
            self::VALUE_PROP_ID,
            $arUserField['ID'],
            $optListHtml
        );

        $valueInput = sprintf(
            '<input class="PropertyValue" id="%s" title="значение свойства" type="text" name="%s[%s]" size="20"  maxlength="225" value="%s" >',
            $uniqId,
            $arHtmlControl["NAME"],
            self::VALUE_PROP_VALUE,
            htmlspecialcharsbx($propertyValue)
        );

        self::getJsScript($arUserField['ID']);

        return '<div class="ElemPropCondItem">'.$selectHtml . ' = ' . $valueInput.'</div>';
    }

    /**
     * @param int $iblockId
     * @param int $selectedId
     *
     * @return string
     */
    private static function getPropOptListHtml($iblockId, $selectedId)
    {
        $html = '';

        if ($iblockId <= 0) {
            return '';
        }

        $iblock = CIBlock::GetByID($iblockId)->GetNext();
        if (false == $iblock) {
            return '';
        }

        $html .= sprintf(
            '<optgroup label="%s [%d]" >',
            $iblock['NAME'],
            $iblock['ID']
        );

        $dbPropList = CIBlockProperty::GetList(['SORT' => 'ASC'], ['ACTIVE' => 'Y', 'IBLOCK_ID' => (int)$iblockId]);
        while ($arProp = $dbPropList->Fetch()) {

            $html .= sprintf(
                '<option %s value="%d" data-user-type="%s" data-property-type="%s" > %s [%s]</option>',
                ($selectedId == $arProp['ID']) ? ' selected="selected" ' : '',
                $arProp['ID'],
                $arProp['USER_TYPE'],
                $arProp['PROPERTY_TYPE'],
                $arProp['NAME'],
                $arProp['CODE']
            );
        }

        $html .= '</optgroup>';

        return $html;
    }

    /**
     * Возвращает инфоблок, в котором применено данное свойство.
     *
     * @param array $arUserField
     *
     * @return int
     */
    private static function getIblockId(array $arUserField)
    {
        if (!isset($arUserField['ENTITY_ID'])) {
            return 0;
        }

        $matches = [];
        if (1 === preg_match('/IBLOCK_(\d+)_SECTION/', $arUserField['ENTITY_ID'], $matches)) {
            return (int)$matches[1];
        }

        return 0;

    }

    /**
     * Пытается вернуть id инфоблока с торговыми предложениями
     *
     * @param int $productsIblockId
     *
     * @return int
     */
    private static function getOffersIblockId($productsIblockId)
    {
        $offersCatalogItem = CatalogIblockTable::query()
                                               ->setFilter(
                                                   ['PRODUCT_IBLOCK_ID' => (int)$productsIblockId]
                                               )
                                               ->setSelect(['IBLOCK_ID'])
                                               ->exec()
                                               ->fetch();

        if (false == $offersCatalogItem || !isset($offersCatalogItem['IBLOCK_ID'])) {
            return 0;
        }

        return (int)$offersCatalogItem['IBLOCK_ID'];
    }

    private static function getJsScript($ufId)
    {
        if (isset(self::$jsEngaged[$ufId])) {
            return '';
        }
        self::$jsEngaged[$ufId] = true;

        Asset::getInstance()->addJs('/local/include/js/element-property-condition-user-type.js');

        return '';

    }

}
