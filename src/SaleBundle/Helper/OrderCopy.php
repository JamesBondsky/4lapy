<?php
namespace FourPaws\SaleBundle\Helper;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Order;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PropertyValue;
use Bitrix\Sale\Shipment;
use Bitrix\Sale\ShipmentItem;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\SaleBundle\Exception\NotFoundException;
use FourPaws\SaleBundle\Exception\OrderCreateException;
use FourPaws\SaleBundle\Service\OrderService;

/**
 * Class OrderCopy
 *
 * @package FourPaws\SaleBundle\Helper
 */
class OrderCopy
{
    /** @var array */
    private $filterFields = [
        /** Копируемые поля корзины (по умолчанию) */
        'basketItemCopyFields' => [
            'SET_PARENT_ID', 'TYPE', 'PRODUCT_ID', 'PRODUCT_PRICE_ID',
            'PRICE', 'CURRENCY', 'WEIGHT', 'QUANTITY', 'LID', 'NAME', 'CALLBACK_FUNC',
            'NOTES', 'PRODUCT_PROVIDER_CLASS', 'CANCEL_CALLBACK_FUNC', 'ORDER_CALLBACK_FUNC',
            'PAY_CALLBACK_FUNC', 'DETAIL_PAGE_URL', 'CATALOG_XML_ID',
            'PRODUCT_XML_ID', 'VAT_RATE', 'MEASURE_NAME', 'MEASURE_CODE',
            'BASE_PRICE', 'VAT_INCLUDED'
        ],
        /** Исключаемые поля корзины (по умолчанию) */
        'basketItemExcludeFields' => [],
        /** Копируемые свойства корзины (по умолчанию) */
        'basketItemCopyProps' => [],
        /** Исключаемые свойства корзины (по умолчанию) */
        'basketItemExcludeProps' => [
            'CATALOG.XML_ID', 'PRODUCT.XML_ID',
        ],
        /** Копируемые свойства заказа (по умолчанию) */
        'orderCopyProps' => [],
        /** Исключаемые свойства заказа (по умолчанию) */
        'orderExcludeProps' => [
            'IS_EXPORTED',
        ],
        /** Копируемые поля заказа (по умолчанию) */
        'orderCopyFields' => [
            'USER_DESCRIPTION'
        ],
    ];
    /** @var array */
    private $filterFieldsFlipped = [];

    /** @var DeliveryService $deliveryService */
    private $deliveryService;
    /** @var Order */
    private $oldOrder;
    /** @var Order */
    private $newOrder;
    /** @var array */
    private $oldBasket2NewMap = [];
    /** @var array */
    private $propCode2PropIdMap;

    /**
     * OrderCopy constructor.
     *
     * @param int $copyOrderId
     * @throws ArgumentNullException
     * @throws NotImplementedException
     * @throws OrderCreateException
     */
    public function __construct(int $copyOrderId)
    {
        $this->oldOrder = Order::load($copyOrderId);
        if (!$this->oldOrder) {
            throw new NotFoundException('Копируемый заказ не найден', 100);
        }

        $this->newOrder = Order::create($this->oldOrder->getSiteId(), $this->oldOrder->getUserId(), $this->oldOrder->getCurrency());
        if (!$this->newOrder) {
            throw new OrderCreateException('Не удалось создать новый заказ для заполнения', 100);
        }

        // Копирование типа плательщика для нового заказа - от него зависят свойства
        $this->newOrder->setPersonTypeId(
            $this->oldOrder->getPersonTypeId()
        );
    }

    /**
     * Делает полную копию заказа
     *
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws ArgumentTypeException
     * @throws NotImplementedException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws OrderCreateException
     * @throws \Exception
     */
    public function doFullCopy()
    {
        $this->copyFields();
        $this->copyBasket();
        $this->copyProps();
        $this->copyShipments();
        $this->copyPayments();
        $this->doFinalAction();
    }

    /**
     * @return Order
     */
    public function getOldOrder(): Order
    {
        return $this->oldOrder;
    }

    /**
     * @return Order
     */
    public function getNewOrder(): Order
    {
        return $this->newOrder;
    }

    /**
     * Копирование полей заказа
     *
     * @throws ArgumentException
     */
    public function copyFields()
    {
        $copyFieldsList = $this->getOrderCopyFields();
        foreach ($copyFieldsList as $fieldName) {
            $this->newOrder->setField(
                $fieldName,
                $this->oldOrder->getField($fieldName)
            );
        }
    }

    /**
     * Копирование корзины старого заказа в корзину нового заказа
     *
     * @throws ArgumentOutOfRangeException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws OrderCreateException
     * @throws \Exception
     */
    public function copyBasket()
    {
        $oldBasket = $this->oldOrder->getBasket();
        /** @var Basket $newBasket */
        $newBasket = Basket::create($oldBasket->getSiteId());

        $oldBasketItems = $oldBasket->getBasketItems();
        foreach ($oldBasketItems as $oldBasketItem) {
            /** @var BasketItem $oldBasketItem */
            // копирование значений полей позиции корзины
            $newBasketItem = $newBasket->createItem(
                $oldBasketItem->getField('MODULE'),
                $oldBasketItem->getField('PRODUCT_ID')
            );

            $oldBasketItemValues = $this->filterCopyBasketItemFields($oldBasketItem->getFieldValues());
            $newBasketItem->setField('NAME', $oldBasketItemValues['NAME']);
            $tmpResult = $newBasketItem->setFields($oldBasketItemValues);
            if (!$tmpResult->isSuccess()) {
                throw new OrderCreateException(implode("\n", $tmpResult->getErrorMessages()), 200);
            }

            // копирование свойств позиции корзины
            $newBasketPropertyCollection = $newBasketItem->getPropertyCollection();
            $oldItemPropertyList = [];
            if ($oldPropertyCollection = $oldBasketItem->getPropertyCollection()) {
                $oldItemPropertyList = $this->filterCopyBasketItemProps($oldPropertyCollection->getPropertyValues());
            }
            foreach ($oldItemPropertyList as $oldItemPropertyFields) {
                unset($oldItemPropertyFields['ID'], $oldItemPropertyFields['BASKET_ID']);
                $newBasketPropertyItem = $newBasketPropertyCollection->createItem([]);
                $tmpResult = $newBasketPropertyItem->setFields($oldItemPropertyFields);
                if (!$tmpResult->isSuccess()) {
                    throw new OrderCreateException(implode("\n", $tmpResult->getErrorMessages()), 300);
                }
            }

            $this->setOldBasket2NewMap($oldBasketItem->getId(), $newBasketItem->getInternalIndex());
        }

        // привязка корзины к новому заказу
        $tmpResult = $this->newOrder->setBasket($newBasket);
        if (!$tmpResult->isSuccess()) {
            throw new OrderCreateException(implode("\n", $tmpResult->getErrorMessages()), 500);
        }

        if ($this->newOrder->getBasket()->getOrderableItems()->isEmpty()) {
            throw new OrderCreateException('Корзина пуста', 600);
        }
    }

    /**
     * Копирование свойств заказа
     */
    public function copyProps()
    {
        $oldOrderPropsCollect = $this->oldOrder->getPropertyCollection();
        $oldOrderPropsByCode = [];
        $oldOrderPropsById = [];
        if ($oldOrderPropsCollect) {
            foreach ($oldOrderPropsCollect as $oldOrderProperty) {
                /** @var PropertyValue $oldOrderProperty */
                $tmpValues = $oldOrderProperty->getFieldValues();
                if ($tmpValues['CODE']) {
                    $oldOrderPropsByCode[$tmpValues['CODE']] = $oldOrderProperty->getValue();
                } elseif ($tmpValues['ORDER_PROPS_ID']) {
                    $oldOrderPropsById[$tmpValues['ORDER_PROPS_ID']] = $oldOrderProperty->getValue();
                }
            }
        }
        $oldOrderPropsByCode = $this->filterCopyOrderProps($oldOrderPropsByCode);
        foreach ($oldOrderPropsByCode as $propCode => $value) {
            $this->setPropValueByCode($propCode, $value);
        }
        foreach ($oldOrderPropsById as $propId => $value) {
            $this->setPropValueById($propId, $value);
        }
    }

    /**
     * @param int $propId
     * @param mixed $value
     * @return bool
     */
    public function setPropValueById(int $propId, $value): bool
    {
        $result = false;
        $newPropertyCollection = $this->newOrder->getPropertyCollection();
        /** @var PropertyValue $newOrderProperty */
        $newOrderProperty = $newPropertyCollection->getItemByOrderPropertyId($propId);
        if ($newOrderProperty) {
            $newOrderProperty->setValue($value);
            $result = true;
        }

        return $result;
    }

    /**
     * @param string $propCode
     * @param mixed $value
     * @return bool
     * @throws ArgumentNullException
     */
    public function setPropValueByCode(string $propCode, $value): bool
    {
        $result = false;

        if (!isset($this->propCode2PropIdMap)) {
            $newPropertyCollection = $this->newOrder->getPropertyCollection();
            foreach ($newPropertyCollection as $newOrderProperty) {
                /** @var PropertyValue $newOrderProperty */
                $tmpValues = $newOrderProperty->getFieldValues();
                if ($tmpValues['CODE']) {
                    $this->propCode2PropIdMap[$tmpValues['CODE']] = $tmpValues['ORDER_PROPS_ID'];
                }
            }
        }

        $propId = $this->propCode2PropIdMap[$propCode] ?? 0;
        if ($propId > 0) {
            $result = $this->setPropValueById($propId, $value);
        }

        return $result;
    }

    /**
     *  Копирование способов доставки
     * (свойство местоположения должно было скопироваться выше)
     *
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws ArgumentTypeException
     * @throws NotSupportedException
     * @throws OrderCreateException
     * @throws \Exception
     */
    public function copyShipments()
    {
        $this->newOrder->setField('DELIVERY_LOCATION', $this->oldOrder->getDeliveryLocation());

        // проверка заполненности свойства местоположения
        $newLocationProp = $this->newOrder->getPropertyCollection()->getDeliveryLocation();
        if (!$newLocationProp->getValue()) {
            $oldLocationProp = $this->oldOrder->getPropertyCollection()->getDeliveryLocation();
            $newLocationProp->setValue($oldLocationProp->getValue());
        }

        $oldShipmentCollect = $this->oldOrder->getShipmentCollection();
        $newShipmentCollect = $this->newOrder->getShipmentCollection();
        //$newShipmentCollect->clearCollection();
        foreach ($oldShipmentCollect as $oldShipment) {
            /** @var Shipment $oldShipment */
            if ($oldShipment->isSystem()) {
                continue;
            }

            // новое отправление
            $newShipment = $newShipmentCollect->createItem();
            $newShipment->setField('CURRENCY', $oldShipment->getCurrency());
            $newShipment->setField('DELIVERY_ID', $oldShipment->getDeliveryId());
            $newShipment->setField('DELIVERY_NAME', $oldShipment->getDeliveryName());

            /** @todo: Обсудить с разработчиками системы оформления заказов изменение подхода установки начальных статусов (в иделае должен быть всегда N) */
            $deliveryCode = $newShipment->getDelivery()->getCode();
            if ($this->getDeliveryService()->isDeliveryCode($deliveryCode)) {
                $this->newOrder->setFieldNoDemand('STATUS_ID', OrderService::STATUS_NEW_COURIER);
            }

            // привязывание позиций корзины к отправлению
            $newShipmentItemsCollect = $newShipment->getShipmentItemCollection();
            foreach ($oldShipment->getShipmentItemCollection() as $oldShipmentItem) {
                /** @var BasketItem $newShipmentBasketItem */
                $newShipmentBasketItem = $this->getNewBasketItemByOldId(
                    $oldShipmentItem->getBasketId()
                );
                if ($newShipmentBasketItem) {
                    /** @var ShipmentItem $oldShipmentItem */
                    $newShipmentItem = $newShipmentItemsCollect->createItem($newShipmentBasketItem);
                    $newShipmentItem->setQuantity($oldShipmentItem->getQuantity());
                }
            }
        }

        $tmpResult = $newShipmentCollect->calculateDelivery();
        if (!$tmpResult->isSuccess()) {
            throw new OrderCreateException(implode("\n", $tmpResult->getErrorMessages()), 700);
        }
    }

    /**
     * Копирование способов оплаты
     *
     * @throws ArgumentOutOfRangeException
     * @throws NotImplementedException
     * @throws ObjectNotFoundException
     * @throws \Exception
     */
    public function copyPayments()
    {
        $oldPaymentCollect = $this->oldOrder->getPaymentCollection();
        $newPaymentCollect = $this->newOrder->getPaymentCollection();
        //$newPaymentCollect->clearCollection();
        foreach ($oldPaymentCollect as $oldPayment) {
            /** @var Payment $oldPayment */
            if ($oldPayment->isInner()) {
                continue;
            }

            $newPayment = $newPaymentCollect->createItem();
            $newPayment->setField('PAY_SYSTEM_ID', $oldPayment->getPaymentSystemId());
            $newPayment->setField('PAY_SYSTEM_NAME', $oldPayment->getPaymentSystemName());
            $newPayment->setField('SUM', $this->newOrder->getPrice());
        }
    }

    /**
     * Пересчет заказа
     *
     * @throws ArgumentNullException
     * @throws ObjectNotFoundException
     * @throws OrderCreateException
     */
    public function doFinalAction()
    {
        $tmpResult = $this->newOrder->doFinalAction(true);
        if (!$tmpResult->isSuccess()) {
            throw new OrderCreateException(implode("\n", $tmpResult->getErrorMessages()), 800);
        }
    }

    /**
     * Сохранение заказа в базу
     *
     * @throws ArgumentOutOfRangeException
     * @throws OrderCreateException
     */
    public function save()
    {
        $tmpResult = $this->newOrder->save();
        if (!$tmpResult->isSuccess()) {
            throw new OrderCreateException(implode("\n", $tmpResult->getErrorMessages()), 900);
        }

        return $tmpResult;
    }

    /**
     * @param int $oldBasketItemId
     * @return BasketItem|null
     * @throws ArgumentNullException
     */
    public function getNewBasketItemByOldId(int $oldBasketItemId)
    {
        $basketItem = null;
        if (isset($this->oldBasket2NewMap[$oldBasketItemId])) {
            $newBasketItemIdx = $this->oldBasket2NewMap[$oldBasketItemId];
            /** @var BasketItem $basketItem */
            $basketItem = $this->newOrder->getBasket()->getItemByIndex($newBasketItemIdx);
        }

        return $basketItem;
    }

    /**
     * @param string $key
     * @param array $fields
     */
    protected function setFilterFields(string $key, array $fields)
    {
        $this->filterFields[$key] = $fields;
        if (isset($this->filterFieldsFlipped[$key])) {
            unset($this->filterFieldsFlipped[$key]);
        }
    }

    /**
     * @param string $key
     * @return array
     */
    protected function getFilterFieldsFlipped(string $key): array
    {
        if (!isset($this->filterFieldsFlipped[$key])) {
            $this->filterFieldsFlipped[$key] = array_flip($this->filterFields[$key]);
        }

        return $this->filterFieldsFlipped[$key] ?? [];
    }

    /**
     * @param array $fields
     * @return OrderCopy
     */
    public function setBasketItemCopyFields(array $fields): self
    {
        $this->setFilterFields('basketItemCopyFields', $fields);

        return $this;
    }

    /**
     * @return array
     */
    public function getBasketItemCopyFields(): array
    {
        return $this->filterFields['basketItemCopyFields'];
    }

    /**
     * @param array $fields
     * @return OrderCopy
     */
    public function appendBasketItemCopyFields(array $fields): self
    {
        $this->setBasketItemCopyFields(
            array_merge(
                $this->getBasketItemCopyFields(),
                $fields
            )
        );

        return $this;
    }

    /**
     * @return array
     */
    public function getBasketItemCopyFieldsFlipped(): array
    {
        return $this->getFilterFieldsFlipped('basketItemCopyFields');
    }

    /**
     * @param array $fields
     * @return OrderCopy
     */
    public function setBasketItemExcludeFields(array $fields): self
    {
        $this->setFilterFields('basketItemExcludeFields', $fields);

        return $this;
    }

    /**
     * @return array
     */
    public function getBasketItemExcludeFields(): array
    {
        return $this->filterFields['basketItemExcludeFields'];
    }

    /**
     * @param array $fields
     * @return OrderCopy
     */
    public function appendBasketItemExcludeFields(array $fields): self
    {
        $this->setBasketItemExcludeFields(
            array_merge(
                $this->getBasketItemExcludeFields(),
                $fields
            )
        );

        return $this;
    }

    /**
     * @return array
     */
    public function getBasketItemExcludeFieldsFlipped(): array
    {
        return $this->getFilterFieldsFlipped('basketItemExcludeFields');
    }

    /**
     * @param array $fields
     * @return OrderCopy
     */
    public function setBasketItemCopyProps(array $fields): self
    {
        $this->setFilterFields('basketItemCopyProps', $fields);

        return $this;
    }

    /**
     * @return array
     */
    public function getBasketItemCopyProps(): array
    {
        return $this->filterFields['basketItemCopyProps'];
    }

    /**
     * @param array $fields
     * @return OrderCopy
     */
    public function appendBasketItemCopyProps(array $fields): self
    {
        $this->setBasketItemCopyProps(
            array_merge(
                $this->getBasketItemCopyProps(),
                $fields
            )
        );

        return $this;
    }

    /**
     * @return array
     */
    public function getBasketItemCopyPropsFlipped(): array
    {
        return $this->getFilterFieldsFlipped('basketItemCopyProps');
    }

    /**
     * @param array $fields
     * @return OrderCopy
     */
    public function setBasketItemExcludeProps(array $fields): self
    {
        $this->setFilterFields('basketItemExcludeProps', $fields);

        return $this;
    }

    /**
     * @return array
     */
    public function getBasketItemExcludeProps(): array
    {
        return $this->filterFields['basketItemExcludeProps'];
    }

    /**
     * @param array $fields
     * @return OrderCopy
     */
    public function appendBasketItemExcludeProps(array $fields): self
    {
        $this->setBasketItemExcludeProps(
            array_merge(
                $this->getBasketItemExcludeProps(),
                $fields
            )
        );

        return $this;
    }

    /**
     * @return array
     */
    public function getBasketItemExcludePropsFlipped(): array
    {
        return $this->getFilterFieldsFlipped('basketItemExcludeProps');
    }

    /**
     * @param array $fields
     * @return OrderCopy
     */
    public function setOrderCopyProps(array $fields): self
    {
        $this->setFilterFields('orderCopyProps', $fields);

        return $this;
    }

    /**
     * @return array
     */
    public function getOrderCopyProps(): array
    {
        return $this->filterFields['orderCopyProps'];
    }

    /**
     * @param array $fields
     * @return OrderCopy
     */
    public function appendOrderCopyProps(array $fields): self
    {
        $this->setOrderCopyProps(
            array_merge(
                $this->getOrderCopyProps(),
                $fields
            )
        );

        return $this;
    }

    /**
     * @return array
     */
    public function getOrderCopyPropsFlipped(): array
    {
        return $this->getFilterFieldsFlipped('orderCopyProps');
    }

    /**
     * @param array $fields
     * @return OrderCopy
     */
    public function setOrderExcludeProps(array $fields): self
    {
        $this->setFilterFields('orderExcludeProps', $fields);

        return $this;
    }

    /**
     * @return array
     */
    public function getOrderExcludeProps(): array
    {
        return $this->filterFields['orderExcludeProps'];
    }

    /**
     * @param array $fields
     * @return OrderCopy
     */
    public function appendOrderExcludeProps(array $fields): self
    {
        $this->setOrderExcludeProps(
            array_merge(
                $this->getOrderExcludeProps(),
                $fields
            )
        );

        return $this;
    }

    /**
     * @return array
     */
    public function getOrderExcludePropsFlipped(): array
    {
        return $this->getFilterFieldsFlipped('orderExcludeProps');
    }

    /**
     * @param array $fields
     * @return OrderCopy
     */
    public function setOrderCopyFields(array $fields): self
    {
        $this->setFilterFields('orderCopyFields', $fields);

        return $this;
    }

    /**
     * @return array
     */
    public function getOrderCopyFields(): array
    {
        return $this->filterFields['orderCopyFields'];
    }

    /**
     * @param array $fields
     * @return OrderCopy
     */
    public function appendOrderCopyFields(array $fields): self
    {
        $this->setOrderCopyFields(
            array_merge(
                $this->getOrderCopyFields(),
                $fields
            )
        );

        return $this;
    }

    /**
     * @param array $fieldValues
     * @param array $copyFields
     * @param array $excludeFields
     * @return array
     */
    protected function resolveFilterFields(array $fieldValues, array $copyFields = [], array $excludeFields = [])
    {
        $resultFields = $fieldValues;
        if ($copyFields) {
            $resultFields = array_intersect_key($resultFields, $copyFields);
        }

        if ($excludeFields) {
            $resultFields = array_diff_key($resultFields, $excludeFields);
        }

        return $resultFields;
    }

    /**
     * @param array $basketItemFieldValues
     * @return array
     */
    protected function filterCopyBasketItemFields(array $basketItemFieldValues)
    {
        $copyFields = $this->getBasketItemCopyFieldsFlipped();
        $excludeFields = $this->getBasketItemExcludeFieldsFlipped();

        return $this->resolveFilterFields($basketItemFieldValues, $copyFields, $excludeFields);
    }

    /**
     * @param array $basketItemPropValues
     * @return array
     */
    protected function filterCopyBasketItemProps(array $basketItemPropValues)
    {
        $copyFields = $this->getBasketItemCopyPropsFlipped();
        $excludeFields = $this->getBasketItemExcludePropsFlipped();

        return $this->resolveFilterFields($basketItemPropValues, $copyFields, $excludeFields);
    }

    /**
     * @param array $orderProps
     * @return array
     */
    protected function filterCopyOrderProps(array $orderProps)
    {
        $copyFields = $this->getOrderCopyPropsFlipped();
        $excludeFields = $this->getOrderExcludePropsFlipped();

        return $this->resolveFilterFields($orderProps, $copyFields, $excludeFields);
    }

    /**
     * @param int $oldBasketItemId
     * @param int $newBasketItemIdx
     */
    protected function setOldBasket2NewMap(int $oldBasketItemId, int $newBasketItemIdx)
    {
        $this->oldBasket2NewMap[$oldBasketItemId] = $newBasketItemIdx;
    }

    /**
     * @return DeliveryService
     * @throws ApplicationCreateException
     */
    protected function getDeliveryService(): DeliveryService
    {
        if (!isset($this->deliveryService)) {
            $this->deliveryService = Application::getInstance()->getContainer()->get('delivery.service');
        }

        return $this->deliveryService;
    }
}
