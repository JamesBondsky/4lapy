<?php

namespace FourPaws\Menu\Query;

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use CDBResult;
use CIBlockElement;
use CIBlockSection;
use FourPaws\BitrixOrm\Collection\CollectionBase;
use FourPaws\BitrixOrm\Query\QueryBase;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\Menu\Collection\MenuItemCollection;
use FourPaws\Menu\Model\MenuItem;

class MainMenuQuery extends QueryBase
{

    /**
     * @return CollectionBase
     */
    public function exec(): CollectionBase
    {
        return new MenuItemCollection($this->doExec());
    }

    /**
     * @inheritdoc
     */
    public function doExec(): CDBResult
    {
        //TODO Метод может не подойти для выборки внутренних участков дерева. Или ок?

        $mixedList = [];

        $arNavStartParams = $this->getNav();
        if (is_array($arNavStartParams) && empty($arNavStartParams)) {
            $arNavStartParams = false;
        }

        $dbSectionList = CIBlockSection::GetList(
            $this->getOrder(),
            $this->getFilterWithBase(),
            false,
            $this->getSelectWithBase(),
            $arNavStartParams
        );

        while ($section = $dbSectionList->GetNext()) {
            $mixedList[] = new MenuItem($section);
        }

        $arNavStartParams = $this->getNav();
        if (is_array($arNavStartParams) && empty($arNavStartParams)) {
            $arNavStartParams = false;
        }

        $arGroupBy = $this->getGroup();
        if (is_array($arGroupBy) && empty($arGroupBy)) {
            $arGroupBy = false;
        }

        $dbElementList = CIBlockElement::GetList(
            $this->getOrder(),
            $this->getFilterWithBase(),
            $arGroupBy,
            $arNavStartParams,
            $this->getSelectWithBase()
        );
        while ($element = $dbElementList->Fetch()) {
            $mixedList[] = new MenuItem($element);
        }

        /**
         * Единый порядок пунктов меню по полю SORT
         */
        usort(
            $mixedList,
            function ($itemA, $itemB) {
                if ($itemA instanceof MenuItem && $itemB instanceof MenuItem) {
                    return $itemA->getSort() <=> $itemB->getSort();
                }

                return 0;
            }
        );

        $dbMixed = new CDBResult();
        $dbMixed->InitFromArray($mixedList);

        return $dbMixed;
    }

    /**
     * @inheritdoc
     */
    public function getBaseSelect(): array
    {
        //TODO Добавить сюда и для SECTION и для ELEMENT информацию о родителе
        return [
            'IBLOCK_ID',
            'ID',
            'NAME',
            'PROPERTY_HREF',
            'PROPERTY_ELEMENT_HREF',
            'PROPERTY_SECTION_HREF',
            'PROPERTY_TARGET_BLANK',
            'UF_TARGET_BLANK',
            'UF_HREF',
            'UF_SECTION_HREF',
            'UF_BRAND_MENU',
        ];
    }

    /**
     * @inheritdoc
     */
    public function getBaseFilter(): array
    {
        return ['IBLOCK_ID' => IblockUtils::getIblockId(IblockType::MENU, IblockCode::MAIN_MENU),];
    }

}
