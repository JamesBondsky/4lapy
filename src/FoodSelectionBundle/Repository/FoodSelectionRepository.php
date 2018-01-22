<?php

namespace FourPaws\FoodSelectionBundle\Repository;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\BitrixOrm\Query\IblockElementQuery;
use FourPaws\BitrixOrm\Query\IblockSectionQuery;
use FourPaws\BitrixOrm\Query\IblockSectQuery;
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
    public function getSections(array $params = []): array
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
            echo '<pre>',var_dump($res),'</pre>';
            echo '<pre>',var_dump($res->toArray()),'</pre>';
            return $res->toArray();
        } catch (IblockNotFoundException $e) {
            return [];
        }
    }
}
