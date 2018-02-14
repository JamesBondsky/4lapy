<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\FoodSelectionBundle\Service;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Bitrix\Main\SystemException;
use FourPaws\BitrixOrm\Model\IblockSect;
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
    
    private $iblockId;
    
    /**
     * FoodSelectionService constructor.
     *
     * @param FoodSelectionRepository $foodSelectionRepository
     *
     * @throws IblockNotFoundException
     */
    public function __construct(FoodSelectionRepository $foodSelectionRepository)
    {
        $this->foodSelectionRepository = $foodSelectionRepository;
        $this->iblockId                = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::FOOD_SELECTION);
    }
    
    /**
     * @param array $params
     *
     * @return array
     */
    public function getItems(array $params = []) : array
    {
        if (!isset($params['filter']['IBLOCK_ID'])) {
            $params['filter']['IBLOCK_ID'] = $this->iblockId;
        }
        
        return $this->foodSelectionRepository->getItems($params);
    }
    
    /**
     * @param int $parentSectionID
     *
     * @return array
     */
    public function getSectionsByParentSectionId(
        int $parentSectionID
    ) : array
    {
        $filter = ['=SECTION_ID' => $parentSectionID];
        
        return $this->getSections(['filter' => $filter]);
    }
    
    /**
     * @param array $params
     *
     * @return array
     */
    public function getSections(array $params = []) : array
    {
        if (!isset($params['filter']['IBLOCK_ID'])) {
            $params['filter']['IBLOCK_ID'] = $this->iblockId;
        }
        
        return $this->foodSelectionRepository->getSections($params);
    }
    
    public function getSectionIdByXmlId(string $xmlId, int $depthLvl = 0) : int
    {
        $filter = ['XML_ID' => $xmlId];
        if($depthLvl > 0){
            $filter['DEPTH_LEVEL'] = $depthLvl;
        }
        $items = $this->getSections(['filter' => $filter]);
        if (!empty($items)) {
            /** @var IblockSect $item */
            $item = current($items);
            
            return $item->getId();
        }
        
        return 0;
    }

    /**
     * @param array $sections
     *
     * @param array $exceptionItems
     *
     * @return array
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     */
    public function getProductsBySections(array $sections, array $exceptionItems = []) : array
    {
        return $this->foodSelectionRepository->getProductsBySections($sections, $this->iblockId, $exceptionItems);
    }
}
