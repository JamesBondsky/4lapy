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
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\Helpers\BxCollection;
use FourPaws\Helpers\DateHelper;
use FourPaws\SaleBundle\Service\OrderService as BaseOrderService;
use FourPaws\SapBundle\Dto\In\Orders\Order as OrderDtoIn;
use FourPaws\SapBundle\Dto\Out\Orders\DeliveryAddress;
use FourPaws\SapBundle\Dto\Out\Orders\Order as OrderDtoOut;
use FourPaws\SapBundle\Dto\Out\Orders\OrderOffer;
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

    const ORDER_CONTRACTOR_CODE = '07';
    const ORDER_PAYMENT_ONLINE_MERCHANT_ID = '850000314610';
    const ORDER_PAYMENT_ONLINE_CODE = '05';
    const ORDER_PAYMENT_STATUS_PAYED = '01';
    const ORDER_PAYMENT_STATUS_NOT_PAYED = '02';
    const ORDER_PAYMENT_STATUS_PRE_PAYED = '03';

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
     * OrderService constructor.
     *
     * @param BaseOrderService $baseOrderService
     * @param DeliveryService $deliveryService
     * @param ArrayTransformerInterface $arrayTransformer
     * @param SerializerInterface $serializer
     * @param Filesystem $filesystem
     * @param UserRepository $userRepository
     */
    public function __construct(
        BaseOrderService $baseOrderService,
        DeliveryService $deliveryService,
        ArrayTransformerInterface $arrayTransformer,
        SerializerInterface $serializer,
        Filesystem $filesystem,
        UserRepository $userRepository
    )
    {
        $this->baseOrderService = $baseOrderService;
        $this->arrayTransformer = $arrayTransformer;
        $this->serializer = $serializer;
        $this->filesystem = $filesystem;
        $this->userRepository = $userRepository;
        $this->deliveryService = $deliveryService;
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
        $orderSource = BxCollection::getOrderPropertyByCode($order->getPropertyCollection(), 'FROM_APP')->getValue() === 'Y'
            ? OrderDtoOut::ORDER_SOURCE_MOBILE_APP
            : OrderDtoOut::ORDER_SOURCE_SITE;

        $orderDto
            ->setId($order->getId())
            ->setDateInsert(DateHelper::convertToDateTime($order->getDateInsert()->toUserTime()))
            ->setClientId($order->getId())
            ->setClientFio(BxCollection::getOrderPropertyByCode($order->getPropertyCollection(), 'NAME')->getValue())
            ->setClientPhone(BxCollection::getOrderPropertyByCode($order->getPropertyCollection(), 'PHONE')->getValue())
            ->setClientOrderPhone(BxCollection::getOrderPropertyByCode($order->getPropertyCollection(), 'PHONE_ALT')->getValue())
            ->setClientComment($order->getField('USER_DESCRIPTION'))
            ->setOrderSource($orderSource)
            ->setBonusCard($orderUser->getDiscountCardNumber())
            /**
             * Товары в заказе
             */
            ->setProducts([]);

        $this->populateOrderDtoPayment($orderDto, $order->getPaymentCollection());
        $this->populateOrderDtoDelivery($orderDto, $order);
        $this->populateOrderDtoProducts($orderDto, $order);

        dump($orderDto);
        die;
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
        return sprintf('%s/%s-%s.xml', trim($this->outPath, '/'), $order->getDateInsert()->format('Ymd'), $order->getId());
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
                ->setPayType(self::ORDER_PAYMENT_ONLINE_CODE)
                ->setPayStatus(self::ORDER_PAYMENT_STATUS_PRE_PAYED)
                ->setPayHoldTransaction($externalPayment->getField('PS_INVOICE_ID'))
                ->setPayHoldDate(DateHelper::convertToDateTime($externalPayment->getField('PS_RESPONSE_DATE')))
                ->setPrePayedSum($externalPayment->getSum())
                ->setPayMerchantCode(self::ORDER_PAYMENT_ONLINE_MERCHANT_ID);
        } else {
            $dto->setPayType('')
                ->setPayStatus(self::ORDER_PAYMENT_STATUS_NOT_PAYED);
        }
    }

    private function populateOrderDtoDelivery(OrderDtoOut $orderDto, Order $order): void
    {
        $shipment = BxCollection::getOrderExternalShipment($order->getShipmentCollection());

        if (null === $shipment) {
            throw new NotFoundOrderShipmentException('Не найдена отгрузка у заказа');
        }


        $orderDto
            /**
             * @todo - после Димы
             */
            ->setCommunicationType('')
            /**
             * @todo deliveries start
             *
             * Способ получения заказа
             *
             * 01 – Курьерская доставка из РЦ;
             * 02 – Самовывоз из магазина;
             * 03 – Самовывоз из магазина (значение не передается Сайтом);
             * 04 – Отложить в магазине (значение не передается Сайтом);
             * 06 – Курьерская доставка из магазина;
             * 07 – Доставка внешним подрядчиком (курьер или самовывоз из пункта выдачи заказов);
             * 08 – РЦ – магазин – домой.
             */
            ->setDeliveryType($deliveryTypeCode)
            /**
             * Тип доставки подрядчиком
             * Поле должно быть заполнено, если выбран способ получения заказа 07.
             * ТД – от терминала до двери покупателя;
             * ТТ – от терминала до пункта выдачи заказов.
             */
            ->setContractorDeliveryType($this->setContractorDeliveryTypeCodeByOrder($order->getShipmentCollection()))
            ->setDeliveryDate(new \DateTime())
            /**
             * Интервал доставки
             * 1    (09:00 – 18:00);
             * 2    (18:00 – 24:00);
             * 3    (08:00 – 12:00);
             * 4    (12:00 – 16:00);
             * 5    (16:00 – 20:00);
             * 6    (20:00 – 24:00).
             */
            ->setDeliveryTimeInterval('')
            /**
             * Адрес доставки для:
             * 01 – Курьерская доставка из РЦ;
             * 06 – Курьерская доставка из магазина;
             * 07 – Курьерская доставка внешним подрядчиком;
             * 08 – РЦ – магазин - домой
             */
            ->setDeliveryAddress(new DeliveryAddress())
            ->setDeliveryAddressOrPoint('');

        if ($deliveryTypeCode !== self::ORDER_CONTRACTOR_CODE) {
            /**
             * Код подрядчика
             * Содержит код подрядчика в SAP
             * Поле должно быть заполнено, если выбран способ получения заказа 07.
             * Значение по умолчанию 0000802070
             **/
            $orderDto->setContractorCode('');
        }
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
                ->setChargeBonus(true)
                ->setDeliveryFromPoint('')
                ->setDeliveryShipmentPoint('');
            
            $collection->add($offer);
            $position++;
        }
        
        $orderDto->setProducts($collection);
    }
}
