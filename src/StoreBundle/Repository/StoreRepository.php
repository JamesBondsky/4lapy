<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\StoreBundle\Repository;

use Adv\Bitrixtools\Tools\HLBlock\HLBlockFactory;
use Bitrix\Catalog\StoreTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Sale\Location\LocationTable;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Entity\Store;
use JMS\Serializer\DeserializationContext;

class StoreRepository extends BaseRepository
{
    public const RADIUS_EARTH_KM = 6367;
    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * @param array    $criteria
     * @param array    $orderBy
     * @param null|int $limit
     * @param null|int $offset
     * @param bool     $useDefaultFilter
     *
     * @return StoreCollection
     * @throws ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \Exception
     */
    public function findBy(
        array $criteria = [],
        array $orderBy = [],
        int $limit = null,
        int $offset = null,
        bool $useDefaultFilter = true
    ): StoreCollection {
        if (empty($orderBy)) {
            $orderBy = $this->getDefaultOrder();
        }

        if ($useDefaultFilter) {
            $criteria = array_merge($this->getDefaultFilter(), $criteria);
        }

        $select = [
            '*',
            'UF_*',
        ];

        $query = StoreTable::query();

        $allKeys = array_unique(array_merge(array_keys($orderBy), array_keys($criteria)));
        /** одноуровеневая проверка логики и вставка ключей */
        if (\in_array(0, $allKeys, true)) {
            unset($allKeys[\array_search(0, $allKeys, true)]);
            foreach ($criteria as $key => $criterion) {
                if (\is_array($criterion)) {
                    /** @noinspection SlowArrayOperationsInLoopInspection */
                    $allKeys = array_merge($allKeys, array_keys($criterion));
                    unset($allKeys[\array_search('LOGIC', $allKeys, true)]);
                }
            }
        }
        $allKeys = array_unique($allKeys);

        $haveDistance = false;
        $haveLocation = false;
        $haveMetro = false;
        foreach ($allKeys as $key) {
            if (!$haveDistance && strpos($key, 'DISTANCE') !== false) {
                /**
                 * Пример DISTANCE поля:
                 * DISTANCE_55.821220_37.815770
                 * где
                 * $explode[0] - фраза "DISTANCE"
                 * GPS_N - latitude - $explode[1] - широта
                 * GPS_S - longitude - $explode[2] - долгота
                 */
                list($fieldName, $latitude, $longitude) = explode('_', $key);
                $pi180 = pi() / 180;

                $distanceExpression =
                    static::RADIUS_EARTH_KM . "*2
                    *ASIN(
                        SQRT(
                            POWER(
                                SIN(
                                    (%1\$s - ABS('$latitude'))  * $pi180 / 2
                                ), 2
                            )
                            + COS(%1\$s * $pi180)
                            * COS(ABS('$latitude') * $pi180)
                            * POWER(
                                SIN(
                                    (%2\$s - '$longitude' ) * $pi180 / 2
                                ), 2
                            )
                        )
                    )";

                if (isset($orderBy[$key])) {
                    $orderBy['DISTANCE'] = $orderBy[$key];
                    unset($orderBy[$key]);
                }
                if (isset($criteria[$key])) {
                    $criteria['DISTANCE'] = $criteria[$key];
                    unset($criteria[$key]);
                }

                $query->registerRuntimeField(
                    new ExpressionField(
                        'DISTANCE', $distanceExpression,
                        ['GPS_N', 'GPS_S']
                    )
                );
                $select[] = 'DISTANCE';
                $haveDistance = true;
            } elseif (!$haveLocation && strpos($key, 'LOCATION') !== false) {
                $query->registerRuntimeField(
                    new ReferenceField(
                        'LOCATION',
                        LocationTable::getEntity(),
                        ['=this.UF_LOCATION' => 'ref.CODE']
                    )
                );
                $haveLocation = true;
            } elseif (!$haveMetro && strpos($key, 'METRO') !== false) {
                $query->registerRuntimeField(
                    new ReferenceField(
                        'METRO',
                        HLBlockFactory::createTableObject('MetroStations')::getEntity(),
                        ['=this.UF_METRO' => 'ref.ID']
                    )
                );
                $haveMetro = true;
            }
        }

        $query->setSelect($select)
            ->setFilter($criteria)
            ->setOrder($orderBy)
            ->setLimit($limit)
            ->setOffset($offset);

        $stores = $query->exec();

        $result = [];
        while ($store = $stores->fetch()) {
            if (!isset($result[$store['ID']])) {
                $store['UF_SERVICES_SINGLE'] = [$store['UF_SERVICES_SINGLE']];
                $result[$store['ID']] = $store;
            } else {
                $result[$store['ID']]['UF_SERVICES_SINGLE'][] = $store['UF_SERVICES_SINGLE'];
            }
        }

        /**
         * todo change group name to constant
         */
        if (!empty($result)) {
            return new StoreCollection(
                $this->arrayTransformer->fromArray(
                    $result,
                    sprintf('array<%s>', Store::class),
                    DeserializationContext::create()->setGroups(['read'])
                )
            );
        }

        return new StoreCollection();
    }

    /**
     * @return array
     */
    protected function getDefaultOrder(): array
    {
        return [
            'SORT' => 'ASC',
            'ID'   => 'ASC',
        ];
    }

    /**
     * @return array
     */
    protected function getDefaultFilter(): array
    {
        return ['ACTIVE' => 'Y'];
    }

    /**
     * @return string
     */
    protected function getDataClass(): string
    {
        return StoreTable::class;
    }

    /**
     * @return string
     */
    protected function getCollectionClass(): string
    {
        return StoreCollection::class;
    }

    /**
     * @return string
     */
    protected function getEntityClass(): string
    {
        return Store::class;
    }
}
