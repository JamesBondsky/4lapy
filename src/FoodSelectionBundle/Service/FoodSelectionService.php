<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\FoodSelectionBundle\Service;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Bitrix\Main\SystemException;
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
     * @param string $xmlId
     * @param int    $parentSectionID
     * @param int    $depthLvl
     *
     * @return array
     */
    public function getSectionsByXmlIdAndParentSection(
        string $xmlId,
        int $parentSectionID = 0,
        int $depthLvl = 0
    ) : array {
        $filter = ['=XML_ID' => $xmlId];
        if ($parentSectionID > 0) {
            $filter['=IBLOCK_SECTION_ID'] = $parentSectionID;
        }
        if ($depthLvl > 0) {
            $filter['=DEPTH_LEVEL'] = $depthLvl;
        }
        
        return $this->getSections(['filter' => $filter,]);
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
    
    /**
     * @param array $sections
     *
     * @param int   $mainSect
     *
     * @throws SystemException
     * @return array
     */
    public function getProductsBySections(array $sections, int $mainSect) : array
    {
        return $this->foodSelectionRepository->getProductsBySections($sections, $mainSect, $this->iblockId);
    }
}
