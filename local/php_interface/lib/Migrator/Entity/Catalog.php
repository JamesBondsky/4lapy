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
    
    const PROPERTY_SKU_LIST_KEY = 'GOODS_AND_SIZES';
    
    const IS_MAIN_PRODUCT_KEY   = 'ALPHA_PRODUCT';
    
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
    
    /**
     * Мы считаем основным товаром тот, у которого:
     * - поле "Основной продукт" === Y
     * - ИЛИ поле "Связанные товары и размеры" не заполнено (в этом случае - т.к. единственное предложение)
     *
     * @param array $data
     *
     * @return bool
     */
    public function isMainProduct(array $data) : bool
    {
        return $data['PROPERTY_VALUES'][self::IS_MAIN_PRODUCT_KEY] === 'Y'
               || empty($data['PROPERTY_VALUES'][self::PROPERTY_SKU_LIST_KEY]);
    }
    
    /**
     * @param string $primary
     * @param array  $data
     *
     * @return \FourPaws\Migrator\Entity\AddResult
     */
    public function addMainProduct(string $primary, array $data) : AddResult
    {
        $data = array_diff_key($data,
                               [
                                   'CATALOG' => null,
                               ]);
        
        unset($data['PROPERTY_VALUES']['IMG']);
        
        $data['IBLOCK_SECTION_ID'] = $this->getUnsortedSectionIdByCode();
        
        return parent::addItem('main_' . $primary, $data);
    }
    
    /**
     * @param string $primary
     * @param array  $data
     *
     * @return \FourPaws\Migrator\Entity\AddResult
     */
    public function addItem(string $primary, array $data) : AddResult
    {
        if ($this->isMainProduct($data)) {
            $mainProductResult                    = $this->addMainProduct($primary, $data);
            $data['PROPERTY_VALUES']['CML2_LINK'] = $mainProductResult->getInternalId();
        } else {
            $data['PROPERTY_VALUES']['CML2_LINK'] = $this->findMainProductInternalId($this->getSkuListFromData($data));
        }
        
        $result = parent::addItem($primary, $data);
        
        if ($mainProductResult) {
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
            /**
             * @TODO переписать на один запрос
             */
            $mainProductId = MapTable::getInternalIdByExternalId('main_' . MapTable::getExternalIdByInternalId($primary,
                                                                                                               $this->entity),
                                                                 $this->entity);
            
            $mainProductData = array_diff_key($data,
                                              [
                                                  'CATALOG'           => null,
                                                  'IBLOCK_SECTION_ID' => null,
                                                  'IBLOCK_ID'         => null,
                                              ]);
            
            unset($mainProductData['PROPERTY_VALUES']['IMG']);
            
            if ($mainProductData['PROPERTY_COMMON_NAME']) {
                $mainProductData['NAME'] = $mainProductData['PROPERTY_COMMON_NAME'];
            }
            
            $mainProductResult                    = parent::updateItem($mainProductId, $mainProductData);
            $data['PROPERTY_VALUES']['CML2_LINK'] = $mainProductResult->getInternalId();
        }
        
        $result = parent::updateItem($primary, $data);
        
        if ($mainProductId) {
            $this->addSku($mainProductId, $this->getSkuListFromData($data));
        }
        
        return $result;
    }
    
    /**
     * @param $skuExternalIds
     *
     * @return int
     */
    public function findMainProductInternalId($skuExternalIds) : int
    {
        foreach ($skuExternalIds as &$id) {
            $id = 'main_' . $id;
        }
        
        return (int)array_shift(MapTable::getInternalIdListByExternalIdList($skuExternalIds, $this->entity));
    }
    
    /**
     * @param int   $productId
     * @param array $skuList
     */
    public function addSku(int $productId, array $skuList)
    {
        foreach ($skuList as $skuExternalId) {
            if ($skuInternalId = MapTable::getInternalIdByExternalId($skuExternalId, $this->entity)) {
                $this->setFieldValue('PROPERTY_CML2_LINK', $skuInternalId, $productId);
            }
        }
    }
    
    /**
     * @param array $data
     *
     * @return array
     */
    public function getSkuListFromData(array $data) : array
    {
        return $data['PROPERTY_VALUES'][self::PROPERTY_SKU_LIST_KEY] ?: [];
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