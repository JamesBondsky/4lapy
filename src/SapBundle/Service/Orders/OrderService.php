<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Service\Orders;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Order;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaymentCollection;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\DeliveryBundle\Exception\NotFoundException;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\DeliveryBundle\Service\IntervalService;
use FourPaws\Helpers\BxCollection;
use FourPaws\Helpers\DateHelper;
use FourPaws\Location\LocationService;
use FourPaws\SaleBundle\Service\OrderService as BaseOrderService;
use FourPaws\SapBundle\Dto\In\Orders\Order as OrderDtoIn;
use FourPaws\SapBundle\Dto\Out\Orders\DeliveryAddress as OutDeliveryAddress;
use FourPaws\SapBundle\Dto\Out\Orders\Order as OrderDtoOut;
use FourPaws\SapBundle\Dto\Out\Orders\OrderOffer;
use FourPaws\SapBundle\Enum\SapOrderEnum;
use FourPaws\SapBundle\Exception\NotFoundOrderDeliveryException;
use FourPaws\SapBundle\Exception\NotFoundOrderPaySystemException;
use FourPaws\SapBundle\Exception\NotFoundOrderShipmentException;
use FourPaws\SapBundle\Exception\NotFoundOrderUserException;
use FourPaws\SapBundle\Source\SourceMessage;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Repository\UserRepository;
use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class OrderService
 *
 * @package FourPaws\SapBundle\Service\Orders
 */
class OrderService implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;
    
    /**
     * @var BaseOrderService
     */
    private $baseOrderService;
    /**
     * @var ArrayTransformerInterface
     */
    private $arrayTransformer;
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
     * OrderService constructor.
     *
     * @param BaseOrderService          $baseOrderService
     * @param DeliveryService           $deliveryService
     * @param LocationService           $locationService
     * @param ArrayTransformerInterface $arrayTransformer
     * @param SerializerInterface       $serializer
     * @param Filesystem                $filesystem
     * @param UserRepository            $userRepository
     * @param IntervalService           $intervalService
     */
    public function __construct(
        BaseOrderService $baseOrderService,
        DeliveryService $deliveryService,
        LocationService $locationService,
        ArrayTransformerInterface $arrayTransformer,
        SerializerInterface $serializer,
        Filesystem $filesystem,
        UserRepository $userRepository,
        IntervalService $intervalService
    ) {
        $this->baseOrderService = $baseOrderService;
        $this->arrayTransformer = $arrayTransformer;
        $this->serializer = $serializer;
        $this->filesystem = $filesystem;
        $this->userRepository = $userRepository;
        $this->deliveryService = $deliveryService;
        $this->locationService = $locationService;
        $this->intervalService = $intervalService;
    }
    
    /**
     * @param Order $order
     *
     * @throws IOException
     * @throws ObjectNotFoundException
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
            ->setClientFio(BxCollection::getOrderPropertyByCode($order->getPropertyCollection(), 'NAME')->getValue())
            ->setClientPhone(BxCollection::getOrderPropertyByCode($order->getPropertyCollection(), 'PHONE')->getValue())
            ->setClientOrderPhone(BxCollection::getOrderPropertyByCode($order->getPropertyCollection(),
                'PHONE_ALT')->getValue())
            ->setClientComment($order->getField('USER_DESCRIPTION'))
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
     * @return Order
     */
    public function transformDtoToOrder(OrderDtoIn $orderDto): Order
    {
        $orderArray = $this->arrayTransformer->toArray($orderDto);
        
        $order = Order::load($orderArray['id']);
        
        /**
         * @todo
         *
         * Do some magic with order
         */
        
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
    public function getFileName(Order $order): string
    {
        return sprintf('/%s/%s-%s.xml', trim($this->outPath, '/'), $order->getDateInsert()->format('Ymd'),
            $order->getId());
    }
    
    /**
     * @param string $outPath
     *
     * @throws IOException
     * @return OrderService
     */
    public function setOutPath(string $outPath): OrderService
    {
        if (!$this->filesystem->exists($outPath)) {
            $this->filesystem->mkdir($outPath, '0775');
        }
        
        $this->outPath = $outPath;
        
        return $this;
    }
    
    /**
     * @param OrderDtoOut       $dto
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
                ->setPayType(SapOrderEnum::ORDER_PAYMENT_ONLINE_CODE)
                ->setPayStatus(SapOrderEnum::ORDER_PAYMENT_STATUS_PRE_PAYED)
                ->setPayHoldTransaction($externalPayment->getField('PS_INVOICE_ID'))
                ->setPayHoldDate(DateHelper::convertToDateTime($externalPayment->getField('PS_RESPONSE_DATE')))
                ->setPrePayedSum($externalPayment->getSum())
                ->setPayMerchantCode(SapOrderEnum::ORDER_PAYMENT_ONLINE_MERCHANT_ID);
        } else {
            $dto->setPayType('')
                ->setPayStatus(SapOrderEnum::ORDER_PAYMENT_STATUS_NOT_PAYED);
        }
    }
    
    /**
     * @param OrderDtoOut $orderDto
     * @param Order       $order
     */
    private function populateOrderDtoDelivery(OrderDtoOut $orderDto, Order $order): void
    {
        $shipment = BxCollection::getOrderExternalShipment($order->getShipmentCollection());
        
        if (null === $shipment) {
            throw new NotFoundOrderShipmentException('Не найдена отгрузка у заказа');
        }
        
        $deliveryTypeCode = $this->getDeliveryTypeCode($order);
        $contractorDeliveryTypeCode = '';
        
        if (strpos($deliveryTypeCode, '_')) {
            $deliveryType = explode('_', $deliveryTypeCode);
            $deliveryTypeCode = $deliveryType[0];
            $contractorDeliveryTypeCode = $deliveryType[1];
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
            $interval = $this->intervalService->getIntervalCode($this->getPropertyValueByCode($order,'DELIVERY_INTERVAL'));
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
            ->setContractorCode($deliveryTypeCode === SapOrderEnum::DELIVERY_TYPE_CONTRACTOR ? SapOrderEnum::DELIVERY_CONTRACTOR_CODE : '');
    }
    
    /**
     * @param OrderDtoOut $orderDto
     * @param Order       $order
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
                $xmlId = explode('#', $xmlId)[1];
            }
            
            $offer = (new OrderOffer())
                ->setPosition($position)
                ->setOfferXmlId($xmlId)
                ->setUnitPrice($basketItem->getBasePrice())
                ->setQuantity($basketItem->getQuantity())
                /**
                 * Только штуки
                 */
                ->setUnitOfMeasureCode(SapOrderEnum::UNIT_PTC_CODE)
                ->setChargeBonus(true)
                ->setDeliveryFromPoint($this->getPropertyValueByCode($order, 'DELIVERY_PLACE_CODE'))
                ->setDeliveryShipmentPoint($this->getPropertyValueByCode($order, 'SHIPMENT_PLACE_CODE'));
            
            $collection->add($offer);
            $position++;
        }
        
        $orderDto->setProducts($collection);
    }
    
    /**
     * @param Order  $order
     * @param string $point
     *
     * @return OutDeliveryAddress
     */
    private function getDeliveryAddress(Order $order, string $point = ''): OutDeliveryAddress
    {
        $city = BxCollection::getOrderPropertyByCode($order->getPropertyCollection(), 'CITY_CODE')->getValue();
        $regionCode = preg_match('~\D~', '', $this->locationService->getRegionCode($city));
        
        $address = (new OutDeliveryAddress())
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
        
        return $address;
    }
    
    /**
     * @param Order $order
     *
     * @return string
     */
    private function getDeliveryTypeCode(Order $order)
    {
        if ($this->getPropertyValueByCode($order, 'REGION_COURIER_FROM_DC')) {
            return SapOrderEnum::DELIVERY_TYPE_ROUTE;
        }
        
        $shipment = BxCollection::getOrderExternalShipment($order->getShipmentCollection());
        
        switch ($shipment->getDelivery()->getCode()) {
            case DeliveryService::INNER_DELIVERY_CODE:
                $location = $this->getPropertyValueByCode($order, 'CITY_CODE');
                $deliveryId = $shipment->getDeliveryId();
                $deliveryZone = $this->deliveryService->getDeliveryZoneCodeByLocation($location, $deliveryId);
                
                switch ($deliveryZone) {
                    case DeliveryService::ZONE_1:
                        return SapOrderEnum::DELIVERY_TYPE_COURIER_RC;
                    case DeliveryService::ZONE_2:
                        return SapOrderEnum::DELIVERY_TYPE_PICKUP;
                }
                
                break;
            case DeliveryService::INNER_PICKUP_CODE:
                return SapOrderEnum::DELIVERY_TYPE_PICKUP;
                break;
            case DeliveryService::DPD_DELIVERY_CODE:
                return SapOrderEnum::DELIVERY_TYPE_CONTRACTOR . '_' . SapOrderEnum::DELIVERY_TYPE_CONTRACTOR_DELIVERY;
                break;
            case DeliveryService::DPD_PICKUP_CODE:
                return SapOrderEnum::DELIVERY_TYPE_CONTRACTOR . '_' . SapOrderEnum::DELIVERY_TYPE_CONTRACTOR_PICKUP;
                break;
        }
        
        throw new NotFoundOrderDeliveryException('Не найден тип доставки');
    }
    
    /**
     * @param Order  $order
     * @param string $code
     *
     * @return string
     */
    private function getPropertyValueByCode(Order $order, string $code): string
    {
        $propertyValue = BxCollection::getOrderPropertyByCode($order->getPropertyCollection(), $code);
        
        return $propertyValue ? ($propertyValue->getValue() ?? '') : '';
    }
}
