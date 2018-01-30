<?php

namespace FourPaws\StoreBundle\Repository;

use Adv\Bitrixtools\Tools\HLBlock\HLBlockFactory;
use Bitrix\Catalog\StoreTable;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Sale\Location\LocationTable;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Entity\Store;
use JMS\Serializer\DeserializationContext;

class StoreRepository extends BaseRepository
{
    protected function getDataClass(): string
    {
        return StoreTable::class;
    }

    protected function getCollectionClass(): string
    {
        return StoreCollection::class;
    }

    protected function getEntityClass(): string
    {
        return Store::class;
    }

    protected function getDefaultFilter(): array
    {
        return ['ACTIVE' => 'Y'];
    }

    protected function getDefaultOrder(): array
    {
        return ['SORT' => 'ASC', 'ID' => 'ASC'];
    }
    
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
        
        $criteria = array_merge($this->getDefaultFilter(), $criteria);
        
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
}
