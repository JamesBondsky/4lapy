<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Service\Orders;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Catalog\Product\Basket as CatalogBasket;
use Bitrix\Catalog\Product\CatalogProvider;
use Bitrix\Iblock\ElementTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\LoaderException;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Order;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaymentCollection;
use Bitrix\Sale\PropertyValueCollection;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use FourPaws\App\Env;
use FourPaws\DeliveryBundle\Exception\NotFoundException;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\DeliveryBundle\Service\IntervalService;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\Helpers\BxCollection;
use FourPaws\Helpers\DateHelper;
use FourPaws\LocationBundle\LocationService;
use FourPaws\SaleBundle\Discount\Utils\Manager;
use FourPaws\SaleBundle\Exception\InvalidArgumentException;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\SaleBundle\Service\OrderService as BaseOrderService;
use FourPaws\SapBundle\Dto\Base\Orders\DeliveryAddress;
use FourPaws\SapBundle\Dto\In\Orders\Order as OrderDtoIn;
use FourPaws\SapBundle\Dto\In\Orders\OrderOffer as OrderOfferIn;
use FourPaws\SapBundle\Dto\Out\Orders\DeliveryAddress as OutDeliveryAddress;
use FourPaws\SapBundle\Dto\Out\Orders\Order as OrderDtoOut;
use FourPaws\SapBundle\Dto\Out\Orders\OrderOffer;
use FourPaws\SapBundle\Enum\SapOrder;
use FourPaws\SapBundle\Exception\CantCreateBasketItem;
use FourPaws\SapBundle\Exception\NotFoundOrderDeliveryException;
use FourPaws\SapBundle\Exception\NotFoundOrderException;
use FourPaws\SapBundle\Exception\NotFoundOrderPaySystemException;
use FourPaws\SapBundle\Exception\NotFoundOrderShipmentException;
use FourPaws\SapBundle\Exception\NotFoundOrderStatusException;
use FourPaws\SapBundle\Exception\NotFoundOrderUserException;
use FourPaws\SapBundle\Exception\NotFoundProductException;
use FourPaws\SapBundle\Service\SapOutFile;
use FourPaws\SapBundle\Service\SapOutInterface;
use FourPaws\SapBundle\Source\SourceMessage;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Repository\UserRepository;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerAwareInterface;
use RuntimeException;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class OrderService
 *
 * @todo divide to base -> out
 *                      -> in
 *
 * @package FourPaws\SapBundle\Service\Orders
 */
class OrderService implements LoggerAwareInterface, SapOutInterface
{
    use LazyLoggerAwareTrait, SapOutFile;

    /**
     * @var BaseOrderService
     */
    private $baseOrderService;
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var string
     */
    private $outPath;
    /**
     * @var string
     */
    private $outPrefix;
    /**
     * @var string
     */
    private $messageId;
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var DeliveryService
     */
    private $deliveryService;
    /**
     * @var LocationService
     */
    private $locationService;
    /**
     * @var IntervalService
     */
    private $intervalService;
    /**
     * @var StatusService
     */
    private $statusService;
    /**
     * @var BasketService
     */
    private $basketService;

    /**
     * OrderService constructor.
     *
     * @param BaseOrderService $baseOrderService
     * @param DeliveryService $deliveryService
     * @param LocationService $locationService
     * @param SerializerInterface $serializer
     * @param Filesystem $filesystem
     * @param UserRepository $userRepository
     * @param IntervalService $intervalService
     * @param StatusService $statusService
     * @param BasketService $basketService
     */
    public function __construct(
        BaseOrderService $baseOrderService,
        DeliveryService $deliveryService,
        LocationService $locationService,
        SerializerInterface $serializer,
        Filesystem $filesystem,
        UserRepository $userRepository,
        IntervalService $intervalService,
        StatusService $statusService,
        BasketService $basketService
    )
    {
        $this->baseOrderService = $baseOrderService;
        $this->serializer = $serializer;
        $this->userRepository = $userRepository;
        $this->deliveryService = $deliveryService;
        $this->locationService = $locationService;
        $this->intervalService = $intervalService;
        $this->statusService = $statusService;

        $this->setFilesystem($filesystem);
        $this->basketService = $basketService;
    }

    /**
     * @param Order $order
     *
     * @throws IOException
     * @throws ObjectNotFoundException
     * @throws NotFoundOrderDeliveryException
     * @throws NotFoundOrderPaySystemException
     * @throws NotFoundOrderShipmentException
     * @throws NotFoundOrderUserException
     * @throws Exception
     */
    public function out(Order $order)
    {
        $message = $this->transformOrderToMessage($order);
        $this->filesystem->dumpFile($this->getFileName($order), $message->getData());
    }

    /**
     * @param Order $order
     *
     * @throws NotFoundOrderShipmentException
     * @throws NotFoundOrderDeliveryException
     * @throws NotFoundOrderPaySystemException
     * @throws ObjectNotFoundException
     * @throws NotFoundOrderUserException
     * @return SourceMessage
     * @throws Exception
     */
    public function transformOrderToMessage(Order $order): SourceMessage
    {
        $orderDto = new OrderDtoOut();

        try {
            $orderUser = $this->userRepository->find($order->getUserId());
        } catch (ConstraintDefinitionException | InvalidIdentifierException $e) {
            $orderUser = null;
        }

        if (null === $orderUser) {
            throw new NotFoundOrderUserException(
                \sprintf(
                    'Пользователь с id %s не найден, заказ #%s',
                    $order->getUserId(),
                    $order->getId()
                )
            );
        }

        /**
         * Источник заказа
         *
         * DFUE – заказ создан на Сайте;
         * MOBI – заказ создан в мобильном приложении;
         */
        $orderSource = $this->getPropertyValueByCode($order, 'FROM_APP') === 'Y'
            ? OrderDtoOut::ORDER_SOURCE_MOBILE_APP
            : OrderDtoOut::ORDER_SOURCE_SITE;

        $orderDto
            ->setId($order->getId())
            ->setDateInsert(DateHelper::convertToDateTime($order->getDateInsert()->toUserTime()))
            ->setClientId($order->getUserId())
            ->setClientFio($this->getPropertyValueByCode($order, 'NAME'))
            ->setClientPhone($this->getPropertyValueByCode($order, 'PHONE'))
            ->setClientOrderPhone($this->getPropertyValueByCode($order, 'PHONE_ALT'))
            ->setClientComment($order->getField('USER_DESCRIPTION') ?? '')
            ->setOrderSource($orderSource)
            ->setBonusCard($this->getPropertyValueByCode($order, 'DISCOUNT_CARD'));

        if (Env::isStage()) {
            $orderDto
                ->setClientPhone(SapOrder::TEST_PHONE)
                ->setClientOrderPhone(SapOrder::TEST_PHONE)
                ->setClientComment(SapOrder::TEST_COMMENT);
        }

        $this->populateOrderDtoPayment($orderDto, $order->getPaymentCollection());
        $this->populateOrderDtoDelivery($orderDto, $order);
        $this->populateOrderDtoProducts($orderDto, $order);

        $xml = $this->serializer->serialize($orderDto, 'xml');
        return new SourceMessage($this->getMessageId($order), OrderDtoOut::class, $xml);
    }

    /**
     * @param OrderDtoIn $orderDto
     *
     * @throws NotFoundProductException
     * @throws SystemException
     * @throws ArgumentNullException
     * @throws NotImplementedException
     * @throws ArgumentException
     * @throws NotFoundOrderException
     * @throws NotFoundOrderShipmentException
     * @throws NotFoundOrderStatusException
     * @throws RuntimeException
     *
     * @throws NotFoundOrderPaySystemException
     * @throws Exception
     * @throws ObjectNotFoundException
     * @throws ArgumentOutOfRangeException
     * @return Order
     */
    public function transformDtoToOrder(OrderDtoIn $orderDto): Order
    {
        $order = Order::load($orderDto->getId());

        if (null === $order) {
            throw new NotFoundOrderException(
                \sprintf(
                    'Заказ #%s не найден',
                    $orderDto->getId()
                )
            );
        }

        $this->setPropertiesFromDto($order, $orderDto);
        $this->setPaymentFromDto($order, $orderDto);
        $this->setDeliveryFromDto($order, $orderDto);
        $this->setBasketFromDto($order, $orderDto);
        $this->setStatusFromDto($order, $orderDto);

        return $order;
    }

    /**
     * @param Order $order
     *
     * @return string
     */
    public function getMessageId(Order $order): string
    {
        if (null === $this->messageId) {
            $this->messageId = \sprintf('order_%s_%s', $order->getId(), \time());
        }

        return $this->messageId;
    }

    /**
     * @param Order $order
     *
     * @return string
     */
    public function getFileName($order): string
    {
        return \sprintf(
            '/%s/%s%s.xml',
            \trim($this->outPath, '/'),
            $this->outPrefix,
            $order->getId()
        );
    }

    /**
     * @param OrderDtoOut $dto
     * @param PaymentCollection $paymentCollection
     *
     * @throws ObjectNotFoundException
     * @throws NotFoundOrderPaySystemException
     */
    private function populateOrderDtoPayment(OrderDtoOut $dto, PaymentCollection $paymentCollection)
    {
        /**
         * @var $externalPayment Payment
         */
        $externalPayment = null;
        $internalPayment = $paymentCollection->getInnerPayment();

        foreach ($paymentCollection as $paySystem) {
            if (!$paySystem->isInner()) {
                $externalPayment = $paySystem;

                break;
            }
        }

        if (null === $externalPayment) {
            throw new NotFoundOrderPaySystemException('Не найдена платежная система');
        }

        /**
         * Сумма оплаты бонусами
         */
        $dto->setBonusPayedCount(($internalPayment && $internalPayment->getSum()) ? $internalPayment->getSum() : 0.0);

        /**
         * Способ оплаты
         *
         * 05 – Онлайн-оплата;
         * Пусто – для других способов оплаты.
         *
         * Статус оплаты
         *
         * 01 – ZIMN Оплачено;
         * 02 – ZIMN Не оплачено;
         * 03 – ZIMN Предоплачено.
         */
        if ($externalPayment->getPaySystem()->getField('CODE') === $this->baseOrderService::PAYMENT_ONLINE) {
            /** @noinspection PhpParamsInspection */
            $dto
                ->setPayType(SapOrder::ORDER_PAYMENT_ONLINE_CODE)
                ->setPayStatus(SapOrder::ORDER_PAYMENT_STATUS_PRE_PAYED)
                ->setPayHoldTransaction($externalPayment->getField('PS_INVOICE_ID'))
                ->setPayHoldDate(DateHelper::convertToDateTime($externalPayment->getField('PS_RESPONSE_DATE')))
                ->setPrePayedSum($externalPayment->getSum())
                ->setPayMerchantCode(SapOrder::ORDER_PAYMENT_ONLINE_MERCHANT_ID);
        } else {
            $dto->setPayType('')
                /**
                 * @see https://jira.adv.ru/browse/LP03-420
                 */
                ->setPayStatus('');
        }
    }

    /**
     * @param OrderDtoOut $orderDto
     * @param Order $order
     *
     * @throws NotFoundOrderShipmentException
     * @throws NotFoundOrderDeliveryException
     * @throws Exception
     */
    private function populateOrderDtoDelivery(OrderDtoOut $orderDto, Order $order): void
    {
        $deliveryTypeCode = '';

        try {
            $deliveryTypeCode = $this->getDeliveryTypeCode($order);
        } catch (NotFoundOrderDeliveryException $e) {
            /**
             * Значит, это быстрый заказ. Или произошла ошибка, но тут нужно с конкретным кейсом разбираться.
             */
        }

        $contractorDeliveryTypeCode = '';

        if (\strpos($deliveryTypeCode, '_')) {
            [$deliveryTypeCode, $contractorDeliveryTypeCode] = \explode('_', $deliveryTypeCode);
        }

        try {
            $interval = $this->intervalService->getIntervalCode($this->getPropertyValueByCode(
                $order,
                'DELIVERY_INTERVAL'
            ));
        } catch (NotFoundException $e) {
            /**
             * Значит, такого интервала нет
             */
            $interval = '';
        }

        $deliveryDate = DateTime::createFromFormat(
            'd.m.Y',
            $this->getPropertyValueByCode($order, 'DELIVERY_DATE')
        );

        $deliveryAddress = $this->getDeliveryAddress($order);

        $orderDto
            ->setCommunicationType($this->getPropertyValueByCode($order, 'COM_WAY'))
            ->setDeliveryType($deliveryTypeCode)
            ->setContractorDeliveryType($contractorDeliveryTypeCode)
            ->setDeliveryTimeInterval($interval)
            ->setDeliveryAddress($deliveryAddress)
            ->setDeliveryAddressOrPoint($deliveryAddress->__toString() . ($shopCode? ', ' . $shopCode : ''))
            ->setContractorCode($deliveryTypeCode === SapOrder::DELIVERY_TYPE_CONTRACTOR ? SapOrder::DELIVERY_CONTRACTOR_CODE : '');

        if ($deliveryDate) {
            $orderDto->setDeliveryDate($deliveryDate);
        }
    }

    /**
     * @param OrderDtoOut $orderDto
     * @param Order $order
     *
     * @throws NotFoundOrderShipmentException
     */
    private function populateOrderDtoProducts(OrderDtoOut $orderDto, Order $order)
    {
        $position = 1;
        $collection = new ArrayCollection();

        /**
         * @var BasketItem $basketItem
         */
        foreach ($order->getBasket() as $basketItem) {
            try {
                $chargeBonus = $this->basketService->isItemWithBonusAwarding($basketItem, $order);
            } catch (InvalidArgumentException $e) {
                $chargeBonus = true;
            }

            $offer = (new OrderOffer())
                ->setPosition($position)
                ->setOfferXmlId($this->basketService->getBasketItemXmlId($basketItem))
                ->setUnitPrice($basketItem->getPrice())
                ->setQuantity($basketItem->getQuantity())
                /**
                 * Только штуки
                 */
                ->setUnitOfMeasureCode(SapOrder::UNIT_PTC_CODE)
                ->setChargeBonus($chargeBonus)
                ->setDeliveryShipmentPoint($this->getBasketPropertyValueByCode($basketItem, 'SHIPMENT_PLACE_CODE'))
                ->setDeliveryFromPoint($this->getPropertyValueByCode($order, 'DELIVERY_PLACE_CODE'));

            $collection->add($offer);
            $position++;
        }

        $this->addBasketDeliveryItem($order, $collection);

        $orderDto->setProducts($collection);
    }

    /**
     * @param Order $order
     *
     * @return DeliveryAddress|OutDeliveryAddress
     * @throws Exception
     */
    private function getDeliveryAddress(Order $order)
    {
        $city = $this->getPropertyValueByCode($order, 'CITY_CODE');
        $regionCode = $this->locationService->getRegionCode($city);
        $regionCode = \preg_match('~\D~', '', $regionCode);

        return (new OutDeliveryAddress())
            ->setDeliveryPlaceCode($this->getPropertyValueByCode($order,'DELIVERY_PLACE_CODE'))
            ->setRegionCode($regionCode)
            ->setPostCode('')
            ->setCityName($this->getPropertyValueByCode($order, 'CITY'))
            ->setStreetName($this->getPropertyValueByCode($order, 'STREET'))
            ->setStreetPrefix('')
            ->setHouse($this->getPropertyValueByCode($order, 'HOUSE'))
            ->setHousing($this->getPropertyValueByCode($order, 'BUILDING'))
            ->setBuilding('')
            ->setOwnerShip('')
            ->setFloor($this->getPropertyValueByCode($order, 'FLOOR'))
            ->setRoomNumber($this->getPropertyValueByCode($order, 'APARTMENT'))
            ->setDeliveryPointCode($this->getPropertyValueByCode($order, 'DPD_TERMINAL_CODE'));
    }

    /**
     * @param Order $order
     *
     * @throws NotFoundOrderDeliveryException
     * @throws NotFoundOrderShipmentException
     * @return string
     */
    private function getDeliveryTypeCode(Order $order): string
    {
        $shipment = BxCollection::getOrderExternalShipment($order->getShipmentCollection());

        if (null === $shipment) {
            throw new NotFoundOrderShipmentException(
                \sprintf(
                    'Отгрузка для заказа #%s не найдена',
                    $order->getId()
                )
            );
        }

        $shipment = BxCollection::getOrderExternalShipment($order->getShipmentCollection());
        $deliveryZone = $this->getDeliveryZone($order);

        if (
            $deliveryZone === DeliveryService::ZONE_2
            && $this->getPropertyValueByCode($order, 'REGION_COURIER_FROM_DC') === 'Y'
        ) {
            return SapOrder::DELIVERY_TYPE_ROUTE;
        }

        $isFastOrder = $this->getPropertyValueByCode($order, 'IS_FAST_ORDER') === 'Y';
        $code = $isFastOrder ? '' : $shipment->getDelivery()->getCode();

        switch ($code) {
            case DeliveryService::INNER_DELIVERY_CODE:
                switch ($deliveryZone) {
                    case DeliveryService::ZONE_1:
                        return SapOrder::DELIVERY_TYPE_COURIER_RC;
                    case DeliveryService::ZONE_2:
                        return SapOrder::DELIVERY_TYPE_COURIER_SHOP;
                }

                break;
            case DeliveryService::INNER_PICKUP_CODE:
                $shipmentPlaceCode = $this->getPropertyValueByCode($order, 'SHIPMENT_PLACE_CODE');

                return $shipmentPlaceCode ? SapOrder::DELIVERY_TYPE_PICKUP : SapOrder::DELIVERY_TYPE_PICKUP_POSTPONE;
                break;
            case DeliveryService::DPD_DELIVERY_CODE:
                return SapOrder::DELIVERY_TYPE_CONTRACTOR . '_' . SapOrder::DELIVERY_TYPE_CONTRACTOR_DELIVERY;
                break;
            case DeliveryService::DPD_PICKUP_CODE:
                return SapOrder::DELIVERY_TYPE_CONTRACTOR . '_' . SapOrder::DELIVERY_TYPE_CONTRACTOR_PICKUP;
                break;
            default:
                switch ($deliveryZone) {
                    case DeliveryService::ZONE_1:
                        return SapOrder::DELIVERY_TYPE_COURIER_RC;
                    case DeliveryService::ZONE_2:
                        return SapOrder::DELIVERY_TYPE_COURIER_SHOP;
                    case DeliveryService::ZONE_3:
                        return SapOrder::DELIVERY_TYPE_PICKUP;
                }
                break;
        }

        throw new NotFoundOrderDeliveryException(\sprintf(
            'Не найден тип доставки для заказа #%s',
            $order->getId()
        ));
    }

    /**
     * @param Order $order
     * @param string $code
     *
     * @return string
     */
    public function getPropertyValueByCode(Order $order, string $code): string
    {
        $propertyValue = BxCollection::getOrderPropertyByCode($order->getPropertyCollection(), $code);

        return $propertyValue ? ($propertyValue->getValue() ?? '') : '';
    }

    /**
     * @param Order $order
     * @param OrderDtoIn $orderDto
     *
     * @throws RuntimeException
     */
    private function setPropertiesFromDto(Order $order, OrderDtoIn $orderDto): void
    {
        $propertyCollection = $order->getPropertyCollection();
        $deliveryAddress = $orderDto->getDeliveryAddress();

        if ($orderDto->getClientFio()) {
            $this->setPropertyValue($propertyCollection, 'NAME', $orderDto->getClientFio());
        }
        $this->setPropertyValue($propertyCollection, 'PHONE', $orderDto->getClientPhone());
        $this->setPropertyValue($propertyCollection, 'PHONE_ALT', $orderDto->getClientOrderPhone());
        $this->setPropertyValue($propertyCollection, 'DELIVERY_DATE', $orderDto->getDeliveryDate()->format('d.m.Y'));

        try {
            $this->setPropertyValue(
                $propertyCollection,
                'DELIVERY_INTERVAL',
                $this->intervalService->getIntervalByCode($orderDto->getDeliveryTimeInterval())
            );
        } catch (NotFoundException $e) {
            $this->log()->error(
                \sprintf(
                    'Интервал %s не найден для заказа %s',
                    $orderDto->getDeliveryTimeInterval(),
                    $orderDto->getId()
                )
            );
        }

        if ($deliveryAddress) {
            $this->setPropertyValue($propertyCollection, 'CITY', $deliveryAddress->getCityName());
            $this->setPropertyValue($propertyCollection, 'STREET', $deliveryAddress->getStreetName());
            $this->setPropertyValue($propertyCollection, 'HOUSE', $deliveryAddress->getHouse());
            $this->setPropertyValue($propertyCollection, 'BUILDING', $deliveryAddress->getBuilding());
            $this->setPropertyValue($propertyCollection, 'FLOOR', $deliveryAddress->getFloor());
            $this->setPropertyValue($propertyCollection, 'APARTMENT', $deliveryAddress->getRoomNumber());
        }
    }

    /**
     * @param PropertyValueCollection $collection
     * @param string $code
     * @param string $value
     */
    public function setPropertyValue(PropertyValueCollection $collection, string $code, string $value): void
    {
        $propertyValue = BxCollection::getOrderPropertyByCode($collection, $code);

        if ($propertyValue) {
            $propertyValue->setValue($value);
        }
    }

    /**
     * @param Order $order
     * @param OrderDtoIn $orderDto
     *
     * @throws ArgumentOutOfRangeException
     * @throws NotImplementedException
     * @throws Exception
     * @throws ObjectNotFoundException
     * @throws NotFoundOrderPaySystemException
     */
    private function setPaymentFromDto(Order $order, OrderDtoIn $orderDto): void
    {
        /**
         * Мы не меняем платежную систему при смене статуса, только суммы
         */
        $statusPayed = SapOrder::ORDER_PAYMENT_STATUS_NOT_PAYED === $orderDto->getPayStatus() ? 'N' : 'Y';
        $bonusPayedCount = $orderDto->getBonusPayedCount();
        $innerPayment = $order->getPaymentCollection()->getInnerPayment();
        $externalPayment = null;

        if ($innerPayment && $bonusPayedCount) {
            $innerPayment->setPaid($statusPayed);
            $innerPayment->setField('PS_SUM', $bonusPayedCount);
            /** @noinspection PhpInternalEntityUsedInspection */
            $innerPayment->setFieldNoDemand('SUM', $bonusPayedCount);
        }

        /**
         * @var Payment $payment
         */
        foreach ($order->getPaymentCollection() as $payment) {
            if ($payment->isInner()) {
                continue;
            }

            $externalPayment = $payment;
        }

        if (null === $externalPayment) {
            throw new NotFoundOrderPaySystemException('Не найдена платежная система');
        }

        $externalPayment->setPaid($statusPayed);
        $externalPayment->setField('PS_SUM', $orderDto->getTotalSum() - $bonusPayedCount);
        /** @noinspection PhpInternalEntityUsedInspection */
        $externalPayment->setFieldNoDemand('SUM', $orderDto->getTotalSum() - $bonusPayedCount);
    }

    /**
     * @param Order $order
     * @param OrderDtoIn $orderDto
     */
    private function setDeliveryFromDto(Order $order, OrderDtoIn $orderDto): void
    {
        /**
         * Мы не меняем службу доставки при смене статуса
         */
    }

    /**
     * @param Order $order
     * @param OrderDtoIn $orderDto
     *
     * @throws NotFoundProductException
     * @throws SystemException
     * @throws RuntimeException
     */
    private function setBasketFromDto(Order $order, OrderDtoIn $orderDto): void
    {
        Manager::disableExtendsDiscount();
        $externalItems = $orderDto->getProducts();

        /**
         * @var BasketItem $basketItem
         */
        foreach ($basketCollection = $order->getBasket()->getBasketItems() as $basketItem) {
            $article = \ltrim($this->basketService->getBasketItemXmlId($basketItem), '0');

            $externalItem = $externalItems->filter(
                function ($item) use ($article) {
                    /**
                     * @var OrderOfferIn $item
                     */
                    return $article === \ltrim($item->getOfferXmlId());
                }
            )->first();

            if ($externalItem) {
                $this->renewBasketItem($basketItem, $externalItem);
                $externalItems->removeElement($externalItem);
            } else {
                try {
                    $basketItem->delete();
                } catch (ObjectNotFoundException $e) {
                    /**
                     * Объект найден, он точно удалится.
                     */
                }
            }
        }

        foreach ($externalItems as $item) {
            $this->addBasketItem($order->getBasket(), $item);
        }
    }

    /**
     * @param BasketItem $basketItem
     * @param OrderOfferIn $externalItem
     *
     * @throws RuntimeException
     */
    private function renewBasketItem(BasketItem $basketItem, OrderOfferIn $externalItem): void
    {
        $basketItem->setPrice($externalItem->getUnitPrice(), true);
        try {
            $basketItem->setField('QUANTITY', $externalItem->getQuantity());
        } catch (ArgumentOutOfRangeException | Exception $e) {
            $this->log()->error(\sprintf('Ошибка обновления товара в корзине: %s', $e->getMessage()));
        }
    }

    /**
     * @param Basket $basket
     * @param OrderOfferIn $externalItem
     *
     * @throws NotFoundProductException
     * @throws RuntimeException
     * @throws SystemException
     */
    private function addBasketItem(Basket $basket, OrderOfferIn $externalItem): void
    {
        $itemId = 0;
        $element = [];
        $xmlId = \ltrim($externalItem->getOfferXmlId(), '0');

        if (\strpos($xmlId, 2) === 0) {
            /**
             * Если артикул начинается с 2, то это доставка (или какая-либо ещё услуга), мы её не добавляем
             */
            return;
        }

        try {
            /**
             * @var array $element
             */
            $element = (new Query(ElementTable::class))
                ->setFilter([
                    'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::OFFERS),
                    'XML_ID' => $xmlId,
                ])
                ->setLimit(1)
                ->setSelect(['XML_ID', 'ID', 'NAME'])
                ->exec()
                ->fetch();

            if (!\is_array($element)) {
                throw new NotFoundProductException(
                    \sprintf(
                        'Продукт с внешним кодом %s не найден.',
                        $externalItem->getOfferXmlId()
                    )
                );
            }

            $itemId = $element['ID'];
        } catch (IblockNotFoundException | ArgumentException $e) {
            $this->log()->error(\sprintf('Ошибка добавления продукта: %s', $e->getMessage()));
        }

        $context = [
            'SITE_ID' => SITE_ID,
            'USER_ID' => $basket->getOrder()->getUserId(),
            'ORDER_ID' => $basket->getOrderId(),
        ];

        $fields = [
            'PRODUCT_ID' => $itemId,
            'QUANTITY' => $externalItem->getQuantity(),
            'MODULE' => 'catalog',
            'CURRENCY' => 'RUB',
            'PRICE' => $externalItem->getUnitPrice(),
            'NAME' => $element['NAME'],
            'PRODUCT_PROVIDER_CLASS' => CatalogProvider::class,
        ];

        try {
            $result = CatalogBasket::addProductToBasket($basket, $fields, $context);

            if (!$result->isSuccess()) {
                throw new CantCreateBasketItem(\implode(', ', $result->getErrorMessages()));
            }
        } catch (CantCreateBasketItem | LoaderException | ObjectNotFoundException $e) {
            $this->log()->error(
                \sprintf(
                    'Ошибка добавления товара в корзину заказа #%s: %s',
                    $basket->getOrderId(),
                    $e->getMessage()
                )
            );
        }
    }

    /**
     * @param Order $order
     * @param OrderDtoIn $orderDto
     *
     * @throws NotFoundOrderShipmentException
     * @throws NotFoundOrderStatusException
     * @throws ArgumentException
     * @return string
     *
     */
    private function setStatusFromDto(Order $order, OrderDtoIn $orderDto): string
    {
        $shipment = BxCollection::getOrderExternalShipment($order->getShipmentCollection());

        if (null === $shipment) {
            throw new NotFoundOrderShipmentException(
                \sprintf(
                    'Отгрузка для заказа #%s не найдена',
                    $order->getId()
                )
            );
        }

        $deliveryCode = $shipment->getDelivery()->getCode();
        $status = $this->statusService->getStatusBySapStatus($deliveryCode, $orderDto->getStatus());

        if ($status) {
            $order->setField(
                'STATUS_ID',
                $this->statusService->getStatusBySapStatus($deliveryCode, $orderDto->getStatus())
            );
        }

        return $status;
    }

    /**
     * @param BasketItem $item
     * @param string $code
     *
     * @return string
     */
    private function getBasketPropertyValueByCode(BasketItem $item, string $code): string
    {
        return $item->getPropertyCollection()->getPropertyValues()[$code]['VALUE'] ?? '';
    }

    /**
     * @param Order $order
     * @param ArrayCollection $collection
     *
     * @throws NotFoundOrderShipmentException
     */
    private function addBasketDeliveryItem(Order $order, ArrayCollection $collection): void
    {
        $deliveryPrice = $order->getDeliveryPrice();

        if ($deliveryPrice > 0) {
            $deliveryZone = $this->getDeliveryZone($order);

            switch ($deliveryZone) {
                case DeliveryService::ZONE_1:
                    $xmlId = SapOrder::DELIVERY_ZONE_1_ARTICLE;
                    break;
                case DeliveryService::ZONE_2:
                    $xmlId = SapOrder::DELIVERY_ZONE_2_ARTICLE;
                    break;
                case DeliveryService::ZONE_3:
                    $xmlId = SapOrder::DELIVERY_ZONE_3_ARTICLE;
                    break;
                case DeliveryService::ZONE_4:
                default:
                    $xmlId = SapOrder::DELIVERY_ZONE_4_ARTICLE;
                    break;
            }

            $offer = (new OrderOffer())
                ->setPosition($collection->count() + 1)
                ->setOfferXmlId($xmlId)
                ->setUnitPrice($deliveryPrice)
                ->setQuantity(1)
                ->setUnitOfMeasureCode(SapOrder::UNIT_PTC_CODE)
                ->setChargeBonus(false)
                ->setDeliveryShipmentPoint('');
            $collection->add($offer);
        }
    }

    /**
     * @param Order $order
     *
     * @return string
     *
     * @throws NotFoundOrderShipmentException
     */
    private function getDeliveryZone(Order $order): string
    {
        $shipment = BxCollection::getOrderExternalShipment($order->getShipmentCollection());

        if (null === $shipment) {
            throw new NotFoundOrderShipmentException(
                \sprintf(
                    'Отгрузка для заказа #%s не найдена',
                    $order->getId()
                )
            );
        }

        $location = $this->getPropertyValueByCode($order, 'CITY_CODE');
        $deliveryId = $shipment->getDeliveryId();

        return $this->deliveryService->getDeliveryZoneByDelivery($location, $deliveryId) ?? '';
    }
}
