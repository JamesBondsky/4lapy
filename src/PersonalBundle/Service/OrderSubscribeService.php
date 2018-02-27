<?php

namespace FourPaws\PersonalBundle\Service;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\Entity\AddResult;
use Bitrix\Main\Entity\DeleteResult;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Entity\UpdateResult;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\Result;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PropertyValue;
use Bitrix\Sale\Shipment;
use Bitrix\Sale\ShipmentItem;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\PersonalBundle\Entity\Order;
use FourPaws\PersonalBundle\Exception\OrderCreateException;
use FourPaws\PersonalBundle\Exception\OrderNotFoundException;
use FourPaws\PersonalBundle\Repository\OrderSubscribeRepository;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class OrderSubscribeService
 *
 * @package FourPaws\PersonalBundle\Service
 */
class OrderSubscribeService
{
    /** @var OrderSubscribeRepository $orderSubscribeRepository */
    private $orderSubscribeRepository;
    /** @var CurrentUserProviderInterface $currentUser */
    private $currentUser;
    /** @var OrderService $orderService */
    private $orderService;
    /** @var DeliveryService $deliveryService */
    private $deliveryService;
    /** @var array $miscData */
    private $miscData = [];
    /** @var array */
    private $basketItemCopyFields = [
        'SET_PARENT_ID', 'TYPE',
        'PRODUCT_ID', 'PRODUCT_PRICE_ID', 'PRICE', 'CURRENCY', 'WEIGHT', 'QUANTITY', 'LID',
        'NAME', 'CALLBACK_FUNC', 'NOTES', 'PRODUCT_PROVIDER_CLASS', 'CANCEL_CALLBACK_FUNC',
        'ORDER_CALLBACK_FUNC', 'PAY_CALLBACK_FUNC', 'DETAIL_PAGE_URL', 'CATALOG_XML_ID', 'PRODUCT_XML_ID',
        'VAT_RATE', 'MEASURE_NAME', 'MEASURE_CODE', 'BASE_PRICE', 'VAT_INCLUDED'
    ];
    /** @var array */
    private $basketItemExcludeFields = [];
    /** @var array */
    private $basketItemCopyProps = [];
    /** @var array */
    private $basketItemExcludeProps = [
        /** @todo: Уточнить какие свойства позиции корзины следует исключать */
        'CATALOG.XML_ID', 'PRODUCT.XML_ID',
    ];
    /** @var array */
    private $orderCopyProps = [];
    /** @var array */
    private $orderExcludeProps = [
        'IS_EXPORTED',
        'DELIVERY_DATE', 'DELIVERY_INTERVAL',
        /** @todo: Уточнить насчет этих свойств */
        'FROM_APP',
    ];

    /**
     * OrderSubscribeService constructor.
     *
     * @param OrderSubscribeRepository $orderSubscribeRepository
     *
     * @throws ServiceNotFoundException
     * @throws ApplicationCreateException
     * @throws ServiceCircularReferenceException
     */
    public function __construct(OrderSubscribeRepository $orderSubscribeRepository)
    {
        $this->orderSubscribeRepository = $orderSubscribeRepository;
    }

    /**
     * @return OrderService
     * @throws ApplicationCreateException
     */
    public function getOrderService(): OrderService
    {
        if (!isset($this->orderService)) {
            $this->orderService = Application::getInstance()->getContainer()->get('order.service');
        }

        return $this->orderService;
    }

    /**
     * @return CurrentUserProviderInterface
     * @throws ApplicationCreateException
     */
    public function getCurrentUserService(): CurrentUserProviderInterface
    {
        if (!isset($this->currentUser)) {
            $this->currentUser = Application::getInstance()->getContainer()->get(CurrentUserProviderInterface::class);
        }

        return $this->currentUser;
    }

    /**
     * @return DeliveryService
     * @throws ApplicationCreateException
     */
    public function getDeliveryService(): DeliveryService
    {
        if (!isset($this->deliveryService)) {
            $this->deliveryService = Application::getInstance()->getContainer()->get('delivery.service');
        }

        return $this->deliveryService;
    }

    /**
     * @return array
     * @throws ArgumentException
     * @throws \Bitrix\Main\SystemException
     * @throws \Exception
     */
    public function getFrequencyEnum(): array
    {
        if (!isset($this->miscData['FREQUENCY_ENUM'])) {
            $this->miscData['FREQUENCY_ENUM'] = [];
            $hlBlockEntityFields = $this->orderSubscribeRepository->getHlBlockEntityFields();
            if (isset($hlBlockEntityFields['UF_FREQUENCY'])) {
                if ($hlBlockEntityFields['UF_FREQUENCY']['USER_TYPE_ID'] === 'enumeration') {
                    // результат выборки кешируется внутри метода
                    $enumItems = (new \CUserFieldEnum())->GetList(
                        [
                            'SORT' => 'ASC'
                        ],
                        [
                            'USER_FIELD_ID' => $hlBlockEntityFields['UF_FREQUENCY']['ID']
                        ]
                    );
                    while ($item = $enumItems->Fetch()) {
                        $this->miscData['FREQUENCY_ENUM'][$item['ID']] = $item;
                    }
                }
            }
        }

        return $this->miscData['FREQUENCY_ENUM'];
    }

    /**
     * @param int $enumId
     * @return string
     * @throws ArgumentException
     * @throws \Bitrix\Main\SystemException
     * @throws \Exception
     */
    public function getFrequencyXmlId(int $enumId): string
    {
        $enum = $this->getFrequencyEnum();

        return isset($enum[$enumId]) ? $enum[$enumId]['XML_ID'] : '';
    }

    /**
     * @param int $enumId
     * @return string
     * @throws ArgumentException
     * @throws \Bitrix\Main\SystemException
     * @throws \Exception
     */
    public function getFrequencyValue(int $enumId): string
    {
        $enum = $this->getFrequencyEnum();

        return isset($enum[$enumId]) ? $enum[$enumId]['VALUE'] : '';
    }

    /**
     * @param int|array $orderId
     * @param bool $filterActive
     * @return ArrayCollection
     * @throws \Exception
     */
    public function getSubscriptionsByOrder($orderId, $filterActive = true)
    {
        $params = [];
        if ($filterActive) {
            $params['=UF_ACTIVE'] = 1;
        }

        return $this->orderSubscribeRepository->findByOrder($orderId, $params);
    }

    /**
     * @param int|array $userId
     * @param bool $filterActive
     * @return ArrayCollection
     * @throws \Exception
     */
    public function getSubscriptionsByUser($userId, $filterActive = true)
    {
        $params = [];
        if ($filterActive) {
            $params['=UF_ACTIVE'] = 1;
        }

        return $this->orderSubscribeRepository->findByUser($userId, $params);
    }

    /**
     * @param int $orderId
     * @return Order|null
     * @throws \Exception
     */
    public function getOrderById(int $orderId)
    {
        return $this->getOrderService()->getOrderById($orderId);
    }

    /**
     * @param int $userId
     * @param bool $filterActive
     * @return ArrayCollection
     * @throws \Exception
     */
    public function getUserSubscribedOrders(int $userId, $filterActive = true): ArrayCollection
    {
        $params = [
            'filter' => [
                'USER_ID' => $userId,
                '!=ORDER_SUBSCRIBE.ID' => false,
            ],
            'runtime' => [
                new ReferenceField(
                    'ORDER_SUBSCRIBE',
                    $this->orderSubscribeRepository->getHlBlockEntityClass(),
                    [
                        '=this.ID' => 'ref.UF_ORDER_ID'
                    ]
                ),
            ]
        ];
        if ($filterActive) {
            $params['filter']['=ORDER_SUBSCRIBE.UF_ACTIVE'] = 1;
        }

        return $this->getOrderService()->getUserOrders($params);
    }

    /**
     * @param array $data
     * @return AddResult
     */
    public function add(array $data): AddResult
    {
        $addResult = $this->orderSubscribeRepository->createEx($data);

        return $addResult;
    }

    /**
     * @param array $data
     * @return UpdateResult
     */
    public function update(array $data): UpdateResult
    {
        $updateResult = $this->orderSubscribeRepository->updateEx($data);

        return $updateResult;
    }

    /**
     * @param int $id
     * @return DeleteResult
     */
    public function delete(int $id): DeleteResult
    {
        $deleteResult = $this->orderSubscribeRepository->deleteEx($id);

        return $deleteResult;
    }

    /**
     * @param int $orderId
     * @return Result
     * @throws ApplicationCreateException
     * @throws OrderCreateException
     * @throws OrderNotFoundException
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws ArgumentTypeException
     * @throws NotImplementedException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws \Exception
     */
    public function copyOrder(int $orderId)
    {
        $result = new Result();

        $oldBitrixOrder = \Bitrix\Sale\Order::load($orderId);
        if (!$oldBitrixOrder) {
            throw new OrderNotFoundException('Копируемый заказ не найден');
        }

        $newBitrixOrder = \Bitrix\Sale\Order::create(
            $oldBitrixOrder->getSiteId(),
            $oldBitrixOrder->getUserId(),
            $oldBitrixOrder->getCurrency()
        );
        if (!$newBitrixOrder) {
            throw new OrderCreateException('Не удалось создать новый заказ для заполнения', 100);
        }

        $oldBasket2NewMap = [];

        /**
         * Установка типа плательщика для нового заказа
         */
        $newBitrixOrder->setPersonTypeId($oldBitrixOrder->getPersonTypeId());

        /**
         * Копирование корзины старого заказа в корзину нового заказа
         */
        $oldBasket = $oldBitrixOrder->getBasket();
        /** @var Basket $newBasket */
        $newBasket = Basket::create($oldBasket->getSiteId());

        $oldBasketItems = $oldBasket->getBasketItems();
        foreach ($oldBasketItems as $oldBasketItem) {
            /** @var BasketItem $oldBasketItem*/
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
                $oldItemPropertyList = $this->filterCopyBasketItemProps(
                    $oldPropertyCollection->getPropertyValues()
                );
            }
            foreach ($oldItemPropertyList as $oldItemPropertyFields) {
                unset($oldItemPropertyFields['ID'], $oldItemPropertyFields['BASKET_ID']);
                $newBasketPropertyItem = $newBasketPropertyCollection->createItem([]);
                $tmpResult = $newBasketPropertyItem->setFields($oldItemPropertyFields);
                if (!$tmpResult->isSuccess()) {
                    throw new OrderCreateException(implode("\n", $tmpResult->getErrorMessages()), 300);
                }
            }

            $oldBasket2NewMap[$oldBasketItem->getId()] = $newBasketItem->getInternalIndex();
        }

        // привязка корзины к новому заказу
        $tmpResult = $newBitrixOrder->setBasket($newBasket);
        if (!$tmpResult->isSuccess()) {
            throw new OrderCreateException(implode("\n", $tmpResult->getErrorMessages()), 500);
        }
        if ($newBitrixOrder->getBasket()->getOrderableItems()->isEmpty()) {
            throw new OrderCreateException('Корзина пуста', 600);
        }

        /**
         * Копирование свойств заказа
         */
        $oldOrderPropsCollect = $oldBitrixOrder->getPropertyCollection();
        $oldOrderProps = [];
        if ($oldOrderPropsCollect) {
            foreach($oldOrderPropsCollect as $oldOrderProperty) {
                /** @var PropertyValue $oldOrderProperty */
                $tmpValues = $oldOrderProperty->getFieldValues();
                $oldOrderProps[$tmpValues['CODE']] = $oldOrderProperty->getValue();
            }
        }
        $oldOrderProps = $this->filterCopyOrderProps($oldOrderProps);

        $newPropertyCollection = $newBitrixOrder->getPropertyCollection();
        foreach($newPropertyCollection as $newOrderProperty) {
            /** @var PropertyValue $newOrderProperty */
            $tmpValues = $newOrderProperty->getFieldValues();
            if ($tmpValues['CODE'] && isset($oldOrderProps[$tmpValues['CODE']])) {
                $newOrderProperty->setValue($oldOrderProps[$tmpValues['CODE']]);
            }
        }

        /**
         * Копирование способов доставки
         * (свойство местоположения должно было скопироваться выше)
         */
        $newBitrixOrder->setField('DELIVERY_LOCATION', $oldBitrixOrder->getDeliveryLocation());
        $oldShipmentCollect = $oldBitrixOrder->getShipmentCollection();
        $newShipmentCollect = $newBitrixOrder->getShipmentCollection();
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

            /** @todo: Нехорошо присваивать статус заказа так, обсудить с разработчиками системы оформления заказов изменение подхода */
            $deliveryCode = $newShipment->getDelivery()->getCode();
            if ($this->getDeliveryService()->isDeliveryCode($deliveryCode)) {
                $newBitrixOrder->setFieldNoDemand(
                    'STATUS_ID',
                    \FourPaws\SaleBundle\Service\OrderService::STATUS_NEW_COURIER
                );
            }

            // привязывание позиций корзины к отправлению
            $newShipmentItemsCollect = $newShipment->getShipmentItemCollection();
            foreach ($oldShipment->getShipmentItemCollection() as $oldShipmentItem) {
                /** @var ShipmentItem $oldShipmentItem */
                $oldBasketItemId = $oldShipmentItem->getBasketId();
                if (isset($oldBasket2NewMap[$oldBasketItemId])) {
                    /** @var BasketItem $newShipmentBasketItem */
                    $newShipmentBasketItem = $newBitrixOrder->getBasket()->getItemByIndex(
                        $oldBasket2NewMap[$oldBasketItemId]
                    );
                    if ($newShipmentBasketItem) {
                        $newShipmentItem = $newShipmentItemsCollect->createItem($newShipmentBasketItem);
                        $newShipmentItem->setQuantity($oldShipmentItem->getQuantity());
                    }
                }
            }
        }
        $tmpResult = $newShipmentCollect->calculateDelivery();
        if (!$tmpResult->isSuccess()) {
            throw new OrderCreateException(implode("\n", $tmpResult->getErrorMessages()), 700);
        }

        /**
         * Копирование способов оплаты
         */
        $oldPaymentCollect = $oldBitrixOrder->getPaymentCollection();
        $newPaymentCollect = $newBitrixOrder->getPaymentCollection();
        foreach ($oldPaymentCollect as $oldPayment) {
            /** @var Payment $oldPayment */
            if ($oldPayment->isInner()) {
                continue;
            }

            $newPayment = $newPaymentCollect->createItem();
            $newPayment->setField('PAY_SYSTEM_ID', $oldPayment->getPaymentSystemId());
            $newPayment->setField('PAY_SYSTEM_NAME', $oldPayment->getPaymentSystemName());
            $newPayment->setField('SUM', $newBitrixOrder->getPrice());
        }

        /**
         * Пересчет заказа
         */
        $tmpResult = $newBitrixOrder->doFinalAction(true);
        if (!$tmpResult->isSuccess()) {
            throw new OrderCreateException(implode("\n", $tmpResult->getErrorMessages()), 800);
        }

        /**
         * Сохранение заказа в базу
         */
        $tmpResult = $newBitrixOrder->save();
        if (!$tmpResult->isSuccess()) {
            throw new OrderCreateException(implode("\n", $tmpResult->getErrorMessages()), 900);
        }

        return $result;
    }

    /**
     * @param array $fieldValues
     * @param array $copyFields
     * @param array $excludeFields
     * @return array
     */
    protected function filterCopyFields(array $fieldValues, array $copyFields = [], array $excludeFields = [])
    {
        $resultFields = $fieldValues;
        if ($copyFields) {
            $resultFields = array_intersect_key(
                $resultFields,
                $copyFields
            );
        }

        if ($excludeFields) {
            $resultFields = array_diff_key(
                $resultFields,
                $excludeFields
            );
        }

        return $resultFields;
    }

    /**
     * @param array $basketItemFieldValues
     * @return array
     */
    protected function filterCopyBasketItemFields(array $basketItemFieldValues)
    {
        static $copyFields = null;
        static $excludeFields = null;
        if ($copyFields === null) {
            $copyFields = array_flip($this->basketItemCopyFields);
        }
        if ($excludeFields === null) {
            $excludeFields = array_flip($this->basketItemExcludeFields);
        }

        return $this->filterCopyFields($basketItemFieldValues, $copyFields, $excludeFields);
    }

    /**
     * @param array $basketItemPropValues
     * @return array
     */
    protected function filterCopyBasketItemProps(array $basketItemPropValues)
    {
        static $copyFields = null;
        static $excludeFields = null;
        if ($copyFields === null) {
            $copyFields = array_flip($this->basketItemCopyProps);
        }
        if ($excludeFields === null) {
            $excludeFields = array_flip($this->basketItemExcludeProps);
        }

        return $this->filterCopyFields($basketItemPropValues, $copyFields, $excludeFields);
    }

    /**
     * @param array $orderProps
     * @return array
     */
    protected function filterCopyOrderProps(array $orderProps)
    {
        static $copyFields = null;
        static $excludeFields = null;
        if ($copyFields === null) {
            $copyFields = array_flip($this->orderCopyProps);
        }
        if ($excludeFields === null) {
            $excludeFields = array_flip($this->orderExcludeProps);
        }

        return $this->filterCopyFields($orderProps, $copyFields, $excludeFields);
    }
}
