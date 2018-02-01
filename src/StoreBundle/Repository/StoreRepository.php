<?php

namespace FourPaws\StoreBundle\Repository;

use Adv\Bitrixtools\Tools\HLBlock\HLBlockFactory;
use Bitrix\Catalog\StoreProductTable;
use Bitrix\Catalog\StoreTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\Query\Join;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Location\LocationTable;
use FourPaws\BitrixOrm\Utils\EntityConstructor;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Entity\Store;
use JMS\Serializer\DeserializationContext;

class StoreRepository extends BaseRepository
{
    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * @param array    $criteria
     * @param array    $orderBy
     * @param null|int $limit
     * @param null|int $offset
     *
     * @return StoreCollection
     * @throws \Exception
     */
    public function findBy(
        array $criteria = [],
        array $orderBy = [],
        int $limit = null,
        int $offset = null
    ) : StoreCollection
    {
        if (empty($orderBy)) {
            $orderBy = $this->getDefaultOrder();
        }
        
        if (empty($criteria)) {
            $criteria = $this->getDefaultFilter();
        }
        
        $query = StoreTable::query();
        $query->setSelect(
            [
                '*',
                'UF_*',
            ]
        )->setFilter($criteria)->setOrder($orderBy)->setLimit($limit)->setOffset($offset);
        /** @todo сделать универсальные условия */
        if (array_key_exists('LOCATION.NAME.NAME', $orderBy)) {
            $query->registerRuntimeField(
                'LOCATION',
                new ReferenceField(
                    'CATALOG', LocationTable::getEntity(), ['=this.UF_LOCATION' => 'ref.ID']
                )
            );
        }
        if (array_key_exists('METRO.UF_NAME', $orderBy) || isset($criteria[0]['%METRO.UF_NAME'])) {
            $query->registerRuntimeField(
                'METRO',
                new ReferenceField(
                    'CATALOG',
                    HLBlockFactory::createTableObject('MetroStations')::getEntity(),
                    ['=this.UF_METRO' => 'ref.ID']
                )
            );
        }
        $stores = $query->exec();
        
        $result = [];
        while ($store = $stores->fetch()) {
            $result[$store['ID']] = $store;
        }
        
        /**
         * todo change group name to constant
         */
        return new StoreCollection(
            $this->arrayTransformer->fromArray(
                $result,
                sprintf('array<%s>', Store::class),
                DeserializationContext::create()->setGroups(['read'])
            )
        );
    }
    
    protected function getDefaultOrder() : array
    {
        return [
            'SORT' => 'ASC',
            'ID'   => 'ASC',
        ];
    }
    
    protected function getDefaultFilter() : array
    {
        return ['ACTIVE' => 'Y'];
    }
    
    /**
     * @param int    $offerId
     * @param string $location
     *
     * @return StoreCollection
     * @throws ArgumentException
     * @throws SystemException
     */
    public function getAvailableProductStoresCurrentLocation(int $offerId, string $location) : StoreCollection
    {
        $query = StoreTable::query();
        $query->registerRuntimeField(
            new ReferenceField(
                'UF_FIELDS',
                EntityConstructor::compileEntityDataClass('UtsCatStores', 'b_uts_cat_store')::getEntity(),
                Join::on('this.ID', 'ref.VALUE_ID')
            )
        );
        $query->registerRuntimeField(
            new ReferenceField(
                'STORE_PRODUCTS',
                StoreProductTable::getEntity(),
                Join::on('this.ID', 'ref.STORE_ID')
            )
        );
        $query->where(
            [
                [
                    'UF_FIELDS.UF_IS_SHOP',
                    1,
                ],
                [
                    'UF_FIELDS.UF_LOCATION',
                    $location,
                ],
                [
                    'STORE_PRODUCTS.AMOUNT',
                    '>',
                    0,
                ],
                [
                    'STORE_PRODUCTS.PRODUCT_ID',
                    $offerId,
                ],
            ]
        );
        $query->setSelect(
            [
                '*',
                'OFFER_AMOUNT' => 'STORE_PRODUCTS.AMOUNT',
                'OFFER_ID'     => 'STORE_PRODUCTS.PRODUCT_ID',
            ]
        );
        
        echo $query->getQuery();
        die();
        $stores = $query->exec();
        
        $result = [];
        while ($store = $stores->fetch()) {
            $store['OFFER_ID']    = $offerId;
            $result[$store['ID']] = $store;
        }
        
        /**
         * todo change group name to constant
         */
        return new StoreCollection(
            $this->arrayTransformer->fromArray(
                $result,
                sprintf('array<%s>', Store::class),
                DeserializationContext::create()->setGroups(['read'])
            )
        );
    }
    
    protected function getDataClass() : string
    {
        return StoreTable::class;
    }
    
    protected function getCollectionClass() : string
    {
        return StoreCollection::class;
    }
    
    protected function getEntityClass() : string
    {
        return Store::class;
    }
}
