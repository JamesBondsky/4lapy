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
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use FourPaws\BitrixOrm\Model\IblockElement;
use FourPaws\BitrixOrm\Model\IblockSect;
use FourPaws\BitrixOrm\Query\IblockElementQuery;
use FourPaws\BitrixOrm\Query\IblockSectQuery;
use FourPaws\BitrixOrm\Utils\IblockPropEntityConstructor;
use FourPaws\Catalog\Model\Product;
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
     * @return array|IblockElement[]
     */
    public function getItems(array $params = []): array
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
     * @return array|IblockSect[]
     */
    public function getSections(array $params = []): array
    {
        /** @var IblockSectQuery $query */
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
     * @param int   $iblockId
     *
     * @param array $exceptionItems
     *
     * @param int   $limit
     *
     * @param array $excludeSections
     *
     * @return array|Product[]
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getProductsBySections(
        array $sections,
        int $iblockId,
        array $exceptionItems = [],
        int $limit = 6,
        array $excludeSections = []
    ): array {
        $countSections = \count($sections);

        $subQuery = SectionElementTable::query()->setSelect(['IBLOCK_ELEMENT_ID'])
            ->whereIn('IBLOCK_SECTION_ID', $sections)
            ->where(
                Query::expr()->count('IBLOCK_ELEMENT_ID'),
                '>=',
                $countSections
            )->setGroup(['IBLOCK_ELEMENT_ID']);
        if (!empty($excludeSections)) {
            $subQuery->whereNotIn('IBLOCK_SECTION_ID', $excludeSections);
        }
        $ids = [];
        $resSubQuery = $subQuery->exec();
        while ($item = $resSubQuery->fetch()){
            $ids[] = $item['IBLOCK_ELEMENT_ID'];
        }
        if(empty($ids)){
            return [];
        }

        $propId = PropertyTable::query()->setFilter(
            [
                'IBLOCK_ID' => $iblockId,
                'CODE'      => 'ITEM',
            ]
        )->setSelect(['ID'])->exec()->fetch()['ID'];
        $dataManager = IblockPropEntityConstructor::getDataClass($iblockId);

        $query = ElementTable::query();
        $query->registerRuntimeField(
            new ReferenceField(
                'PROP',
                $dataManager::getEntity(),
                ['=this.ID' => 'ref.IBLOCK_ELEMENT_ID']
            )
        );
        $query->where('ACTIVE', 'Y');
        $query->where('IBLOCK_ID', $iblockId);
        $query->whereIn(
            'ID',
            $ids
        );
        if (!empty($exceptionItems)) {
            $query->whereNotIn('PROP.PROPERTY_' . $propId, $exceptionItems);
        }
        $query->setSelect(
            [
                'ITEM' => 'PROP.PROPERTY_' . $propId,
            ]
        );
        $query->setGroup('ITEM');
        $res = $query->exec();
        $itemIds = [];
        while ($item = $res->fetch()) {
            $itemIds[] = $item['ITEM'];
        }

        $products = [];
        if (!empty($itemIds)) {
            $query = new ProductQuery();
            if ($limit > 0) {
                $query->withNav(['nTopCount' => $limit]);
            }
            $res = $query->withFilter(['=ID' => array_unique($itemIds), 'ACTIVE' => 'Y'])
                ->exec();
            $products = $res->toArray();
        }

        return $products;
    }
}
