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
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Order;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaymentCollection;
use Bitrix\Sale\PropertyValueCollection;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use FourPaws\DeliveryBundle\Exception\NotFoundException;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\DeliveryBundle\Service\IntervalService;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\Helpers\BxCollection;
use FourPaws\Helpers\DateHelper;
use FourPaws\Location\LocationService;
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
 * @package FourPaws\SapBundle\Service\Orders
 */
class OrderService implements LoggerAwareInterface, SapOutInterface
{
    use LazyLoggerAwareTrait;

    /**
     * @var BaseOrderService
     */
    private $baseOrderService;
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var Filesystem
     */
    private $filesystem;
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
     */
    public function __construct(
        BaseOrderService $baseOrderService,
        DeliveryService $deliveryService,
        LocationService $locationService,
        SerializerInterface $serializer,
        Filesystem $filesystem,
        UserRepository $userRepository,
        IntervalService $intervalService,
        StatusService $statusService
    )
    {
        $this->baseOrderService = $baseOrderService;
        $this->serializer = $serializer;
        $this->filesystem = $filesystem;
        $this->userRepository = $userRepository;
        $this->deliveryService = $deliveryService;
        $this->locationService = $locationService;
        $this->intervalService = $intervalService;
        $this->statusService = $statusService;
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
     */
    public function out(Order $order)
    {
        $message = $this->transformOrderToMessage($order);
        $this->filesystem->dumpFile($this->getFileName($order), $message->getData());
    }

    /**
     * @param Order $order
     *
     * @return SourceMessage
     * @throws NotFoundOrderShipmentException
     * @throws NotFoundOrderDeliveryException
     * @throws NotFoundOrderPaySystemException
     * @throws ObjectNotFoundException
     * @throws NotFoundOrderUserException
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
            throw new NotFoundOrderUserException(sprintf(
                'Пользователь с id %s не найден, заказ #%s',
                $order->getUserId(),
                $order->getId()
            ));
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
            ->setClientId($order->getId())
            ->setClientFio($this->getPropertyValueByCode($order, 'NAME'))
            ->setClientPhone($this->getPropertyValueByCode($order, 'PHONE'))
            ->setClientOrderPhone($this->getPropertyValueByCode($order, 'PHONE_ALT'))
            ->setClientComment($order->getField('USER_DESCRIPTION') ?? '')
            ->setOrderSource($orderSource)
            ->setBonusCard($orderUser->getDiscountCardNumber());

        $this->populateOrderDtoPayment($orderDto, $order->getPaymentCollection());
        $this->populateOrderDtoDelivery($orderDto, $order);
        $this->populateOrderDtoProducts($orderDto, $order);

        $xml = $this->serializer->serialize($orderDto, 'xml');
        return new SourceMessage($this->getMessageId($order), OrderDtoOut::class, $xml);
    }

    /**
     * @param OrderDtoIn $orderDto
     *
     * @throws ArgumentNullException
     * @throws NotImplementedException
     * @throws ArgumentException
     * @throws NotFoundOrderException
     * @throws NotFoundOrderShipmentException
     * @throws NotFoundOrderStatusException
     * @throws RuntimeException
     *
     * @return Order
     * @throws NotFoundOrderPaySystemException
     * @throws Exception
     * @throws ObjectNotFoundException
     * @throws ArgumentOutOfRangeException
     */
    public function transformDtoToOrder(OrderDtoIn $orderDto): Order
    {
        $order = Order::load($orderDto->getId());

        if (null === $order) {
            throw new NotFoundOrderException(sprintf(
                'Заказ #%s не найден',
                $orderDto->getId()
            ));
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
            $this->messageId = sprintf('order_%s_%s', $order->getId(), time());
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
        return sprintf(
            '/%s/%s-%s%s.xml',
            trim($this->outPath, '/'),
            $order->getDateInsert()->format('Ymd'),
            $this->outPrefix,
            $order->getId()
        );
    }

    /**
     * @param string $outPath
     *
     * @throws IOException
     */
    public function setOutPath(string $outPath): void
    {
        if (!$this->filesystem->exists($outPath)) {
            $this->filesystem->mkdir($outPath, '0775');
        }

        $this->outPath = $outPath;
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
        if ($externalPayment->getField('CODE') === $this->baseOrderService::PAYMENT_ONLINE) {
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
                ->setPayStatus(SapOrder::ORDER_PAYMENT_STATUS_NOT_PAYED);
        }
    }

    /**
     * @param OrderDtoOut $orderDto
     * @param Order $order
     *
     * @throws NotFoundOrderShipmentException
     * @throws NotFoundOrderDeliveryException
     */
    private function populateOrderDtoDelivery(OrderDtoOut $orderDto, Order $order): void
    {
        $deliveryTypeCode = $this->getDeliveryTypeCode($order);
        $contractorDeliveryTypeCode = '';

        if (strpos($deliveryTypeCode, '_')) {
            [$deliveryTypeCode, $contractorDeliveryTypeCode] = explode('_', $deliveryTypeCode);
        }

        $deliveryPoint = '';

        $shopCode = $this->getPropertyValueByCode($order, 'DELIVERY_PLACE_CODE');
        $terminalCode = $this->getPropertyValueByCode($order, 'DPD_TERMINAL_CODE');

        if ($shopCode) {
            $deliveryPoint = $shopCode;
        }

        if ($terminalCode) {
            $deliveryPoint = $terminalCode;
        }

        try {
            $interval = $this->intervalService->getIntervalCode($this->getPropertyValueByCode($order, 'DELIVERY_INTERVAL'));
        } catch (NotFoundException $e) {
            /**
             * Значит, такого интервала нет
             */
            $interval = '';
        }

        $orderDto
            ->setCommunicationType($this->getPropertyValueByCode($order, 'COM_WAY'))
            ->setDeliveryType($deliveryTypeCode)
            ->setContractorDeliveryType($contractorDeliveryTypeCode)
            ->setDeliveryDate(\DateTime::createFromFormat('d.m.Y', $this->getPropertyValueByCode($order, 'DELIVERY_DATE')))
            ->setDeliveryTimeInterval($interval)
            ->setDeliveryAddress($this->getDeliveryAddress($order, $terminalCode))
            ->setDeliveryAddressOrPoint($deliveryPoint)
            ->setContractorCode($deliveryTypeCode === SapOrder::DELIVERY_TYPE_CONTRACTOR ? SapOrder::DELIVERY_CONTRACTOR_CODE : '');
    }

    /**
     * @param OrderDtoOut $orderDto
     * @param Order $order
     */
    private function populateOrderDtoProducts(OrderDtoOut $orderDto, Order $order)
    {
        $position = 1;
        $collection = new ArrayCollection();

        /**
         * @var BasketItem $basketItem
         */
        foreach ($order->getBasket() as $basketItem) {
            $xmlId = $basketItem->getField('PRODUCT_XML_ID');

            if (strpos($xmlId, '#')) {
                /** @noinspection ShortListSyntaxCanBeUsedInspection */
                list(, $xmlId) = explode('#', $xmlId);
            }

            $offer = (new OrderOffer())
                ->setPosition($position)
                ->setOfferXmlId($xmlId)
                ->setUnitPrice($basketItem->getBasePrice())
                ->setQuantity($basketItem->getQuantity())
                /**
                 * Только штуки
                 */
                ->setUnitOfMeasureCode(SapOrder::UNIT_PTC_CODE)
                ->setChargeBonus(true)
                ->setDeliveryFromPoint($this->getPropertyValueByCode($order, 'DELIVERY_PLACE_CODE'))
                ->setDeliveryShipmentPoint($this->getPropertyValueByCode($order, 'SHIPMENT_PLACE_CODE'));

            $collection->add($offer);
            $position++;
        }

        $orderDto->setProducts($collection);
    }

    /**
     * @param Order $order
     * @param string $point
     *
     * @return DeliveryAddress|OutDeliveryAddress
     */
    private function getDeliveryAddress(Order $order, string $point = '')
    {
        $city = $this->getPropertyValueByCode($order, 'CITY_CODE');
        $regionCode = $this->locationService->getRegionCode($city);
        $regionCode = preg_match('~\D~', '', $regionCode);

        return (new OutDeliveryAddress())
            ->setRegionCode($regionCode)
            ->setPostCode('')
            ->setCityName($this->getPropertyValueByCode($order, 'CITY'))
            ->setStreetName($this->getPropertyValueByCode($order, 'STREET'))
            ->setStreetPrefix('')
            ->setHouse($this->getPropertyValueByCode($order, 'HOUSE'))
            ->setHousing('')
            ->setBuilding($this->getPropertyValueByCode($order, 'BUILDING'))
            ->setOwnerShip('')
            ->setFloor($this->getPropertyValueByCode($order, 'FLOOR'))
            ->setRoomNumber($this->getPropertyValueByCode($order, 'APARTMENT'))
            ->setDeliveryPointCode($point);
    }

    /**
     * @param Order $order
     *
     * @return string
     * @throws NotFoundOrderDeliveryException
     * @throws NotFoundOrderShipmentException
     */
    private function getDeliveryTypeCode(Order $order): string
    {
        if ($this->getPropertyValueByCode($order, 'REGION_COURIER_FROM_DC')) {
            return SapOrder::DELIVERY_TYPE_ROUTE;
        }

        $shipment = BxCollection::getOrderExternalShipment($order->getShipmentCollection());

        if (null === $shipment) {
            throw new NotFoundOrderShipmentException(
                sprintf(
                    'Отгрузка для заказа #%s не найдена',
                    $order->getId()
                )
            );
        }

        switch ($shipment->getDelivery()->getCode()) {
            case DeliveryService::INNER_DELIVERY_CODE:
                $location = $this->getPropertyValueByCode($order, 'CITY_CODE');
                $deliveryId = $shipment->getDeliveryId();
                $deliveryZone = $this->deliveryService->getDeliveryZoneCodeByLocation($location, $deliveryId);

                switch ($deliveryZone) {
                    case DeliveryService::ZONE_1:
                        return SapOrder::DELIVERY_TYPE_COURIER_RC;
                    case DeliveryService::ZONE_2:
                        return SapOrder::DELIVERY_TYPE_PICKUP;
                }

                break;
            case DeliveryService::INNER_PICKUP_CODE:
                return SapOrder::DELIVERY_TYPE_PICKUP;
                break;
            case DeliveryService::DPD_DELIVERY_CODE:
                return SapOrder::DELIVERY_TYPE_CONTRACTOR . '_' . SapOrder::DELIVERY_TYPE_CONTRACTOR_DELIVERY;
                break;
            case DeliveryService::DPD_PICKUP_CODE:
                return SapOrder::DELIVERY_TYPE_CONTRACTOR . '_' . SapOrder::DELIVERY_TYPE_CONTRACTOR_PICKUP;
                break;
        }

        throw new NotFoundOrderDeliveryException('Не найден тип доставки');
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
            $this->log()->error(sprintf(
                'Интервал %s не найден для заказа %s',
                $orderDto->getDeliveryTimeInterval(),
                $orderDto->getId()
            ));
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
        $statusPayed = SapOrder::ORDER_PAYMENT_STATUS_NOT_PAYED === $orderDto->getPayStatus() ? 'N' : 'Y';
        $bonusPayedCount = $orderDto->getBonusPayedCount();
        $innerPayment = $order->getPaymentCollection()->getInnerPayment();
        $externalPayment = null;

        if ($innerPayment && $bonusPayedCount) {
            $innerPayment->setPaid($statusPayed);
            $innerPayment->setField('PS_SUM', $bonusPayedCount);
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
        $externalPayment->setFieldNoDemand('SUM', $orderDto->getTotalSum() - $bonusPayedCount);
    }

    /**
     * @param Order $order
     * @param OrderDtoIn $orderDto
     */
    private function setDeliveryFromDto(Order $order, OrderDtoIn $orderDto): void
    {
        /**
         * @todo
         *
         * Set order shipments from DTO
         */
    }

    /**
     * @param Order $order
     * @param OrderDtoIn $orderDto
     *
     * @throws RuntimeException
     */
    private function setBasketFromDto(Order $order, OrderDtoIn $orderDto): void
    {
        $externalItems = $orderDto->getProducts();

        /**
         * @var BasketItem $basketItem
         */
        foreach ($basketCollection = $order->getBasket()->getBasketItems() as $basketItem) {
            $article = substr($basketItem->getField('PRODUCT_XML_ID'), (strpos($basketItem->getField('PRODUCT_XML_ID'), '#') + 1) ?: 0);
            $article = ltrim($article, '0');

            $externalItem = $externalItems->filter(
                function ($item) use ($article) {
                    /**
                     * @var OrderOfferIn $item
                     */
                    return (string)$article === ltrim($item->getOfferXmlId());
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
            $this->log()->error(sprintf('Ошибка обновления товара в корзине: %s', $e->getMessage()));
        }
    }

    /**
     * @param Basket $basket
     * @param OrderOfferIn $externalItem
     *
     * @throws NotFoundProductException
     * @throws RuntimeException
     */
    private function addBasketItem(Basket $basket, OrderOfferIn $externalItem): void
    {
        /**
         * @todo
         *
         * Сделать это нормально
         */
        $itemId = 0;
        $element = [];

        try {
            $element = (new Query(ElementTable::class))
                ->setFilter([
                    'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::OFFERS),
                    'XML_ID' => ltrim($externalItem->getOfferXmlId(), '0'),
                ])
                ->setLimit(1)
                ->setSelect(['XML_ID', 'ID', 'NAME'])
                ->exec()
                ->fetch();

            $itemId = $element['ID'];

            if (!$itemId) {
                throw new NotFoundProductException(sprintf(
                    'Продукт с внешним кодом %s не найден.',
                    $externalItem->getOfferXmlId()
                ));
            }
        } catch (IblockNotFoundException | ArgumentException $e) {
            $this->log()->error(sprintf('Ошибка добавления продукта: %s', $e->getMessage()));
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
                throw new CantCreateBasketItem(implode(', ', $result->getErrorMessages()));
            }
        } catch (CantCreateBasketItem | LoaderException | ObjectNotFoundException $e) {
            $this->log()->error(sprintf(
                'Ошибка добавления товара в корзину заказа #%s: %s',
                $basket->getOrderId(),
                $e->getMessage()
            ));
        }
    }

    /**
     * @param Order $order
     * @param OrderDtoIn $orderDto
     *
     * @throws NotFoundOrderShipmentException
     * @throws NotFoundOrderStatusException
     * @throws ArgumentException
     */
    private function setStatusFromDto(Order $order, OrderDtoIn $orderDto)
    {
        $shipment = BxCollection::getOrderExternalShipment($order->getShipmentCollection());

        if (null === $shipment) {
            throw new NotFoundOrderShipmentException(
                sprintf(
                    'Отгрузка для заказа #%s не найдена',
                    $order->getId()
                )
            );
        }

        $deliveryCode = $shipment->getDelivery()->getCode();
        $order->setField('STATUS_ID', $this->statusService->getStatusBySapStatus($deliveryCode, $orderDto->getStatus()));
    }

    /**
     * @param string $outPrefix
     */
    public function setOutPrefix(string $outPrefix): void
    {
        $this->outPrefix = $outPrefix;
    }
}
