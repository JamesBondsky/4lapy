<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\FoodSelectionBundle\Service;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\FoodSelectionBundle\Repository\FoodSelectionRepository;

/**
 * Class FoodSelectionService
 *
 * @package FourPaws\FoodSelectionBundle\Service
 */
class FoodSelectionService
{
    /**
     * @var FoodSelectionRepository
     */
    private $foodSelectionRepository;
    
    /**
     * FoodSelectionService constructor.
     *
     * @param FoodSelectionRepository $foodSelectionRepository
     *
     */
    public function __construct(FoodSelectionRepository $foodSelectionRepository)
    {
        $this->foodSelectionRepository = $foodSelectionRepository;
    }
    
    /**
     * @param array $params
     *
     * @return array
     * @throws IblockNotFoundException
     */
    public function getItems(array $params = []) : array
    {
        if (!isset($params['filter']['IBLOCK_ID'])) {
            $params['filter']['IBLOCK_ID'] = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::FOOD_SELECTION);
        }
        return $this->foodSelectionRepository->getItems($params);
    }
    
    /**
     * @param array $params
     *
     * @return array
     * @throws IblockNotFoundException
     */
    public function getSections(array $params = []) : array
    {
        if (!isset($params['filter']['IBLOCK_ID'])) {
            $params['filter']['IBLOCK_ID'] = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::FOOD_SELECTION);
        }
        
        return $this->foodSelectionRepository->getSections($params);
    }
}
