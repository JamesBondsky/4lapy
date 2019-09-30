<?php

namespace FourPaws\SapBundle\Service\Orders;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\BitrixUtils;
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
use Bitrix\Sale\Delivery\Services\Manager as DeliveryManager;
use Bitrix\Sale\Order;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaymentCollection;
use Bitrix\Sale\PropertyValueCollection;
use Bitrix\Sale\PropertyValueCollectionBase;
use Bitrix\Sale\Shipment;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use FourPaws\App\Application;
use FourPaws\App\Env;
use FourPaws\AppBundle\Entity\UserFieldEnumValue;
use FourPaws\DeliveryBundle\Exception\NotFoundException;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\DeliveryBundle\Service\IntervalService;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\External\Dostavista\Model\CancelOrder;
use FourPaws\Helpers\BxCollection;
use FourPaws\Helpers\DateHelper;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\KioskBundle\Service\KioskService;
use FourPaws\LocationBundle\LocationService;
use FourPaws\PersonalBundle\Service\StampService;
use FourPaws\SaleBundle\Discount\Utils\Manager;
use FourPaws\SaleBundle\Enum\OrderPayment;
use FourPaws\SaleBundle\Repository\Table\AnimalShelterTable;
use FourPaws\SaleBundle\Service\BasketService;
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
use FourPaws\StoreBundle\Service\ScheduleResultService;
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
 * @todo    divide to base -> out
 *                         -> in
 *
 * @package FourPaws\SapBundle\Service\Orders
 */
class OrderService implements LoggerAwareInterface, SapOutInterface
{
    use LazyLoggerAwareTrait, SapOutFile;

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
     * @var StampService
     */
    private $stampService;

    /**
     * @var int
     */
    private $lastOrderId = null;

    /**
     * @var PropertyValueCollectionBase
     */
    private $propertyCollection = null;

    /**
     * OrderService constructor.
     *
     * @param DeliveryService $deliveryService
     * @param LocationService $locationService
     * @param SerializerInterface $serializer
     * @param Filesystem $filesystem
     * @param UserRepository $userRepository
     * @param IntervalService $intervalService
     * @param StatusService $statusService
     * @param BasketService $basketService
     * @param StampService $stampService
     */
    public function __construct(
        DeliveryService $deliveryService,
        LocationService $locationService,
        SerializerInterface $serializer,
        Filesystem $filesystem,
        UserRepository $userRepository,
        IntervalService $intervalService,
        StatusService $statusService,
        BasketService $basketService,
        StampService $stampService
    )
    {
        $this->serializer = $serializer;
        $this->userRepository = $userRepository;
        $this->deliveryService = $deliveryService;
        $this->locationService = $locationService;
        $this->intervalService = $intervalService;
        $this->statusService = $statusService;

        $this->setFilesystem($filesystem);
        $this->basketService = $basketService;
        $this->stampService = $stampService;
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
     * @return PropertyValueCollectionBase
     * @throws ArgumentException
     * @throws NotImplementedException
     */
    private function getPropertyCollection(Order $order): PropertyValueCollectionBase
    {
        $needReloadProperties = false;
        if ($this->lastOrderId == null || $this->lastOrderId != $order->getId()) {
            $this->lastOrderId = $order->getId();
            $needReloadProperties = true;
        }

        if ($needReloadProperties || $this->propertyCollection == null) {
            $this->propertyCollection = $order->getPropertyCollection();
        }

        return $this->propertyCollection;
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

        $this->getPropertyCollection($order);

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
         * KIOS – заказ создан через киоск;
         */
        $orderSource = OrderDtoOut::ORDER_SOURCE_SITE;
        if ($this->getPropertyValueByCode($order, 'FROM_APP') === 'Y') {
            switch ($this->getPropertyValueByCode($order, 'FROM_APP_DEVICE')) {
                case 'android':
                    $orderSource = OrderDtoOut::ORDER_SOURCE_MOBILE_APP_ANDROID;
                    break;
                case 'ios':
                    $orderSource = OrderDtoOut::ORDER_SOURCE_MOBILE_APP_IOS;
                    break;
            }
        }
        if(KioskService::isKioskMode()){
            $orderSource = OrderDtoOut::ORDER_SOURCE_KIOSK;
        }

        $description = \trim(\implode("\n",
            [
                $order->getField('USER_DESCRIPTION'),
                $order->getField('COMMENTS')
            ]
        )) ?: '';

        $deliveryTypeCode = $this->getDeliveryCode($order);
        if ($deliveryTypeCode == DeliveryService::INNER_DELIVERY_CODE || $deliveryTypeCode == DeliveryService::DELIVERY_DOSTAVISTA_CODE) {
            $this->populateOrderDtoUserCoords($orderDto, $order);
        }

        $orderDto
            ->setId($order->getField('ACCOUNT_NUMBER'))
            ->setDateInsert(DateHelper::convertToDateTime($order->getDateInsert()
                ->toUserTime()))
            ->setClientId($order->getUserId())
            ->setClientFio(\str_replace('#', '', $this->getPropertyValueByCode($order, 'NAME')))
            ->setClientPhone(PhoneHelper::formatPhone(
                $this->getPropertyValueByCode($order, 'PHONE'),
                PhoneHelper::FORMAT_URL
            ))
            ->setClientOrderPhone(PhoneHelper::formatPhone(
                $this->getPropertyValueByCode($order, 'PHONE_ALT'),
                PhoneHelper::FORMAT_URL
            ))
            ->setClientComment($description)
            ->setOrderSource($orderSource)
            ->setBonusCard($this->getPropertyValueByCode($order, 'DISCOUNT_CARD'))
            ->setAvatarEmail($this->getPropertyValueByCode($order, 'OPERATOR_EMAIL'))
            ->setAvatarDepartment($this->getPropertyValueByCode($order, 'OPERATOR_SHOP'));

        if ($this->deliveryService->isDobrolapDeliveryCode($deliveryTypeCode)) {
            $orderDto->setFastDeliv('P');
        } else {
            $isFastDeliv = $this->isFastDelivery($this->getPropertyValueByCode($order, 'SCHEDULE_REGULARITY'));
            $orderDto->setFastDeliv($isFastDeliv ? 'X' : '');
        }


        if (Env::isStage()) {
            $orderDto
                ->setClientPhone(SapOrder::TEST_PHONE)
                ->setClientOrderPhone(SapOrder::TEST_PHONE)
                ->setClientComment(SapOrder::TEST_COMMENT);
        }

        $this->populateOrderDtoPayment($orderDto, $order->getPaymentCollection());
        $this->populateOrderDtoDelivery($orderDto, $order);
        $this->populateOrderDtoProducts($orderDto, $order);
        $this->populateOrderDtoCouponNumber($orderDto, $order);

        $xml = $this->serializer->serialize($orderDto, 'xml');

        return new SourceMessage($this->getMessageId($order), OrderDtoOut::class, $xml);
    }

    /**
     * @param OrderDtoIn $orderDto
     *
     * @throws \Bitrix\Main\NotSupportedException
     * @throws NotFoundOrderDeliveryException
     * @throws NotFoundProductException
     * @throws SystemException
     * @throws ArgumentNullException
     * @throws NotImplementedException
     * @throws ArgumentException
     * @throws NotFoundOrderException
     * @throws NotFoundOrderShipmentException
     * @throws NotFoundOrderStatusException
     * @throws RuntimeException
     * @throws NotFoundOrderPaySystemException
     * @throws Exception
     * @throws ObjectNotFoundException
     * @throws ArgumentOutOfRangeException
     *
     * @return Order
     */
    public function transformDtoToOrder(OrderDtoIn $orderDto): Order
    {
        /** @var Order $order */
        $order = Order::loadByAccountNumber($orderDto->getId());

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
        $this->setBasketFromDto($order, $orderDto);
        $this->setDeliveryFromDto($order, $orderDto);
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
            $order->getField('ACCOUNT_NUMBER')
        );
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
        if ($externalPayment->getPaySystem()
                ->getField('CODE') === OrderPayment::PAYMENT_ONLINE) {
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
     * @param Order       $order
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
            [
                $deliveryTypeCode,
                $contractorDeliveryTypeCode
            ] = \explode('_', $deliveryTypeCode);
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

        if ($this->deliveryService->isDobrolapDeliveryCode($this->getDeliveryCode($order))) {
            $deliveryAddress = new OutDeliveryAddress();
            $shelterBarcode = $this->getPropertyValueByCode($order, 'DOBROLAP_SHELTER');
            $shelter = AnimalShelterTable::getByBarcode($shelterBarcode);
            if ($shelter) {
                $deliveryAddress->setCityName($shelter['city']);
                $deliveryAddress->setStreetName($shelter['name']);
            }
        } else {
            $deliveryAddress = $this->getDeliveryAddress($order);
        }

        $orderDto
            ->setCommunicationType($this->getPropertyValueByCode($order, 'COM_WAY'))
            ->setDeliveryType($deliveryTypeCode)
            ->setContractorDeliveryType($contractorDeliveryTypeCode)
            ->setDeliveryTimeInterval($interval)
            ->setDeliveryAddress($deliveryAddress)
            ->setDeliveryAddressOrPoint($deliveryAddress->__toString())
            ->setContractorCode($deliveryTypeCode
                                === SapOrder::DELIVERY_TYPE_CONTRACTOR ? SapOrder::DELIVERY_CONTRACTOR_CODE : '');

        if ($deliveryDate) {
            $orderDto->setDeliveryDate($deliveryDate);
        }
    }

    /**
     * @param OrderDtoOut $orderDto
     * @param Order       $order
     *
     * @throws \FourPaws\SapBundle\Exception\NotFoundOrderDeliveryException
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
            if ($basketItem->isDelay()) {
                continue;
            }

            $offer = (new OrderOffer())
                ->setOfferXmlId($this->basketService->getBasketItemXmlId($basketItem))
                ->setUnitPrice($basketItem->getPrice())
                /**
                 * Только штуки
                 */
                ->setUnitOfMeasureCode(SapOrder::UNIT_PTC_CODE)
                ->setChargeBonus((bool)$this->getBasketPropertyValueByCode($basketItem, 'HAS_BONUS'))
                ->setDeliveryShipmentPoint($this->getBasketPropertyValueByCode($basketItem, 'SHIPMENT_PLACE_CODE'))
                ->setDeliveryFromPoint($this->getPropertyValueByCode($order, 'DELIVERY_PLACE_CODE'));

            if ($this->stampService::IS_STAMPS_OFFER_ACTIVE) {
                $useStamps = $this->getBasketPropertyValueByCode($basketItem, 'USE_STAMPS');
                $discountStamps = 0;
                if ($useStamps) {
                    $maxStampsLevel = $this->getBasketPropertyValueByCode($basketItem, 'MAX_STAMPS_LEVEL');
                    if ($maxStampsLevelArr = unserialize($maxStampsLevel)) {
                        $offer->setExchangeName($maxStampsLevelArr['key'] . '*P');
                        $discountStamps = $this->stampService->parseLevelKey($maxStampsLevelArr['key'])['discountStamps'];
                    } else {
                        $useStamps = false;
                    }
                }
            }

            $hasBonus = $this->getBasketPropertyValueByCode($basketItem, 'HAS_BONUS');
            $quantity = $basketItem->getQuantity();
            if ($hasBonus && $hasBonus < $quantity) {
                $quantity -= $hasBonus;
                $detachedOffer = clone $offer;
                $detachedOffer
                    ->setQuantity($hasBonus)
                    ->setChargeBonus((bool)$hasBonus)
                    ->setPosition($position);

                if ($this->stampService::IS_STAMPS_OFFER_ACTIVE && $useStamps) {
                    $detachedOffer->setStampsQuantity($hasBonus * $discountStamps);
                }

                $collection->add($detachedOffer);
                $hasBonus = 0;
                $position++;
            }

            if ($this->stampService::IS_STAMPS_OFFER_ACTIVE && $useStamps) {
                // $offer->setStampsQuantity($maxStampsLevelArr['value']); todo проблематично использовать, так как есть разделение по бонусам
                $offer->setStampsQuantity($quantity * $discountStamps);
            }

            $offer->setQuantity($quantity);
            $isPseudoActionPropValue = $this->getBasketPropertyValueByCode($basketItem, 'IS_PSEUDO_ACTION');
            $isPseudoAction = BitrixUtils::BX_BOOL_TRUE === $isPseudoActionPropValue;
            if ($isPseudoAction)
            {
                $offer->setChargeBonus(true);
            }
            else
            {
                $offer->setChargeBonus((bool)$hasBonus);
            }
            $offer->setPosition($position);
            $collection->add($offer);
            $position++;
        }

        $this->addBasketDeliveryItem($order, $collection);

        $orderDto->setProducts($collection);
    }

    /**
     * @param OrderDtoOut $orderDto
     * @param Order $order
     * @throws ArgumentException
     * @throws NotImplementedException
     */
    public function populateOrderDtoCouponNumber(OrderDtoOut $orderDto, Order $order)
    {
        $promocode = BxCollection::getOrderPropertyByCode($this->getPropertyCollection($order), 'PROMOCODE');
        if ($promocode && ($promocodeValue = $promocode->getValue()) && strpos($promocodeValue, 's') === 0)
        {
            $orderDto->setCouponNumber($promocodeValue);
        }
    }

    /**
     * @param OrderDtoOut $orderDto
     * @param Order $order
     * @throws ArgumentException
     * @throws NotImplementedException
     */
    public function populateOrderDtoUserCoords(OrderDtoOut $orderDto, Order $order)
    {
        $coords = BxCollection::getOrderPropertyByCode($this->getPropertyCollection($order), 'USER_COORDS');
        if ($coords && ($coordsValue = $coords->getValue()) && $coordsValue != null && $coordsValue != '') {
            $arCoords = explode(',', $coordsValue);
            $orderDto->setLatitude($arCoords[0]);
            $orderDto->setLongitude($arCoords[1]);
        }
    }

    /**
     * @param Order $order
     *
     * @return DeliveryAddress|OutDeliveryAddress
     * @throws Exception
     */
    public function getDeliveryAddress(Order $order)
    {
        $city = $this->getPropertyValueByCode($order, 'CITY_CODE');
        $deliveryPlaceCode = '';
        $deliveryCode = $this->getDeliveryCode($order);
        if ($deliveryCode === DeliveryService::INNER_PICKUP_CODE) {
            $deliveryPlaceCode = $this->getPropertyValueByCode($order, 'DELIVERY_PLACE_CODE');
        }

        return (new OutDeliveryAddress())
            ->setDeliveryPlaceCode($deliveryPlaceCode)
            ->setRegion($this->getPropertyValueByCode($order, 'REGION'))
            ->setRegionCode($this->locationService->getRegionNumberCode($city))
            ->setPostCode($this->getPropertyValueByCode($order, 'ZIP_CODE'))
            ->setCityName(
                implode(', ', \array_filter(
                    [
                        $this->getPropertyValueByCode($order, 'AREA'),
                        $this->getPropertyValueByCode($order, 'CITY'),
                    ]
                ))
            )
            ->setStreetName($this->getPropertyValueByCode($order, 'STREET'))
            ->setStreetPrefix($this->getPropertyValueByCode($order, 'STREET_PREFIX'))
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
        $code = $this->getDeliveryCode($order);
        $deliveryZone = $this->getDeliveryZone($order);

        if (
            (
                in_array($deliveryZone, DeliveryService::getZonesTwo()) ||
                mb_strpos($deliveryZone, DeliveryService::ADD_DELIVERY_ZONE_CODE_PATTERN) !== false ||
                mb_strpos($deliveryZone, DeliveryService::ZONE_MOSCOW_DISTRICT_CODE_PATTERN) !== false
            )
            && $this->getPropertyValueByCode($order, 'REGION_COURIER_FROM_DC') === 'Y'
        ) {
            return SapOrder::DELIVERY_TYPE_ROUTE;
        }

        $isFastOrder = $this->getPropertyValueByCode($order, 'IS_FAST_ORDER') === 'Y';
        if ($isFastOrder) {
            $code = '';
        }

        switch ($code) {
            case DeliveryService::INNER_DELIVERY_CODE:
                switch ($deliveryZone) {
                    case DeliveryService::ZONE_1:
                    case DeliveryService::ZONE_5:
                    case DeliveryService::ZONE_6:
                    case DeliveryService::ZONE_IVANOVO:
                        return SapOrder::DELIVERY_TYPE_COURIER_RC;
                    case DeliveryService::ZONE_2:
                    case DeliveryService::ZONE_NIZHNY_NOVGOROD:
                    case DeliveryService::ZONE_NIZHNY_NOVGOROD_REGION:
                    case DeliveryService::ZONE_VLADIMIR:
                    case DeliveryService::ZONE_VLADIMIR_REGION:
                    case DeliveryService::ZONE_VORONEZH:
                    case DeliveryService::ZONE_VORONEZH_REGION:
                    case DeliveryService::ZONE_YAROSLAVL:
                    case DeliveryService::ZONE_YAROSLAVL_REGION:
                    case DeliveryService::ZONE_TULA:
                    case DeliveryService::ZONE_TULA_REGION:
                    case DeliveryService::ZONE_KALUGA:
                    case DeliveryService::ZONE_KALUGA_REGION:
                    case DeliveryService::ZONE_IVANOVO_REGION:
                        return SapOrder::DELIVERY_TYPE_COURIER_SHOP;
                    default:
                        if (
                            mb_strpos($deliveryZone, DeliveryService::ADD_DELIVERY_ZONE_CODE_PATTERN) !== false ||
                            mb_strpos($deliveryZone, DeliveryService::ZONE_MOSCOW_DISTRICT_CODE_PATTERN) !== false
                        ) {
                            return SapOrder::DELIVERY_TYPE_COURIER_SHOP;
                        }
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
            case DeliveryService::DELIVERY_DOSTAVISTA_CODE:
                return SapOrder::DELIVERY_TYPE_DOSTAVISTA;
                break;
            case DeliveryService::DOBROLAP_DELIVERY_CODE:
                return SapOrder::DELIVERY_TYPE_COURIER_RC;
                break;
            default:
                switch ($deliveryZone) {
                    case DeliveryService::ZONE_1:
                    case DeliveryService::ZONE_5:
                    case DeliveryService::ZONE_6:
                    case DeliveryService::ZONE_IVANOVO:
                        return SapOrder::DELIVERY_TYPE_COURIER_RC;
                    case DeliveryService::ZONE_2:
                    case DeliveryService::ZONE_NIZHNY_NOVGOROD:
                    case DeliveryService::ZONE_NIZHNY_NOVGOROD_REGION:
                    case DeliveryService::ZONE_VLADIMIR:
                    case DeliveryService::ZONE_VLADIMIR_REGION:
                    case DeliveryService::ZONE_VORONEZH:
                    case DeliveryService::ZONE_VORONEZH_REGION:
                    case DeliveryService::ZONE_YAROSLAVL:
                    case DeliveryService::ZONE_YAROSLAVL_REGION:
                    case DeliveryService::ZONE_TULA:
                    case DeliveryService::ZONE_TULA_REGION:
                    case DeliveryService::ZONE_KALUGA:
                    case DeliveryService::ZONE_KALUGA_REGION:
                    case DeliveryService::ZONE_IVANOVO_REGION:
                        return SapOrder::DELIVERY_TYPE_COURIER_SHOP;
                    case DeliveryService::ZONE_3:
                        return SapOrder::DELIVERY_TYPE_PICKUP;
                    default:
                        if (
                            mb_strpos($deliveryZone, DeliveryService::ADD_DELIVERY_ZONE_CODE_PATTERN) !== false ||
                            mb_strpos($deliveryZone, DeliveryService::ZONE_MOSCOW_DISTRICT_CODE_PATTERN) !== false
                        ) {
                            return SapOrder::DELIVERY_TYPE_COURIER_SHOP;
                        }
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
     *
     * @return string
     *
     * @throws NotFoundOrderShipmentException
     */
    private function getDeliveryCode(Order $order): string
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

        return $shipment->getDelivery()
            ->getCode();
    }

    /**
     * @param Order  $order
     * @param string $code
     *
     * @return string
     */
    public function getPropertyValueByCode(Order $order, string $code): string
    {
        $propertyValue = BxCollection::getOrderPropertyByCode($this->getPropertyCollection($order), $code);

        return $propertyValue ? ($propertyValue->getValue() ?? '') : '';
    }

    /**
     * @param Order      $order
     * @param OrderDtoIn $orderDto
     *
     * @throws RuntimeException
     */
    private function setPropertiesFromDto(Order $order, OrderDtoIn $orderDto): void
    {
        $deliveryAddress = $orderDto->getDeliveryAddress();

        if ($orderDto->getClientFio()) {
            $this->setPropertyValue($this->getPropertyCollection($order), 'NAME', $orderDto->getClientFio());
        }
        $this->setPropertyValue(
            $this->getPropertyCollection($order),
            'PHONE',
            PhoneHelper::formatPhone($orderDto->getClientPhone(), PhoneHelper::FORMAT_SHORT)
        );
        $this->setPropertyValue(
            $this->getPropertyCollection($order),
            'PHONE_ALT',
            PhoneHelper::formatPhone($orderDto->getClientOrderPhone(), PhoneHelper::FORMAT_SHORT)
        );
        $this->setPropertyValue($this->getPropertyCollection($order), 'DELIVERY_DATE', $orderDto->getDeliveryDate()
            ->format('d.m.Y'));

        if ($deliveryAddress) {
            $this->setPropertyValue($this->getPropertyCollection($order), 'CITY', $deliveryAddress->getCityName());
            $this->setPropertyValue($this->getPropertyCollection($order), 'STREET', $deliveryAddress->getStreetName());
            $this->setPropertyValue($this->getPropertyCollection($order), 'HOUSE', $deliveryAddress->getHouse());
            $this->setPropertyValue($this->getPropertyCollection($order), 'BUILDING', $deliveryAddress->getHousing());
            $this->setPropertyValue($this->getPropertyCollection($order), 'FLOOR', $deliveryAddress->getFloor());
            $this->setPropertyValue($this->getPropertyCollection($order), 'APARTMENT', $deliveryAddress->getRoomNumber());
        }
    }

    /**
     * @param PropertyValueCollection $collection
     * @param string                  $code
     * @param string                  $value
     */
    public function setPropertyValue(PropertyValueCollection $collection, string $code, string $value): void
    {
        $propertyValue = BxCollection::getOrderPropertyByCode($collection, $code);

        if ($propertyValue) {
            $propertyValue->setValue($value);
        }
    }

    /**
     * @param Order      $order
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
        $statusPayed = (empty($orderDto->getPayStatus())
                        || SapOrder::ORDER_PAYMENT_STATUS_NOT_PAYED === $orderDto->getPayStatus())
            ? 'N'
            : 'Y';

        $bonusPayedCount = $orderDto->getBonusPayedCount();
        $innerPayment = $order->getPaymentCollection()
            ->getInnerPayment();
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
     * @param Order      $order
     * @param OrderDtoIn $orderDto
     *
     * @throws \Bitrix\Main\NotSupportedException
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws NotFoundOrderDeliveryException
     * @throws Exception
     * @throws SystemException
     */
    private function setDeliveryFromDto(Order $order, OrderDtoIn $orderDto): void
    {
        $deliveryType = $orderDto->getDeliveryType();

        $deliveryCode = null;
        $currentDeliveryCode = null;

        switch ($deliveryType) {
            case SapOrder::DELIVERY_TYPE_COURIER_RC:
            case SapOrder::DELIVERY_TYPE_COURIER_SHOP:
            case SapOrder::DELIVERY_TYPE_ROUTE:
                $deliveryCode = DeliveryService::INNER_DELIVERY_CODE;
                break;
            case SapOrder::DELIVERY_TYPE_PICKUP:
            case SapOrder::DELIVERY_TYPE_PICKUP_POSTPONE:
                $deliveryCode = DeliveryService::INNER_PICKUP_CODE;
                break;
            case SapOrder::DELIVERY_TYPE_CONTRACTOR:
                /** @noinspection NullPointerExceptionInspection */
                $deliveryCode = ($orderDto->getDeliveryAddress()
                                 && $orderDto->getDeliveryAddress()
                                     ->getDeliveryPointCode())
                    ? DeliveryService::DPD_PICKUP_CODE
                    : DeliveryService::DPD_DELIVERY_CODE;
                break;
            case SapOrder::DELIVERY_TYPE_DOSTAVISTA:
                $deliveryCode = DeliveryService::DELIVERY_DOSTAVISTA_CODE;
                break;
        }


        if (null === $deliveryCode) {
            throw new NotFoundOrderDeliveryException('Unknown sap delivery code');
        }

        $deliveryPrice = 0;
        foreach ($orderDto->getProducts() as $orderOffer) {
            $xmlId = \ltrim($orderOffer->getOfferXmlId(), '0');
            if ($xmlId[0] === '2') {
                $deliveryPrice = $orderOffer->getUnitPrice();
            }
        }

        $deliveryService = DeliveryManager::getObjectByCode($deliveryCode);
        /** @var Shipment $shipment */
        foreach ($order->getShipmentCollection() as $shipment) {
            if ($shipment->isSystem()) {
                continue;
            }

            /** @noinspection PhpInternalEntityUsedInspection */
            $shipment->setFields(
                [
                    'DELIVERY_ID'           => $deliveryService->getId(),
                    'PRICE_DELIVERY'        => $deliveryPrice,
                    'DELIVERY_NAME'         => $deliveryService->getName(),
                    'CUSTOM_PRICE_DELIVERY' => 'Y',
                ]
            );
        }
    }

    /**
     * @param Order      $order
     * @param OrderDtoIn $orderDto
     *
     * @throws ArgumentOutOfRangeException
     * @throws SystemException
     */
    private function setBasketFromDto(Order $order, OrderDtoIn $orderDto): void
    {
        Manager::enableExtendsDiscount();
        Manager::setExtendCalculated(true);

        /** @var array[] $externalItems */
        $externalItems = [];
        foreach ($orderDto->getProducts() as $product) {
            $xmlId = \ltrim($product->getOfferXmlId(), '0');
            if (!isset($externalItems[$xmlId])) {
                $externalItems[$xmlId] = [];
            }

            $found = false;
            /** @var OrderOfferIn $extItem */
            foreach ($externalItems[$xmlId] as $extItem) {
                if (abs($product->getUnitPrice() - $extItem->getUnitPrice()) < 0.0001) {
                    $extItem->setQuantity($product->getQuantity() + $extItem->getQuantity());
                    $found = true;
                }
            }

            if (!$found) {
                $externalItems[$xmlId][] = clone $product;
            }
        }

        /**
         * Сортируем позиции по возрастанию цены, исходя из того,
         * что в корзине позиция со скидкой всегда первая
         */
        foreach ($externalItems as $items) {
            \usort($items, function (OrderOfferIn $item1, OrderOfferIn $item2) {
                return $item1->getUnitPrice() <=> $item2->getUnitPrice();
            });
        }

        /**
         * @var BasketItem $basketItem
         */
        foreach ($basketCollection = $order->getBasket()
            ->getBasketItems() as $basketItem) {
            $article = \ltrim($this->basketService->getBasketItemXmlId($basketItem), '0');

            $externalItem = null;
            if ($externalItems[$article]) {
                $externalItem = array_shift($externalItems[$article]);
            }

            if ($externalItem) {
                $this->renewBasketItem($basketItem, $externalItem);
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

        foreach ($externalItems as $items) {
            /** @var OrderOfferIn $item */
            foreach ($items as $item) {
                try {
                    $this->addBasketItem($order->getBasket(), $item);
                } catch (NotFoundProductException|RuntimeException|SystemException $e) {
                    $this->log()->error('[' . $e->getCode() . '] ' . $e->getMessage());
                    //Если товар с внешним кодом не из спец категории, то кидаем исключение
                    if ((int)$item->getOfferXmlId() < 2000000) {
                        throw $e;
                    }
                }
            }
        }

        Manager::setExtendCalculated(false);
        Manager::disableExtendsDiscount();
    }

    /**
     * @param BasketItem   $basketItem
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
            $this->log()
                ->error(\sprintf('Ошибка обновления товара в корзине: %s', $e->getMessage()));
        }
    }

    /**
     * @param Basket       $basket
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
                    'XML_ID'    => $xmlId,
                ])
                ->setLimit(1)
                ->setSelect([
                    'XML_ID',
                    'ID',
                    'NAME'
                ])
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
            $this->log()
                ->error(\sprintf('Ошибка добавления продукта: %s', $e->getMessage()));
        }

        $context = [
            'SITE_ID'  => SITE_ID,
            'USER_ID'  => $basket->getOrder()
                ->getUserId(),
            'ORDER_ID' => $basket->getOrderId(),
        ];

        $fields = [
            'PRODUCT_ID'             => $itemId,
            'QUANTITY'               => $externalItem->getQuantity(),
            'MODULE'                 => 'catalog',
            'CURRENCY'               => 'RUB',
            'PRICE'                  => $externalItem->getUnitPrice(),
            'NAME'                   => $element['NAME'],
            'PRODUCT_PROVIDER_CLASS' => CatalogProvider::class,
        ];

        try {
            $result = CatalogBasket::addProductToBasket($basket, $fields, $context);

            if (!$result->isSuccess()) {
                throw new CantCreateBasketItem(\implode(', ', $result->getErrorMessages()));
            }
        } catch (CantCreateBasketItem | LoaderException | ObjectNotFoundException $e) {
            $this->log()
                ->error(
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
     * @return string
     * @throws ArgumentException
     * @throws \GuzzleHttp\Exception\GuzzleException
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

        $deliveryCode = $shipment->getDelivery()
            ->getCode();
        $status = $this->statusService->getStatusBySapStatus($deliveryCode, $orderDto->getStatus());

        if ($status) {
            $order->setField(
                'STATUS_ID',
                $this->statusService->getStatusBySapStatus($deliveryCode, $orderDto->getStatus())
            );
            $deliveryService = Application::getInstance()->getContainer()->get('delivery.service');
            if ($deliveryService->isDostavistaDeliveryCode($deliveryCode)) {
                /** @var OrderService $locationService */
                $dostavistaService = Application::getInstance()->getContainer()->get('dostavista.service');
                $dostavistaStatus = StatusService::STATUS_SITE_DOSTAVISTA_MAP[$orderDto->getStatus()];
                if ($dostavistaStatus == StatusService::STATUS_SITE_DOSTAVISTA_MAP[6]) {
                    $dostavistaOrder = BxCollection::getOrderPropertyByCode($this->getPropertyCollection($order), 'ORDER_ID_DOSTAVISTA')->getValue();
                    if ($dostavistaOrder) {
                        $cancelOrder = new CancelOrder();
                        $cancelOrder->bitrixOrderId = $order->getId();
                        $cancelOrder->dostavistaOrderId = $dostavistaOrder;
                        $dostavistaService->dostavistaOrderCancel($cancelOrder);
                    } else {
                        $this->log()
                            ->error(
                                \sprintf(
                                    'Номер заказа для достависты заказа %s не найден.',
                                    $order->getId()
                                )
                            );
                    }
                }
            }
        }

        return $status;
    }

    /**
     * @param BasketItem $item
     * @param string     $code
     *
     * @return string
     */
    private function getBasketPropertyValueByCode(BasketItem $item, string $code): string
    {
        return $item->getPropertyCollection()->getPropertyValues()[$code]['VALUE'] ?? '';
    }

    /**
     * @param Order           $order
     * @param ArrayCollection $collection
     *
     * @throws \FourPaws\SapBundle\Exception\NotFoundOrderDeliveryException
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
                case DeliveryService::ZONE_5:
                    $xmlId = SapOrder::DELIVERY_ZONE_5_ARTICLE;
                    break;
                case DeliveryService::ZONE_6:
                    $xmlId = SapOrder::DELIVERY_ZONE_6_ARTICLE;
                    break;
                case DeliveryService::ZONE_2:
                case DeliveryService::ZONE_NIZHNY_NOVGOROD:
                case DeliveryService::ZONE_NIZHNY_NOVGOROD_REGION:
                case DeliveryService::ZONE_VLADIMIR:
                case DeliveryService::ZONE_VLADIMIR_REGION:
                case DeliveryService::ZONE_VORONEZH:
                case DeliveryService::ZONE_VORONEZH_REGION:
                case DeliveryService::ZONE_YAROSLAVL:
                case DeliveryService::ZONE_YAROSLAVL_REGION:
                case DeliveryService::ZONE_TULA:
                case DeliveryService::ZONE_TULA_REGION:
                case DeliveryService::ZONE_KALUGA:
                case DeliveryService::ZONE_KALUGA_REGION:
                case DeliveryService::ZONE_IVANOVO:
                case DeliveryService::ZONE_IVANOVO_REGION:
                case DeliveryService::ADD_DELIVERY_ZONE_10:
                    $xmlId = SapOrder::DELIVERY_ZONE_2_ARTICLE;
                    break;
                case DeliveryService::ZONE_3:
                    $xmlId = SapOrder::DELIVERY_ZONE_3_ARTICLE;
                    break;
                case DeliveryService::ZONE_4:
                    $xmlId = SapOrder::DELIVERY_ZONE_4_ARTICLE;
                    break;
                default:
                    if (
                        mb_strpos($deliveryZone, DeliveryService::ADD_DELIVERY_ZONE_CODE_PATTERN) !== false ||
                        mb_strpos($deliveryZone, DeliveryService::ZONE_MOSCOW_DISTRICT_CODE_PATTERN) !== false
                    ) {
                        $xmlId = SapOrder::DELIVERY_ZONE_2_ARTICLE;
                    } else {
                        $xmlId = SapOrder::DELIVERY_ZONE_4_ARTICLE;
                    }
            }

            $deliveryPlaceCode = $this->getPropertyValueByCode($order, 'DELIVERY_PLACE_CODE');
            $deliveryShipmentPoint = $deliveryPlaceCode;
            if (\in_array($this->getDeliveryTypeCode($order), [
                SapOrder::DELIVERY_TYPE_PICKUP_POSTPONE,
                SapOrder::DELIVERY_TYPE_COURIER_SHOP,
                SapOrder::DELIVERY_TYPE_DOSTAVISTA
            ], true)
            ) {
                $deliveryShipmentPoint = '';
            }

            $offer = (new OrderOffer())
                ->setPosition($collection->count() + 1)
                ->setOfferXmlId($xmlId)
                ->setUnitPrice($deliveryPrice)
                ->setQuantity(1)
                ->setUnitOfMeasureCode(SapOrder::UNIT_PTC_CODE)
                ->setChargeBonus(false)
                ->setDeliveryFromPoint($deliveryPlaceCode)
                ->setDeliveryShipmentPoint($deliveryShipmentPoint);
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


    /**
     * @param string $regularityName
     * @return bool
     * @throws ArgumentException
     * @throws LoaderException
     * @throws SystemException
     * @throws \Bitrix\Main\ObjectPropertyException
     */
    public function isFastDelivery(string $regularityName)
    {
        /** @var ScheduleResultService $scheduleResultService */
        $scheduleResultService = Application::getInstance()->getContainer()->get(ScheduleResultService::class);
        /** @var UserFieldEnumValue $regularityFastDeliv */
        $regularityFastDeliv = $scheduleResultService->getRegularityEnumByXmlId(ScheduleResultService::FAST_DELIV);
        return $regularityName == $regularityFastDeliv->getValue();
    }
}
