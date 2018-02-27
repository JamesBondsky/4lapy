<?php

namespace FourPaws\Components;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use CBitrixComponent;
use FourPaws\BitrixOrm\Collection\CollectionBase;
use FourPaws\Catalog\Collection\ProductCollection;
use FourPaws\Catalog\Query\ProductQuery;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

/** @noinspection AutoloadingIssuesInspection */
class CatalogSaleListComponent extends CBitrixComponent
{
    const PROPERTY_SALE = 'PROPERTY_SALE';
    
    protected $filter;
    
    /**
     * @param $params
     *
     * @return array
     */
    public function onPrepareComponentParams($params): array
    {
        $params['CACHE_TYPE'] = 'N';
        if (!isset($params['CACHE_TIME'])) {
            $params['CACHE_TIME'] = 36000;
        }
        
        $params['SECTION_CODE'] = $params['SECTION_CODE'] ?? '';
        $params['SECTION_CODE'] = $params['SECTION_CODE'] ?? '';
        $params['COUNT'] = (int)$params['COUNT'] ?: 12;
        
        return parent::onPrepareComponentParams($params);
    }
    
    /**
     * {@inheritdoc}
     */
    public function executeComponent()
    {
        if ($this->startResultCache()) {
            parent::executeComponent();
            
            $this->prepareResult();
            
            $this->includeComponentTemplate();
        }
    }
    
    /**
     * Set product collection
     *
     * @throws IblockNotFoundException
     */
    protected function prepareResult()
    {
        $this->prepareProductFilter();
        $this->arResult['products'] = $this->getProductList();
    }
    
    /**
     * @throws IblockNotFoundException
     */
    protected function prepareProductFilter()
    {
        $this->filter = [];
        
        if ($this->arParams['PRODUCT_FILTER']) {
            $this->filter = $this->arParams['PRODUCT_FILTER'];
        }
        
        if ($this->arParams['OFFER_FILTER'] && is_array($this->arParams['OFFER_FILTER'])) {
            $this->filter['ID'] = \CIBlockElement::SubQuery('PROPERTY_CML2_LINK',
                array_merge($this->arParams['OFFER_FILTER'], [
                        'IBLOCK_ID' => IblockUtils::getIblockId(
                            IblockType::CATALOG,
                            IblockCode::OFFERS
                        ),
                    ]
                )
            );
        }
        
        $this->filter = $this->filter ?: ['ID' => '-1'];
    }
    
    /**
     * @return CollectionBase|ProductCollection
     */
    protected function getProductList(): ProductCollection
    {
        return (new ProductQuery())->withFilter($this->filter)->withOrder(['sort' => 'asc'])->withNav(['nTopCount' => $this->arParams['COUNT']])->exec();
    }
    
    /**
     * @return ProductCollection
     */
    public function getProductCollection()
    {
        return $this->arResult['products'];
    }
}
