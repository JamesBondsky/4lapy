<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\FoodSelectionBundle\Service;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\HLBlock\HLBlockFactory;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Iblock\ElementTable;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\FoodSelectionBundle\Repository\FoodSelectionRepository;
use FourPaws\Helpers\HighloadHelper;
use FourPaws\Migrator\Entity\HighloadBlock;

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
     * @param string $xmlId
     * @param int    $parentSectionID
     * @param int    $depthLvl
     *
     * @return array
     * @throws IblockNotFoundException
     */
    public function getSectionsByXmlIdAndParentSection(string $xmlId, int $parentSectionID = 0, int $depthLvl = 0) : array
    {
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
     * @throws IblockNotFoundException
     */
    public function getSections(array $params = []) : array
    {
        if (!isset($params['filter']['IBLOCK_ID'])) {
            $params['filter']['IBLOCK_ID'] = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::FOOD_SELECTION);
        }
        
        return $this->foodSelectionRepository->getSections($params);
    }
    
    public function getItemsBySections(array $sections)
    {
        //$tableData = []
        HighloadBlockTable::compileEntity($tableData);
        //$query=ElementTable::query();
        //$query->registerRuntimeField(new \Bitrix\Main\Entity\ReferenceField(
        //    $REF_NAME,
        //    ElementP::getEntity(),
        //    ['=this.' => 'ref.']
        //))
    }
}
