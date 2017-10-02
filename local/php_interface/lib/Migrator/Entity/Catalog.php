<?php

namespace FourPaws\Migrator\Entity;

use Bitrix\Iblock\SectionTable;
use FourPaws\Migrator\Utils;

/**
 * Class Catalog
 *
 * @package FourPaws\Migrator\Entity
 */
class Catalog extends IBlockElement
{
    const UNSORTED_SECTION_CODE = 'unsorted';
    
    private $catalogId = 0;
    
    public function setDefaults()
    {
        /**
         * У нас нет значений по умолчанию для этой сущности
         */
        return;
    }
    
    /**
     * Catalog constructor.
     *
     * @param string $entity
     * @param int    $iblockId
     */
    public function __construct($entity, $iblockId = 0)
    {
        if (!$iblockId) {
            $iblockId        = Utils::getIblockId('catalog', 'offers');
            $this->catalogId = Utils::getIblockId('catalog', 'products');
        }
        
        parent::__construct($entity, $iblockId);
    }
    
    public function isMainProduct(array $data) : bool
    {
        $result = [];
        
        /**
         * @todo implement this
         */
        
        return !!$result;
    }
    
    public function addItem(string $primary, array $data) : AddResult
    {
        if ($this->isMainProduct($data)) {
            $mainProductData              = array_diff_key($data, ['CATALOG' => null]);
            $mainProductData['IBLOCK_ID'] = $this->catalogId;
            
            $mainProductResult = parent::addItem('main_' . $primary, $data);
        }
        
        $result = parent::addItem($primary, $data);
        
        if ($this->isMainProduct($data)) {
            $this->addSku($mainProductResult->getInternalId(), $this->getSkuListFromData($data));
        }
        
        return $result;
    }
    
    /**
     * @param string $primary
     * @param array  $data
     *
     * @return \FourPaws\Migrator\Entity\UpdateResult
     */
    public function updateItem(string $primary, array $data) : UpdateResult
    {
        if ($this->isMainProduct($data)) {
            $mainProductData                      = array_diff_key($data, ['CATALOG' => null]);
            $mainProductData['IBLOCK_ID']         = $this->catalogId;
            $mainProductData['IBLOCK_SECTION_ID'] = $this->getUnsortedSectionIdByCode();

            if ($mainProductData['PROPERTY_COMMON_NAME']) {
                $mainProductData['NAME'] = $mainProductData['PROPERTY_COMMON_NAME'];
            }
            
            parent::updateItem('main_' . $primary, $data);
        }
        
        return parent::updateItem($primary, $data);
    }
    
    /**
     * @param int   $productId
     * @param array $skuList
     */
    public function addSku(int $productId, array $skuList)
    {
        /**
         * @todo implement this
         */
    }
    
    /**
     * @param array $data
     *
     * @return array
     */
    public function getSkuListFromData(array $data) : array
    {
        $result = [];
        
        /**
         * @todo implement this
         */
        
        return $result;
    }
    
    /**
     * @return int
     */
    public function getUnsortedSectionIdByCode()
    {
        static $sectionId;
        
        if (!$sectionId) {
            $sectionId = (SectionTable::getList([
                                                    'filter' => [
                                                        'CODE'      => self::UNSORTED_SECTION_CODE,
                                                        'IBLOCK_ID' => $this->catalogId,
                                                    ],
                                                    'select' => ['ID'],
                                                ])->fetch())['ID'];
        }
        
        return (int)$sectionId;
    }
}