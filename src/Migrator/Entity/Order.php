<?php

namespace FourPaws\Migrator\Entity;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Compatible\Internals\EntityCompatibility;
use Bitrix\Sale\Compatible\OrderCompatibility;
use Bitrix\Sale\Delivery\Services\Manager as DeliveryManager;
use Bitrix\Sale\Order as SaleOrder;
use Bitrix\Sale\PaySystem\Manager;
use Bitrix\Sale\Shipment;
use ErrorException;
use Exception;
use FourPaws\Migrator\Client\Catalog;
use FourPaws\Migrator\Client\Delivery;
use FourPaws\Migrator\Client\OrderProperty;
use FourPaws\Migrator\Client\User;
use FourPaws\Migrator\Entity\Exceptions\AddException;
use FourPaws\Migrator\Entity\Exceptions\UpdateException;
use FourPaws\Migrator\Utils;
use FourPaws\SaleBundle\Discount\Utils\Manager as DiscountManager;
use FourPaws\SaleBundle\EventController\Event as SaleEvent;
use FourPaws\SapBundle\EventController\Event;

/**
 * Class Order
 *
 * @package FourPaws\Migrator\Entity
 */
class Order extends AbstractEntity
{
    protected $propertyMap;

    /**
     * @return string
     */
    public function getTimestamp(): string
    {
        return 'DATE_UPDATE';
    }

    /**
     * Order constructor.
     *
     * @param string $entity
     *
     * @throws ArgumentException
     */
    public function __construct($entity)
    {
        DiscountManager::disableExtendsDiscount();
        Event::disableEvents();
        SaleEvent::disableEvents();
        $this->propertyMap = MapTable::getFullMapByEntity(OrderProperty::ENTITY_NAME);

        parent::__construct($entity);
    }

    /**
     * @return array
     */
    public function setDefaults(): array
    {
        /**
         * У нас нет заказов по умолчанию
         */

        return [];
    }

    /**
     * @param string $primary
     * @param array $data
     *
     * @return AddResult
     *
     * @throws ObjectException
     * @throws NotImplementedException
     * @throws AddException
     * @throws ObjectNotFoundException
     * @throws ArgumentException
     * @throws ArgumentOutOfRangeException
     * @throws ArgumentNullException
     * @throws ArgumentTypeException
     * @throws SystemException
     * @throws NotSupportedException
     * @throws ErrorException
     * @throws Exception
     */
    public function addItem(string $primary, array $data): AddResult
    {
        $userId = MapTable::getInternalIdByExternalId($data['USER_ID'], User::ENTITY_NAME);

        if (!$userId) {
            throw new AddException(sprintf('User with external id #%s is not found.', $data['USER_ID']));
        }

        $order = SaleOrder::create(SITE_ID, $userId, $data['CURRENCY']);
        $this->prepareOrder($data, $order);
        $order->doFinalAction();
        $result = $order->save();

        if (!$result->isSuccess()) {
            throw new AddException(sprintf('Order with primary %s add errors: %s.',
                $primary,
                implode(', ', $result->getErrorMessages())));
        }

        MapTable::addEntity($this->entity, $primary, $result->getId());

        return new AddResult(true, $result->getId());
    }

    /**
     * @param string $primary
     * @param array $data
     *
     * @return UpdateResult
     *
     * @throws Exception
     */
    public function updateItem(string $primary, array $data): UpdateResult
    {
        $order = SaleOrder::load($primary);

        if (null === $order) {
            throw new UpdateException(sprintf('Order with primary %s is not found.',
                $primary));
        }

        $this->prepareOrder($data, $order);
        $result = $order->save();

        if (!$result->isSuccess()) {
            throw new UpdateException(sprintf('Order with primary %s update errors: %s.',
                $primary,
                implode(', ', $result->getErrorMessages())));
        }

        return new UpdateResult($result->isSuccess(), $primary);
    }

    /**
     * @param string $field
     * @param string $primary
     * @param        $value
     *
     * @return UpdateResult
     *
     * @throws UpdateException
     * @throws Exception
     */
    public function setFieldValue(string $field, string $primary, $value): UpdateResult
    {
        throw new UpdateException('Order fields is not updated.');
    }

    /**
     * @param array $rawData
     *
     * @return array
     */
    protected function prepareFullData(array $rawData): array
    {
        return EntityCompatibility::convertDateFields($rawData);
    }

    /**
     * @param array $rawData
     *
     * @return array
     */
    protected function prepareOrderData(array $rawData): array
    {
        $rawData = OrderCompatibility::convertDateFields($rawData, Utils::getOdrerDateFields());

        foreach ($rawData['PROPERTY_VALUES'] as $property) {
            if ((int)$property['ORDER_PROPS_ID'] === 42) {
                $rawData['COMMENTS'] .= ' ' . $property['VALUE'];

                break;
            }
        }

        $filter = function ($key) {
            return \in_array($key, SaleOrder::getAvailableFields(), true);
        };

        return \array_filter($rawData, $filter, ARRAY_FILTER_USE_KEY);
    }

    /**
     * @param array $rawData
     * @param SaleOrder $order
     *
     * @return SaleOrder
     *
     * @throws ObjectNotFoundException
     * @throws ArgumentException
     * @throws ArgumentOutOfRangeException
     * @throws ArgumentNullException
     * @throws ArgumentTypeException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws SystemException
     * @throws AddException
     * @throws ErrorException
     * @throws Exception
     */
    protected function prepareOrder(array $rawData, SaleOrder $order): SaleOrder
    {
        unset($rawData['USER_ID'], $rawData['FUSER_ID']);

        $order->setPersonTypeId($rawData['PERSON_TYPE_ID']);

        $this->addBasketToOrder($rawData['BASKET'] ?? [], $order);
        $this->addPaymentToOrder($rawData, $order);
        $this->addDeliveryToOrder($rawData, $order);
        $this->addPropertiesToOrder($rawData, $order);
        $order->setFieldsNoDemand($this->prepareOrderData($rawData));

        return $order;
    }

    /**
     * @param array $rawBasketList
     * @param SaleOrder $order
     *
     * @return SaleOrder
     *
     * @throws SystemException
     * @throws ArgumentException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws AddException
     * @throws Exception
     */
    protected function addBasketToOrder(array $rawBasketList, SaleOrder $order): SaleOrder
    {
        $basket = $order->getBasket();

        if (null === $basket) {
            $basket = Basket::create(SITE_ID);
        }

        $userId = $order->getUserId();

        foreach ($rawBasketList as $rawBasket) {
            $productId = MapTable::getInternalIdByExternalId($rawBasket['PRODUCT_ID'], Catalog::ENTITY_NAME);

            $rawBasket['USER_ID'] = $userId;
            $rawBasket = $this->prepareBasketData($rawBasket);

            $item = BasketItem::create($basket, $rawBasket['MODULE'], $productId);
            unset($rawBasket['MODULE']);
            $item->setFields($rawBasket);

            $basket->addItem($item);
        }

        if (!$order->getId()) {
            $order->setBasket($basket);
        }

        return $order;
    }

    /**
     * @param array $properties
     * @param SaleOrder $order
     *
     * @return SaleOrder
     *
     * @throws ObjectNotFoundException
     * @throws ArgumentException
     * @throws Exception
     */
    protected function addPropertiesToOrder(array $properties, SaleOrder $order): SaleOrder
    {
        $propertyCollection = $order->getPropertyCollection();
        $propertyCollection->setValuesFromPost($this->preparePropertiesData($properties['PROPERTY_VALUES'] ?? []), []);

        return $order;
    }

    /**
     * На данный момент мы умеем работать только с простыми службами доставки
     *
     * @param array $data
     * @param SaleOrder $order
     *
     * @return SaleOrder
     *
     * @throws ArgumentException
     * @throws ArgumentOutOfRangeException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws ArgumentNullException
     * @throws ArgumentTypeException
     * @throws SystemException
     * @throws ErrorException
     * @throws Exception
     */
    protected function addDeliveryToOrder(array $data, SaleOrder $order): SaleOrder
    {
        $shipmentCollection = $order->getShipmentCollection();
        $deliveryId = MapTable::getInternalIdByExternalId($data['DELIVERY_ID'], Delivery::ENTITY_NAME);

        $service = DeliveryManager::getObjectById($deliveryId);

        /**
         * @var Shipment $shipment
         */
        $fields = [
            'DELIVERY_ID' => $deliveryId,
            'DELIVERY_NAME' => $service->getName(),
            'CURRENCY' => $data['CURRENCY'],
            'PRICE_DELIVERY' => $data['PRICE_DELIVERY'],
            'BASE_PRICE_DELIVERY' => $data['PRICE_DELIVERY'],
            'CUSTOM_PRICE_DELIVERY' => 'Y',
            'TRACKING_NUMBER' => $data['TRACKING_NUMBER'],
        ];

        if ($shipmentCollection->count() < 2) {
            $shipment = $shipmentCollection->createItem();
            $shipment->setFields($fields);

            $shipmentItemCollection = $shipment->getShipmentItemCollection();

            foreach ($order->getBasket() as $item) {
                $shipmentItemCollection->createItem($item)->setQuantity($item->getQuantity());
            }
        } else {
            $shipment = $shipmentCollection[0];
            $shipment->setFields($fields);

            $shipmentItemCollection = $shipment->getShipmentItemCollection();

            foreach ($order->getBasket() as $item) {
                $shipmentItemCollection->deleteByBasketItem($item);
                $shipmentItemCollection->createItem($item)->setQuantity($item->getQuantity());
            }
        }

        return $order;
    }

    /**
     * @param array $data
     * @param SaleOrder $order
     *
     * @return SaleOrder
     *
     * @throws ArgumentException
     * @throws ArgumentOutOfRangeException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws Exception
     */
    protected function addPaymentToOrder(array $data, SaleOrder $order): SaleOrder
    {
        if (!$data['PAY_SYSTEM_ID']) {
            return $order;
        }

        $service = Manager::getObjectById($data['PAY_SYSTEM_ID']);

        $paymentCollection = $order->getPaymentCollection();

        $item = $paymentCollection->count() < 1 ? $paymentCollection->createItem() : $paymentCollection[0];

        $item->setFields([
            'SUM' => $order->getPrice() + $order->getDeliveryPrice(),
            'PAY_SYSTEM_NAME' => $service->getField('NAME'),
            'PAY_SYSTEM_ID' => $data['PAY_SYSTEM_ID'],
        ]);

        $item->setPaid($data['PAYED']);

        return $order;
    }

    /**
     * @todo интервал в зависимости от зоны.
     *
     * @param string $intervalId
     *
     * @return string
     */
    protected function getDeliveryInterval(string $intervalId): string
    {
        switch ($intervalId) {
            case 0:
                return '08:00-12:00';
            case 1:
                return '12:00-16:00';
            case 2:
                return '16:00-20:00';
            case 3:
                return '20:00-24:00';
            default:
                return '00:00-23:30';
        }
    }

    /**
     * @param array $data
     *
     * @return array
     *
     * @throws ArgumentException
     */
    protected function prepareBasketData(array $data): array
    {
        $fields = [
            'PRICE',
            'PRODUCT_ID',
            'PRICE',
            'CURRENCY',
            'WEIGHT',
            'QUANTITY',
            'NAME',
            'DETAIL_PAGE_URL',
            'PRODUCT_XML_ID',
            'DISCOUNT_NAME',
            'DISCOUNT_VALUE',
            'VAT_RATE',
            'DIMENSIONS',
            'MODULE',
        ];

        $filter = function ($key) use ($fields) {
            return \in_array($key, $fields, true);
        };

        $data = array_filter($data, $filter, ARRAY_FILTER_USE_KEY);
        $data['CUSTOM_PRICE'] = 'Y';

        return $data;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    protected function preparePropertiesData(array $data): array
    {
        array_walk($data,
            function (&$rawProperty) {
                $rawProperty['ORDER_PROPS_ID'] = $this->propertyMap[$rawProperty['ORDER_PROPS_ID']];
            });

        $data = \array_combine(\array_column($data, 'ORDER_PROPS_ID'), \array_column($data, 'VALUE'));
        unset($data[null]);

        $data[13] = $this->getDeliveryInterval($data[13] ?? '');

        return $data;
    }
}
