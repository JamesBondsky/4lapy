<?php

namespace FourPaws\Migrator\Entity;

use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Compatible\Internals\EntityCompatibility;
use Bitrix\Sale\Compatible\OrderCompatibility;
use Bitrix\Sale\Delivery\Services\Manager as DeliveryManager;
use Bitrix\Sale\Order as SaleOrder;
use Bitrix\Sale\PaySystem\Manager;
use FourPaws\Migrator\Client\Catalog;
use FourPaws\Migrator\Client\Delivery;
use FourPaws\Migrator\Client\OrderProperty;
use FourPaws\Migrator\Client\User;
use FourPaws\Migrator\Entity\Exceptions\AddException;
use FourPaws\Migrator\Entity\Exceptions\UpdateException;
use FourPaws\Migrator\Utils;

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
    public function getTimestamp() : string
    {
        return 'DATE_UPDATE';
    }
    
    /**
     * Order constructor.
     *
     * @param string $entity
     *
     * @throws \Bitrix\Main\ArgumentException
     */
    public function __construct($entity)
    {
        $this->propertyMap = MapTable::getFullMapByEntity(OrderProperty::ENTITY_NAME);
        
        parent::__construct($entity);
    }
    
    public function setDefaults() : array
    {
        /**
         * У нас нет заказов по умолчанию
         */
        
        return [];
    }
    
    /**
     * @param string $primary
     * @param array  $data
     *
     * @return \FourPaws\Migrator\Entity\AddResult
     *
     * @throws \FourPaws\Migrator\Entity\Exceptions\AddException
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentTypeException
     * @throws \Bitrix\Main\SystemException
     * @throws \Bitrix\Main\NotSupportedException
     * @throws \ErrorException
     * @throws \Exception
     */
    public function addItem(string $primary, array $data) : AddResult
    {
        $userId = MapTable::getInternalIdByExternalId($data['USER_ID'], User::ENTITY_NAME);
        
        if (!$userId) {
            throw new AddException(sprintf('User with external id #%s is not found.', $data['USER_ID']));
        }
        
        $order = SaleOrder::create(SITE_ID, $userId, $data['CURRENCY']);
        $this->_prepareOrder($data, $order);
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
     * @param array  $data
     *
     * @return \FourPaws\Migrator\Entity\UpdateResult
     *
     * @throws \Exception
     */
    public function updateItem(string $primary, array $data) : UpdateResult
    {
        $order = SaleOrder::load($primary);
        
        if (null === $order) {
            throw new UpdateException(sprintf('Order with primary %s is not found.',
                                              $primary));
        }
        
        $this->_prepareOrder($data, $order);
        $order->doFinalAction();
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
     * @return \FourPaws\Migrator\Entity\UpdateResult
     *
     * @throws \FourPaws\Migrator\Entity\Exceptions\UpdateException
     * @throws \Exception
     */
    public function setFieldValue(string $field, string $primary, $value) : UpdateResult
    {
        throw new UpdateException('Order fields is not updated.');
    }
    
    /**
     * @param array $rawData
     *
     * @return array
     */
    protected function prepareFullData(array $rawData) : array
    {
        return EntityCompatibility::convertDateFields($rawData);
    }
    
    /**
     * @param array $rawData
     *
     * @return array
     */
    protected function _prepareOrderData(array $rawData) : array
    {
        $rawData = OrderCompatibility::convertDateFields($rawData, Utils::getOdrerDateFields());
        
        foreach ($rawData['PROPERTY_VALUES'] as $property) {
            if ((int)$property['ORDER_PROPS_ID'] === 42) {
                $rawData['COMMENTS'] .= ' ' . $property['VALUE'];
                
                break;
            }
        }
        
        $filter = function ($key) {
            return in_array($key, SaleOrder::getAvailableFields(), true);
        };
        
        return array_filter($rawData, $filter, ARRAY_FILTER_USE_KEY);
    }
    
    /**
     * @param array              $rawData
     * @param \Bitrix\Sale\Order $order
     *
     * @return \Bitrix\Sale\Order
     *
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentTypeException
     * @throws \Bitrix\Main\NotSupportedException
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\Migrator\Entity\Exceptions\AddException
     * @throws \ErrorException
     * @throws \Exception
     */
    protected function _prepareOrder(array $rawData, SaleOrder $order) : SaleOrder
    {
        unset($rawData['USER_ID']);
        
        $order->setMathActionOnly(true);
        $order->setPersonTypeId($rawData['PERSON_TYPE_ID']);
        
        $this->_addBasketToOrder($rawData['BASKET'] ?? [], $order);
        $this->_addPaymentToOrder($rawData, $order);
        $this->_addDeliveryToOrder($rawData, $order);
        $this->_addPropertiesToOrder($rawData, $order);
        $order->setFieldsNoDemand($this->_prepareOrderData($rawData));
        
        return $order;
    }
    
    /**
     * @param array              $rawBasketList
     * @param \Bitrix\Sale\Order $order
     *
     * @return \Bitrix\Sale\Order
     *
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\NotSupportedException
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \FourPaws\Migrator\Entity\Exceptions\AddException
     * @throws \Exception
     */
    protected function _addBasketToOrder(array $rawBasketList, SaleOrder $order) : SaleOrder
    {
        $basket = Basket::loadItemsForOrder($order);
        $userId = $order->getUserId();
        
        foreach ($rawBasketList as $rawBasket) {
            $productId = MapTable::getInternalIdByExternalId($rawBasket['PRODUCT_ID'], Catalog::ENTITY_NAME);
            
            $rawBasket['USER_ID'] = $userId;
            $rawBasket            = $this->_prepareBasketData($rawBasket);
            
            if ($item = $basket->getExistsItem($rawBasket['MODULE'], $productId)) {
                $item->setFieldsNoDemand($rawBasket);
                $item->save();
            } else {
                $item = BasketItem::create($basket, $rawBasket['MODULE'], $productId);
                $item->setFieldsNoDemand($rawBasket);
                $result = $item->save();
                
                if (!$result->isSuccess()) {
                    throw new AddException(sprintf('Basket product #%s add error: %s',
                                                   $productId,
                                                   implode(', ', $result->getErrorMessages())));
                }
                
                $basket->addItem($item);
            }
        }
        
        if (!$order->getId()) {
            $order->setBasket($basket);
        }
        
        $basket->save();
        
        return $order;
    }
    
    /**
     * @param array              $properties
     * @param \Bitrix\Sale\Order $order
     *
     * @return \Bitrix\Sale\Order
     *
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Exception
     */
    protected function _addPropertiesToOrder(array $properties, SaleOrder $order) : SaleOrder
    {
        $propertyCollection = $order->getPropertyCollection();
        $propertyCollection->setValuesFromPost($this->_preparePropertiesData($properties['PROPERTY_VALUES'] ?? []), []);
        $propertyCollection->save();
        
        return $order;
    }
    
    /**
     * На данный момент мы умеем работать только с простыми службами доставки
     *
     * @param array              $data
     * @param \Bitrix\Sale\Order $order
     *
     * @return \Bitrix\Sale\Order
     *
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotSupportedException
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentTypeException
     * @throws \Bitrix\Main\SystemException
     * @throws \ErrorException
     * @throws \Exception
     */
    protected function _addDeliveryToOrder(array $data, SaleOrder $order) : SaleOrder
    {
        $shipmentCollection = $order->getShipmentCollection();
        $deliveryId         = MapTable::getInternalIdByExternalId($data['DELIVERY_ID'], Delivery::ENTITY_NAME);
        
        $service = DeliveryManager::getObjectById($deliveryId);
        
        /**
         * @var \Bitrix\Sale\Shipment $shipment
         */
        $fields = [
            'DELIVERY_ID'           => $deliveryId,
            'DELIVERY_NAME'         => $service->getName(),
            'CURRENCY'              => $data['CURRENCY'],
            'PRICE_DELIVERY'        => $data['PRICE_DELIVERY'],
            'BASE_PRICE_DELIVERY'   => $data['PRICE_DELIVERY'],
            'CUSTOM_PRICE_DELIVERY' => 'Y',
            'TRACKING_NUMBER'       => $data['TRACKING_NUMBER'],
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
     * @param array              $data
     * @param \Bitrix\Sale\Order $order
     *
     * @return \Bitrix\Sale\Order
     *
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotSupportedException
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \Exception
     */
    protected function _addPaymentToOrder(array $data, SaleOrder $order) : SaleOrder
    {
        if (!$data['PAY_SYSTEM_ID']) {
            return $order;
        }
        
        $service = Manager::getObjectById($data['PAY_SYSTEM_ID']);
        
        $paymentCollection = $order->getPaymentCollection();
        
        $item = $paymentCollection->count() < 1 ? $paymentCollection->createItem() : $paymentCollection[0];
        
        $item->setFields([
                             'SUM'             => $order->getPrice() + $order->getDeliveryPrice(),
                             'PAY_SYSTEM_NAME' => $service->getField('NAME'),
                             'PAY_SYSTEM_ID'   => $data['PAY_SYSTEM_ID'],
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
    protected function _getDeliveryInterval(string $intervalId) : string
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
     * @throws \Bitrix\Main\ArgumentException
     */
    protected function _prepareBasketData(array $data) : array
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
            return in_array($key, $fields, true);
        };
        
        $data = array_filter($data, $filter, ARRAY_FILTER_USE_KEY);
        
        return $data;
    }
    
    /**
     * @param array $data
     *
     * @return array
     */
    protected function _preparePropertiesData(array $data) : array
    {
        array_walk($data,
            function (&$rawProperty) {
                $rawProperty['ORDER_PROPS_ID'] = $this->propertyMap[$rawProperty['ORDER_PROPS_ID']];
            });
        
        $data = array_combine(array_column($data, 'ORDER_PROPS_ID'), array_column($data, 'VALUE'));
        unset($data[null]);
        
        $data[13] = $this->_getDeliveryInterval($data[13] ?? '');
        
        return $data;
    }
}
