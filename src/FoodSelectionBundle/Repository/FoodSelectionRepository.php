<?php

namespace FourPaws\FoodSelectionBundle\Repository;

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\BitrixOrm\Query\IblockElementQuery;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

/**
 * Class FoodSelectionRepository
 *
 * @package FourPaws\FoodSelectionBundle\Repository
 */
class FoodSelectionRepository
{
    public function findBy(array $params = []) : array
    {
       $query = new IblockElementQuery(IblockUtils::getIblockId(IblockType::CATALOG,
                                                                IblockCode::FOOD_SELECTION));
        $query->exec();
    }
}
