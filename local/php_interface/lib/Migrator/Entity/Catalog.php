<?php

namespace FourPaws\Migrator\Entity;

use Bitrix\Iblock\SectionTable;
use FourPaws\Migrator\IblockNotFoundException;
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
    }
    
    /**
     * Catalog constructor.
     *
     * @param string $entity
     * @param int    $iblockId
     *
     * @throws \FourPaws\Migrator\IblockNotFoundException
     */
    public function __construct($entity, $iblockId = 0)
    {
        if (!$iblockId) {
            try {
                $iblockId        = Utils::getIblockId('catalog', 'offers');
                $this->catalogId = Utils::getIblockId('catalog', 'products');
            } catch (\Exception $e) {
                throw new IblockNotFoundException($e->getMessage());
            }
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
     * @throws \FourPaws\Migrator\Entity\Exceptions\AddException
     * @throws \FourPaws\Migrator\Entity\Exceptions\AddProductException
     * @throws \InvalidArgumentException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Exception
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
        $data['IBLOCK_ID']         = $this->catalogId;
        
        return parent::addItem('main_' . $primary, $data);
    }
    
    /**
     * @param string $primary
     * @param array  $data
     *
     * @throws \FourPaws\Migrator\Entity\Exceptions\AddException
     * @throws \FourPaws\Migrator\Entity\Exceptions\AddProductException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Exception
     * @throws \InvalidArgumentException
     * @throws \Bitrix\Main\ArgumentException
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
     * @throws \FourPaws\Migrator\Entity\Exceptions\UpdateException
     * @throws \FourPaws\Migrator\Entity\Exceptions\UpdateProductException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Exception
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
     * @throws \Bitrix\Main\ArgumentException
     *
     * @return int
     */
    public function findMainProductInternalId(array $skuExternalIds) : int
    {
        foreach ($skuExternalIds as &$id) {
            $id = 'main_' . $id;
        }

        unset($id);
        
        $result = MapTable::getInternalIdListByExternalIdList($skuExternalIds, $this->entity);
        
        return (int)array_shift($result);
    }
    
    /**
     * @param int   $productId
     * @param array $skuList
     *
     * @throws \Bitrix\Main\ArgumentException
     * @throws \FourPaws\Migrator\Entity\Exceptions\UpdateException
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
     *
     * @throws \Bitrix\Main\ArgumentException
     */
    public function getUnsortedSectionIdByCode() : int
    {
        static $sectionId;
        
        if (!$sectionId) {
            $sectionId = SectionTable::getList([
                                                   'filter' => [
                                                       'CODE'      => self::UNSORTED_SECTION_CODE,
                                                       'IBLOCK_ID' => $this->catalogId,
                                                   ],
                                                   'select' => ['ID'],
                                               ])->fetch()['ID'];
        }
        
        return (int)$sectionId;
    }
}