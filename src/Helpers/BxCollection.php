<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 06.03.18
 * Time: 21:05
 */

namespace FourPaws\Helpers;


use Bitrix\Sale\Internals\CollectionBase;
use Bitrix\Sale\PropertyValue;
use Bitrix\Sale\Shipment;
use Bitrix\Sale\ShipmentCollection;

/**
 * Class BxCollection
 *
 * @package FourPaws\Helpers
 */
class BxCollection
{
    /**
     * @param CollectionBase $collection
     *
     * @param callable $filter
     * @return array
     */
    public static function filterCollection(CollectionBase $collection, callable $filter): array
    {
        return \array_filter($collection->getIterator()->getArrayCopy(), $filter);
    }

    /**
     * @param CollectionBase $collection
     * @param string $code
     *
     * @return PropertyValue
     */
    public static function getOrderPropertyByCode(CollectionBase $collection, string $code): ?PropertyValue
    {
        $filtered = self::filterCollection($collection, function ($value) use ($code) {
            /**
             * @var PropertyValue $value
             */
            return $value->getField('CODE') === $code;
        });
        
        return \current($filtered) ?: null;
    }

    /**
     * Возвращает !!!Первую не системную отгрузку
     *
     * @param ShipmentCollection $collection
     *
     * @return Shipment
     */
    public static function getOrderExternalShipment(ShipmentCollection $collection): ?Shipment
    {
        $filtered = self::filterCollection($collection, function ($shipment) {
            /**
             * @var Shipment $shipment
             */
            return !$shipment->isSystem();
        });

        return \current($filtered);
    }
}
