<?php

namespace Sprint\Migration;

class OffersPriceProps20180109180000 extends \Adv\Bitrixtools\Migration\SprintMigrationBase {
    protected $description = 'Добавление свойств для акционных цен в инфоблок торговых предложений';

    public function up()
    {
        //$obHelper = new \Sprint\Migration\HelperManager();
        if (!\Bitrix\Main\Loader::includeModule('iblock')) {
            return false;
        }
        $this->addOffersProps();
    }

    public function down()
    {
        //
    }

    /**
     * @return bool
     */
    protected function addOffersProps()
    {
        $iTmpIBlockId = $this->getIBlockIdByCode('offers', 'catalog');
        if (!$iTmpIBlockId) {
            return false;
        }

        $obHelper = new \Sprint\Migration\HelperManager();

        $iPropsSort = 5000;

        // ---
        $sTmpPropCode = 'PRICE_ACTION';
        $iPropsSort += 100;
        $obHelper->Iblock()->addPropertyIfNotExists(
            $iTmpIBlockId,
            [
                'NAME' => 'Цена по акции',
                'PROPERTY_TYPE' => 'N',
                'LIST_TYPE' => 'C',
                'SEARCHABLE' => 'N',
                'USER_TYPE' => '',
                'USER_TYPE_SETTINGS' => [],
                'MULTIPLE' => 'N',
                'ACTIVE' => 'Y',
                'FILTRABLE' => 'Y',
                'SORT' => $iPropsSort,
                'CODE' => $sTmpPropCode,
                'ROW_COUNT' => '1',
                'COL_COUNT' => '10',
                'HINT' => '',
            ]
        );

        // ---
        $sTmpPropCode = 'COND_FOR_ACTION';
        $iPropsSort += 100;
        $obHelper->Iblock()->addPropertyIfNotExists(
            $iTmpIBlockId,
            [
                'NAME' => 'Тип цены по акции',
                'PROPERTY_TYPE' => 'S',
                'LIST_TYPE' => 'C',
                'SEARCHABLE' => 'N',
                'USER_TYPE' => '',
                'USER_TYPE_SETTINGS' => [],
                'MULTIPLE' => 'N',
                'ACTIVE' => 'Y',
                'FILTRABLE' => 'Y',
                'SORT' => $iPropsSort,
                'CODE' => $sTmpPropCode,
                'ROW_COUNT' => '1',
                'COL_COUNT' => '10',
                'HINT' => 'Пусто – розничная цена; VKA0 – цена по акции "Рекламная цена"; ZRBT – цена по акции "Скидка на товар"'
            ]
        );

        // ---
        $sTmpPropCode = 'COND_VALUE';
        $iPropsSort += 100;
        $obHelper->Iblock()->addPropertyIfNotExists(
            $iTmpIBlockId,
            [
                'NAME' => 'Размер скидки на товар',
                'PROPERTY_TYPE' => 'N',
                'LIST_TYPE' => 'C',
                'SEARCHABLE' => 'N',
                'USER_TYPE' => '',
                'USER_TYPE_SETTINGS' => [],
                'MULTIPLE' => 'N',
                'ACTIVE' => 'Y',
                'FILTRABLE' => 'Y',
                'SORT' => $iPropsSort,
                'CODE' => $sTmpPropCode,
                'ROW_COUNT' => '1',
                'COL_COUNT' => '10',
                'HINT' => 'Содержит процент скидки по акции "Скидка на товар" (ZRBT) со знаком минус.'
            ]
        );

        $arTabs = $obHelper->AdminIblock()->extractElementForm($iTmpIBlockId);
        $arTabs['Торговое предложение']['PROPERTY_PRICE_ACTION'] = 'Цена по акции';
        $arTabs['Торговое предложение']['PROPERTY_COND_FOR_ACTION'] = 'Тип цены по акции';
        $arTabs['Торговое предложение']['PROPERTY_COND_VALUE'] = 'Размер скидки на товар';
        $obHelper->AdminIblock()->buildElementForm($iTmpIBlockId, $arTabs);

        return true;
    }

    /**
     * @param string $sIBlockCode
     * @param string $sIBlockType
     * @return int
     */
    protected function getIBlockIdByCode($sIBlockCode, $sIBlockType = '')
    {
        $iReturn = 0;

        $arFilter = array(
            'CHECK_PERMISSIONS' => 'N',
            'CODE' => $sIBlockCode,
        );
        if (strlen($sIBlockType)) {
            $arFilter['TYPE'] = $sIBlockType;
        }
        $arIBlock = \CIBlock::GetList(array('ID' => 'ASC'), $arFilter)->fetch();
        $iReturn = $arIBlock ? $arIBlock['ID'] : 0;

        return $iReturn;
    }
}
