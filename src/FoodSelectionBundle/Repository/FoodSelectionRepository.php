<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\FoodSelectionBundle\Repository;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Iblock\SectionElementTable;
use Bitrix\Iblock\SectionTable;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\SystemException;
use FourPaws\BitrixOrm\Query\IblockElementQuery;
use FourPaws\BitrixOrm\Query\IblockSectQuery;
use FourPaws\BitrixOrm\Utils\IblockPropEntityConstructor;
use FourPaws\Catalog\Query\ProductQuery;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

/**
 * Class FoodSelectionRepository
 *
 * @package FourPaws\FoodSelectionBundle\Repository
 */
class FoodSelectionRepository
{
    /**
     * @param array $params
     *
     * @return array
     */
    public function getItems(array $params = []) : array
    {
        /** @var IblockElementQuery $query */
        try {
            $query = new IblockElementQuery(
                IblockUtils::getIblockId(
                    IblockType::CATALOG,
                    IblockCode::FOOD_SELECTION
                )
            );
            if (!isset($params['select'])) {
                $params['select'] = ['*'];
            }
            $query->withSelect($params['select']);
            if (!empty($params['filter'])) {
                $query->withFilter($params['filter']);
            }
            if (!empty($params['order'])) {
                $query->withOrder($params['order']);
            }
            
            if (!empty($params['nav'])) {
                $query->withNav($params['nav']);
            }
            if (!empty($params['group'])) {
                $query->withGroup($params['group']);
            }
            
            $res = $query->exec();
            
            return $res->toArray();
        } catch (IblockNotFoundException $e) {
            return [];
        }
    }
    
    /**
     * @param array $params
     *
     * @return array
     */
    public function getSections(array $params = []) : array
    {
        /** @var IblockElementQuery $query */
        try {
            $query = new IblockSectQuery(
                IblockUtils::getIblockId(
                    IblockType::CATALOG,
                    IblockCode::FOOD_SELECTION
                )
            );
            if (!isset($params['select'])) {
                $params['select'] = ['*'];
            }
            $query->withSelect($params['select']);
            if (!empty($params['filter'])) {
                $query->withFilter($params['filter']);
            }
            if (!empty($params['order'])) {
                $query->withOrder($params['order']);
            }
            
            if (!empty($params['nav'])) {
                $query->withNav($params['nav']);
            }
            if (!empty($params['group'])) {
                $query->withGroup($params['group']);
            }
            
            $res = $query->exec();
            
            return $res->toArray();
        } catch (IblockNotFoundException $e) {
            return [];
        }
    }
    
    /**
     * @param array $sections
     *
     * @param int   $mainSect
     *
     * @throws SystemException
     * @return array
     */
    public function getProductsBySections(array $sections, int $mainSect, int $iblockId) : array
    {
        $countSections = \count($sections);
        $propId        = PropertyTable::query()->setFilter(
            [
                'IBLOCK_ID' => $iblockId,
                'CODE'      => 'ITEM',
            ]
        )->setSelect(['ID'])->exec()->fetch()['ID'];
        $dataManager   = IblockPropEntityConstructor::getDataClass($iblockId);
        $query         = ElementTable::query();
        $query->registerRuntimeField(
            new ReferenceField(
                'PROP',
                $dataManager::getEntity(),
                ['=this.ID' => 'ref.IBLOCK_ELEMENT_ID']
            )
        )->registerRuntimeField(
            new ReferenceField(
                'SECTION',
                SectionElementTable::getEntity(),
                ['=this.ID' => 'ref.IBLOCK_ELEMENT_ID']
            )
        );
        $query->whereIn(
            'ID',
            SectionElementTable::query()->setSelect(
                [
                    'IBLOCK_ELEMENT_ID',
                ]
            )->registerRuntimeField(
                new ReferenceField(
                    'PARENT_SECT',
                    SectionTable::getEntity(),
                    ['=this.IBLOCK_SECTION_ID' => 'ref.ID']
                )
            )->whereIn('IBLOCK_SECTION_ID', $sections)->where('PARENT_SECT.IBLOCK_SECTION_ID', '=', $mainSect)->where(
                    Query::expr()->count('IBLOCK_ELEMENT_ID'),
                    '>=',
                    $countSections
                )->setGroup(['IBLOCK_ELEMENT_ID'])
        );
        $query->setSelect(
            [
                'ITEM' => 'PROP.PROPERTY_' . $propId,
            ]
        );
        $query->setGroup('ID');
        echo $query->getQuery();
        //$query->dump();
        
        $res     = $query->exec();
        $itemIds = [];
        while ($item = $res->fetch()) {
            $itemIds[] = $item['ID'];
        }
        
        $products = [];
        if (!empty($itemIds)) {
            $query    = new ProductQuery();
            $res      = $query->withFilter(['=ID' => $itemIds])->exec();
            $products = $res->toArray();
        }
        
        return $products;
    }
}
