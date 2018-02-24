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
    
    /**
     * @param $params
     *
     * @return array
     */
    public function onPrepareComponentParams($params): array
    {
        $params['CACHE_TYPE']='N';
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
        $this->arResult['products'] = $this->getProductList();
    }
    
    /**
     * @return CollectionBase|ProductCollection
     *
     * @throws IblockNotFoundException
     */
    protected function getProductList(): ProductCollection
    {
        $subquery = \CIBlockElement::SubQuery('PROPERTY_CML2_LINK', [
            '!PROPERTY_IS_SALE' => false,
            'IBLOCK_ID'      => IblockUtils::getIblockId(
                IblockType::CATALOG,
                IblockCode::OFFERS
            ),
        ]);
        
        return (new ProductQuery())->withFilter(['ID' => $subquery])->withOrder(['sort' => 'asc'])->withNav(['nTopCount' => $this->arParams['COUNT']])->exec();
    }
    
    /**
     * @return ProductCollection
     */
    public function getProductCollection()
    {
        return $this->arResult['products'];
    }
}
