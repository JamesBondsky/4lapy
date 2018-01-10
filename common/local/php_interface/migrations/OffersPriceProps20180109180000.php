<?php

namespace Sprint\Migration;

class OffersPriceProps20180109180000 extends \Adv\Bitrixtools\Migration\SprintMigrationBase {
    protected $description = 'Добавление свойств для акционных цен в инфоблок торговых предложений';

    public function up()
    {
        //$helper = new \Sprint\Migration\HelperManager();
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
        $tmpIBlockId = $this->getIBlockIdByCode('offers', 'catalog');
        if (!$tmpIBlockId) {
            return false;
        }

        $helper = new \Sprint\Migration\HelperManager();

        $propsSort = 5000;

        // ---
        $tmpPropCode = 'PRICE_ACTION';
        $propsSort += 100;
        $helper->Iblock()->addPropertyIfNotExists(
            $tmpIBlockId,
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
                'SORT' => $propsSort,
                'CODE' => $tmpPropCode,
                'ROW_COUNT' => '1',
                'COL_COUNT' => '10',
                'HINT' => '',
            ]
        );

        // ---
        $tmpPropCode = 'COND_FOR_ACTION';
        $propsSort += 100;
        $helper->Iblock()->addPropertyIfNotExists(
            $tmpIBlockId,
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
                'SORT' => $propsSort,
                'CODE' => $tmpPropCode,
                'ROW_COUNT' => '1',
                'COL_COUNT' => '10',
                'HINT' => 'Пусто – розничная цена; VKA0 – цена по акции "Рекламная цена"; ZRBT – цена по акции "Скидка на товар"'
            ]
        );

        // ---
        $tmpPropCode = 'COND_VALUE';
        $propsSort += 100;
        $helper->Iblock()->addPropertyIfNotExists(
            $tmpIBlockId,
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
                'SORT' => $propsSort,
                'CODE' => $tmpPropCode,
                'ROW_COUNT' => '1',
                'COL_COUNT' => '10',
                'HINT' => 'Содержит процент скидки по акции "Скидка на товар" (ZRBT) со знаком минус.'
            ]
        );

        $tabs = $helper->AdminIblock()->extractElementForm($tmpIBlockId);
        $tabs['Торговое предложение']['PROPERTY_PRICE_ACTION'] = 'Цена по акции';
        $tabs['Торговое предложение']['PROPERTY_COND_FOR_ACTION'] = 'Тип цены по акции';
        $tabs['Торговое предложение']['PROPERTY_COND_VALUE'] = 'Размер скидки на товар';
        $helper->AdminIblock()->buildElementForm($tmpIBlockId, $tabs);

        return true;
    }

    /**
     * @param string $iblockCode
     * @param string $iblockType
     * @return int
     */
    protected function getIBlockIdByCode($iblockCode, $iblockType = '')
    {
        $return = 0;

        $filter = array(
            'CHECK_PERMISSIONS' => 'N',
            'CODE' => $iblockCode,
        );
        if (strlen($iblockType)) {
            $filter['TYPE'] = $iblockType;
        }
        $iblock = \CIBlock::GetList(array('ID' => 'ASC'), $filter)->fetch();
        $return = $iblock ? $iblock['ID'] : 0;

        return $return;
    }
}
