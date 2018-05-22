<?php

namespace FourPaws\Migrator\Entity;

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\SectionTable;
use Bitrix\Main\Entity\Query;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\Migrator\Entity\Exceptions\AddException;
use FourPaws\Migrator\IblockNotFoundException;
use FourPaws\Migrator\Utils;

/**
 * Class Catalog
 *
 * @package FourPaws\Migrator\Entity
 */
class Catalog extends IBlockElement
{
    public const UNSORTED_SECTION_CODE = 'unsorted';

    public const PROPERTY_SKU_LIST_KEY = 'GOODS_AND_SIZES';

    public const IS_MAIN_PRODUCT_KEY   = 'ALPHA_PRODUCT';
    
    private $catalogId = 0;

    /**
     * @return array
     */
    public function setDefaults() : array
    {
        return [];
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
     * @throws \FourPaws\Migrator\Entity\Exceptions\UpdateException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Exception
     * @throws \InvalidArgumentException
     * @throws \Bitrix\Main\ArgumentException
     *
     * @return \FourPaws\Migrator\Entity\AddResult
     */
    public function addItem(string $primary, array $data) : AddResult
    {
        /**
         * @var array $product
         */
        $product = (new Query(ElementTable::class))
            ->setSelect(['ID'])
            ->setLimit(1)
            ->setFilter(['IBLOCK_ID' => IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::OFFERS), 'XML_ID' => $data['XML_ID']])
            ->exec()
            ->fetch();

        $primary = $product['ID'];
        $result = $this->updateItem($primary, $data);
        return new AddResult($result->getResult(), $result->getInternalId());

        throw new AddException(\sprintf('IBlock %s element product #%s update error: element is not found',
            $this->getIblockId(),
            $primary));

        $mainProductResult = null;
        
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
     * @param array $data
     *
     * @throws \Bitrix\Main\SystemException
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
        $mainProductId = 0;
    
        /**
         * Close all (exclude refs) from export
         */
        $data = array_diff_key($data,
                               [
                                   'ID'                 => null,
                                   'NAME'               => null,
                                   'ACTIVE'             => null,
                                   'SORT'               => null,
                                   'DATE_CREATE'        => null,
                                   'CREATED_BY'         => null,
                                   'TIMESTAMP_X'        => null,
                                   'MODIFIED_BY'        => null,
                                   'CODE'               => null,
                                   'TAGS'               => null,
                                   'SHOW_COUNTER'       => null,
                                   'SHOW_COUNTER_START' => null,
                                   'DETAIL_PICTURE'     => null,
                               ]);
        
        if (false && $this->isMainProduct($data)) {
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
                                                  'XML_ID'            => null,
                                              ]);
            
            unset($mainProductData['PROPERTY_VALUES']['IMG']);
            
            if ($mainProductData['PROPERTY_COMMON_NAME']) {
                $mainProductData['NAME'] = $mainProductData['PROPERTY_COMMON_NAME'];
            }
            
            $mainProductResult                    = parent::updateItem($mainProductId, $mainProductData);
            $data['PROPERTY_VALUES']['CML2_LINK'] = $mainProductResult->getInternalId();
        }

        /**
         * @var array $product
         */
        $product = (new Query(ElementTable::class))
            ->setSelect(['ID'])
            ->setLimit(1)
            ->setFilter(['IBLOCK_ID' => IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::OFFERS), 'XML_ID' => $data['XML_ID']])
            ->exec()
            ->fetch();

        $primary = $product['ID'];
        $result = parent::updateItem($primary, $data);
        
        /* if ($mainProductId) {
            $this->addSku($mainProductId, $this->getSkuListFromData($data));
        } */
        
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
