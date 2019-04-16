<?php

namespace FourPaws\SaleBundle\Service;

use Adv\Bitrixtools\Tools\BitrixUtils;
use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\IO\InvalidPathException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Internals\PaySystemActionTable;
use Bitrix\Sale\Order;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PropertyValue;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\App\Application;
use FourPaws\DeliveryBundle\Entity\Terminal;
use FourPaws\Helpers\BusinessValueHelper;
use FourPaws\Helpers\DateHelper;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\LocationBundle\Exception\AddressSplitException;
use FourPaws\PersonalBundle\Service\PiggyBankService;
use FourPaws\SaleBundle\Discount\Utils\Manager;
use FourPaws\SaleBundle\Dto\Fiscalization\CartItems;
use FourPaws\SaleBundle\Dto\Fiscalization\CustomerDetails;
use FourPaws\SaleBundle\Dto\Fiscalization\Fiscal;
use FourPaws\SaleBundle\Dto\Fiscalization\Fiscalization;
use FourPaws\SaleBundle\Dto\Fiscalization\Item;
use FourPaws\SaleBundle\Dto\Fiscalization\ItemQuantity;
use FourPaws\SaleBundle\Dto\Fiscalization\ItemTax;
use FourPaws\SaleBundle\Dto\Fiscalization\OrderBundle;
use FourPaws\SaleBundle\Dto\SberbankOrderInfo\Attribute;
use FourPaws\SaleBundle\Dto\SberbankOrderInfo\OrderBundle\Item as SberbankOrderItem;
use FourPaws\SaleBundle\Dto\SberbankOrderInfo\OrderInfo;
use FourPaws\SaleBundle\Enum\OrderPayment;
use FourPaws\SaleBundle\Exception\FiscalValidation\FiscalAmountExceededException;
use FourPaws\SaleBundle\Exception\FiscalValidation\FiscalAmountException;
use FourPaws\SaleBundle\Exception\FiscalValidation\InvalidItemCodeException;
use FourPaws\SaleBundle\Exception\FiscalValidation\NoMatchingFiscalItemException;
use FourPaws\SaleBundle\Exception\FiscalValidation\PositionQuantityExceededException;
use FourPaws\SaleBundle\Exception\FiscalValidation\PositionWrongAmountException;
use FourPaws\SaleBundle\Exception\NotFoundException;
use FourPaws\SaleBundle\Exception\OrderUpdateException;
use FourPaws\SaleBundle\Exception\PaymentException;
use FourPaws\SaleBundle\Exception\PaymentReverseException;
use FourPaws\SaleBundle\Exception\SberbankOrderNotFoundException;
use FourPaws\SaleBundle\Exception\SberbankOrderNotPaidException;
use FourPaws\SaleBundle\Exception\SberBankOrderNumberNotFoundException;
use FourPaws\SaleBundle\Exception\SberbankOrderPaymentDeclinedException;
use FourPaws\SaleBundle\Exception\SberbankPaymentException;
use FourPaws\SaleBundle\Payment\Sberbank;
use FourPaws\SapBundle\Consumer\ConsumerRegistry;
use FourPaws\StoreBundle\Entity\Store;
use JMS\Serializer\ArrayTransformerInterface;
use Psr\Log\LoggerAwareInterface;
use Bitrix\Sale\Delivery\Services\Table as ServicesTable;
use FourPaws\DeliveryBundle\Service\DeliveryService;
/**
 * Class PaymentService
 *
 * @package FourPaws\SaleBundle\Service
 */
class PaymentService implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /**
     * @var ArrayTransformerInterface
     */
    protected $arrayTransformer;

    /**
     * @var BasketService
     */
    protected $basketService;

    /**
     * @var DeliveryService
     */
    protected $deliveryService;

    protected const SBERBANK_PAYMENT_URL_FORMAT = '%s://%s/payment/merchants/%s/payment_ru.html?mdOrder=%s';

    /**
     * @var Sberbank
     */
    private $sberbankProcessing;

    /**
     * PaymentService constructor.
     * @param BasketService             $basketService
     * @param ArrayTransformerInterface $arrayTransformer
     */
    public function __construct(BasketService $basketService, ArrayTransformerInterface $arrayTransformer, DeliveryService $deliveryService)
    {
        $this->arrayTransformer = $arrayTransformer;
        $this->basketService = $basketService;
        $this->deliveryService = $deliveryService;
    }

    /**
     * @param Store $store
     * @param float $paymentSum
     * @return array
     */
    public function getAvailablePaymentsForStore(Store $store, float $paymentSum = 0): array
    {
        $result = [OrderPayment::PAYMENT_ONLINE];
        if ($store instanceof Terminal) {
            if ($store->isNppAvailable() && $store->getNppValue() >= $paymentSum) {
                if ($store->hasCardPayment()) {
                    $result[] = OrderPayment::PAYMENT_CASH_OR_CARD;
                } elseif ($store->hasCashPayment()) {
                    $result[] = OrderPayment::PAYMENT_CASH;
                }
            }
        } else {
            $result[] = OrderPayment::PAYMENT_CASH_OR_CARD;
        }

        return $result;
    }

    /**
     * @param Order $order
     * @param int   $taxSystem
     * @param bool  $skipGifts
     *
     * @return Fiscalization
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws InvalidPathException
     * @throws ObjectNotFoundException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getFiscalization(Order $order, int $taxSystem = 0, $skipGifts = true): Fiscalization
    {
        /** @var DateTime $dateCreate */
        $dateCreate = $order->getField('DATE_INSERT');

        $orderBundle = new OrderBundle();
        $fiscal = (new Fiscal())
            ->setOrderBundle($orderBundle)
            ->setTaxSystem($taxSystem);

        $orderBundle
            ->setCustomerDetails($this->getCustomerDetails($order))
            ->setDateCreate(DateHelper::convertToDateTime($dateCreate))
            ->setCartItems($this->getCartItems($order, $skipGifts));

        return (new Fiscalization())->setFiscal($fiscal);
    }

    /**
     * @param Fiscalization $fiscalization
     * @param OrderInfo     $orderInfo
     * @param int|null      $sumPaid
     *
     * @throws FiscalAmountExceededException
     * @throws FiscalAmountException
     * @throws InvalidItemCodeException
     * @throws NoMatchingFiscalItemException
     * @throws PositionQuantityExceededException
     * @throws PositionWrongAmountException
     */
    public function validateFiscalization(
        Fiscalization $fiscalization,
        OrderInfo $orderInfo,
        int $sumPaid = null
    ): void
    {
        $fiscalItems = $fiscalization->getFiscal()->getOrderBundle()->getCartItems()->getItems();
        $fiscalAmount = $this->getFiscalTotal($fiscalization);

        $sberbankOrderItems = $orderInfo->getOrderBundle()->getCartItems()->getItems();
        /** @var Item $fiscalItem */
        foreach ($fiscalItems as $fiscalItem) {
            /** @var SberbankOrderItem $matchingItem */
            $matchingItem = null;
            /** @var SberbankOrderItem $orderItem */
            foreach ($sberbankOrderItems as $orderItem) {
                if ((int)$orderItem->getPositionId() === $fiscalItem->getPositionId()) {
                    $matchingItem = $orderItem;
                    break;
                }
            }

            if (null === $matchingItem) {
                throw new NoMatchingFiscalItemException(
                    \sprintf(
                        'No matching item found for position %s',
                        $fiscalItem->getPositionId()
                    )
                );
            }

            if ($matchingItem->getItemCode() !== $fiscalItem->getCode()) {
                throw new InvalidItemCodeException(
                    \sprintf(
                        'Item code %s for position %s doesn\'t, match existing item code',
                        $fiscalItem->getCode(),
                        $fiscalItem->getPositionId()
                    )
                );
            }

            if ($fiscalItem->getTotal() !== $fiscalItem->getQuantity()->getValue() * $fiscalItem->getPrice()) {
                throw new PositionWrongAmountException(
                    \sprintf(
                        'Item %s total (%s) for position %s is not equal to (price * amount) (%s)',
                        $fiscalItem->getCode(),
                        $fiscalItem->getTotal(),
                        $fiscalItem->getPositionId(),
                        $fiscalItem->getQuantity()->getValue() * $fiscalItem->getPrice()
                    )
                );
            }
        }

        $approvedAmount = $orderInfo->getPaymentAmountInfo()->getApprovedAmount();
        if ($fiscalAmount > $approvedAmount) {
            throw new FiscalAmountExceededException(
                \sprintf(
                    'Fiscal amount (%s) exceeds approved amount (%s)',
                    $fiscalAmount,
                    $approvedAmount
                )
            );
        }

        if (null !== $sumPaid && $fiscalAmount < $sumPaid) {
            if (($sumPaid - $fiscalAmount) > ($sumPaid * 0.01)) {
                throw new FiscalAmountException(
                    \sprintf(
                        'Fiscal amount (%s) is lesser than paid amount (%s)',
                        $fiscalAmount,
                        $sumPaid
                    )
                );
            }
        }
    }

    /**
     * @param Order $order
     *
     * @return string
     *
     * @throws ObjectNotFoundException
     */
    public function getOrderInvoiceId(Order $order): string
    {
        $result = null;
        /** @var Payment $payment */
        foreach ($order->getPaymentCollection() as $payment) {
            if ($payment->isInner()) {
                continue;
            }

            $result = $payment->getField('PS_INVOICE_ID');
            break;
        }

        return $result ?: '';
    }

    /**
     * @param Order $order
     *
     * @return bool
     * @throws ObjectNotFoundException
     */
    public function isOnlinePayment(Order $order): bool
    {
        $result = false;
        try {
            $result = $this->getOrderPaymentType($order) === OrderPayment::PAYMENT_ONLINE;
        } catch (NotFoundException $e) {
        }

        return $result;
    }

    /**
     * @param Order $order
     *
     * @throws ObjectNotFoundException
     * @throws NotFoundException
     * @return Payment
     */
    public function getOrderPayment(Order $order): Payment
    {
        $payment = null;
        /** @var Payment $orderPayment */
        foreach ($order->getPaymentCollection() as $orderPayment) {
            if ($orderPayment->isInner()) {
                continue;
            }

            $payment = $orderPayment;
        }

        if (null === $payment) {
            throw new NotFoundException('payment system is not defined');
        }

        return $payment;
    }

    /**
     * @param Order $order
     *
     * @throws ObjectNotFoundException
     * @return string
     */
    public function getOrderPaymentType(Order $order): string
    {
        return $this->getOrderPayment($order)->getPaySystem()->getField('CODE');
    }

    /**
     * @param Order $order
     * @param float $amount
     * @param array $fiscalization
     *
     * @return bool
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws InvalidPathException
     * @throws ObjectNotFoundException
     * @throws ObjectPropertyException
     * @throws PaymentException
     * @throws SystemException
     */
    public function depositPayment(Order $order, float $amount, array $fiscalization = null): bool
    {
        $orderInvoiceId = $this->getOrderInvoiceId($order);
        if (null === $fiscalization) {
            $fiscalization = $this->fiscalToArray($this->getFiscalization($order));
        }
        return $this->response(function () use ($orderInvoiceId, $amount, $fiscalization) {
            return $this->getSberbankProcessing()->depositPayment($orderInvoiceId, $amount, $fiscalization);
        });
    }

    /**
     * @param Order $order
     * @param float $amount
     * @param bool  $save
     *
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws NotImplementedException
     * @throws ObjectException
     * @throws ObjectNotFoundException
     * @throws PaymentException
     * @throws SystemException
     * @throws \Exception
     */
    public function cancelPayment(Order $order, float $amount = 0, $save = true): void
    {
        if ($this->isOnlinePayment($order)) {
            $this->reverseOnlinePayment($order, $amount);
        }

        /** @var Payment $payment */
        foreach ($order->getPaymentCollection() as $payment) {
            $payment->setPaid(BitrixUtils::BX_BOOL_FALSE);
            if ($save) {
                $payment->save();
            }
        }

        if ($save) {
            $order->save();
        }
    }/** @noinspection PhpUnusedParameterInspection */

    /**
     * @param Order $order
     * @param float $amount
     *
     * @throws ArgumentException
     * @throws NotFoundException
     * @throws ObjectNotFoundException
     * @throws PaymentException
     * @throws PaymentReverseException
     */
    protected function reverseOnlinePayment(Order $order, float $amount = 0): void
    {
        if (!$this->getOrderPayment($order)->isPaid()) {
            return;
        }

        $orderInfo = $this->getSberbankProcessing()->getOrderStatusByOrderId($this->getOrderInvoiceId($order));
        $orderStatus = $orderInfo['orderStatus'];
        if ($orderStatus === Sberbank::ORDER_STATUS_HOLD) {
            $this->tryPaymentReverse($order);
        } elseif ($orderStatus === Sberbank::ORDER_STATUS_PAID) {
            $this->tryPaymentRefund($order, $orderInfo['amount']);
        } else {
            throw new PaymentReverseException(sprintf('Invalid order status: %s', $orderStatus));
        }
    }

    /**
     * @param Order $order
     *
     * @throws ObjectNotFoundException
     * @throws PaymentException
     * @return bool
     */
    protected function tryPaymentReverse(Order $order): bool
    {
        $orderInvoiceId = $this->getOrderInvoiceId($order);
        return $this->response(function () use ($orderInvoiceId) {
            return $this->getSberbankProcessing()->reversePayment($orderInvoiceId);
        });
    }

    /**
     * @param Order $order
     * @param int   $amount
     *
     * @throws ObjectNotFoundException
     * @throws PaymentException
     * @return bool
     */
    protected function tryPaymentRefund(Order $order, int $amount): bool
    {
        $orderInvoiceId = $this->getOrderInvoiceId($order);
        return $this->response(function () use ($orderInvoiceId, $amount) {
            return $this->getSberbankProcessing()->refundPayment($orderInvoiceId, $amount);
        });
    }

    /**
     * @todo CopyPaste from Sberbank pay system.
     * Do refactor.
     *
     * @param callable $responseCallback
     *
     * @return bool
     *
     * @throws PaymentException
     */
    private function response(callable $responseCallback): bool
    {
        $response = ['Fake response'];

        for ($i = 0; $i <= 10; $i++) {
            $response = $responseCallback();

            if ((int)$response['errorCode'] !== 1) {
                break;
            }
        }

        return $this->getSberbankProcessing()->parseResponse($response);
    }

    /**
     * @return Sberbank
     */
    private function getSberbankProcessing(): Sberbank
    {
        if (null === $this->sberbankProcessing) {
            /** @noinspection PhpIncludeInspection */
            require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/sberbank.ecom/config.php';
            $settings = BusinessValueHelper::getPaysystemSettings(3, [
                'USER_NAME',
                'PASSWORD',
                'TEST_MODE',
                'TWO_STAGE',
                'LOGGING',
            ]);

            $this->sberbankProcessing = new Sberbank(
                $settings['USER_NAME'],
                $settings['PASSWORD'],
                $settings['TWO_STAGE'] === 'Y',
                $settings['TEST_MODE'] === 'Y',
                $settings['LOGGING'] === 'Y'
            );
        }

        return $this->sberbankProcessing;
    }

    /**
     * @param Order $order
     * @return CustomerDetails
     */
    private function getCustomerDetails(Order $order): CustomerDetails
    {
        $result = new CustomerDetails();

        /** @var \Bitrix\Sale\PropertyValue $propertyValue */
        foreach ($order->getPropertyCollection() as $propertyValue) {
            $property = $propertyValue->getProperty();
            if ($property['IS_PAYER'] === BitrixUtils::BX_BOOL_TRUE) {
                $result->setContact($propertyValue->getValue());
            } elseif ($property['IS_EMAIL'] === BitrixUtils::BX_BOOL_TRUE && $propertyValue->getValue()) {
                /**
                 * у сбера email валидируется строже, проще использовать телефон
                 */
//                $result->setEmail($propertyValue->getValue());
            } elseif ($property['IS_PHONE'] === BitrixUtils::BX_BOOL_TRUE) {
                $result->setPhone(PhoneHelper::formatPhone($propertyValue->getValue(), '7' . PhoneHelper::FORMAT_SHORT));
            }
        }

        return $result;
    }

    /**
     * @param Order $order
     * @param bool  $skipGifts
     *
     * @return CartItems
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws ObjectNotFoundException
     * @throws SystemException
     * @throws InvalidPathException
     * @throws ObjectPropertyException
     */
    private function getCartItems(Order $order, bool $skipGifts = true): CartItems
    {
        $items = new ArrayCollection();

        $measureList = [];
        $defaultMeasure = 'Штука';
        $dbMeasure = \CCatalogMeasure::getList();
        while ($arMeasure = $dbMeasure->GetNext()) {
            $measureList[$arMeasure['ID']] = $arMeasure['MEASURE_TITLE'];
            if ($arMeasure['IS_DEFAULT'] === BitrixUtils::BX_BOOL_TRUE) {
                $defaultMeasure = $arMeasure['MEASURE_TITLE'];
            }
        }

        $vatList = [];
        $dbRes = \CCatalogVat::GetListEx();
        while ($arRes = $dbRes->Fetch()) {
            $vatList[$arRes['ID']] = (int)$arRes['RATE'];
        }

        $vatGateway = [
            -1 => 0,
            0  => 1,
            10 => 2,
            18 => 3,
            20 => 3
        ];

        $position = 0;
        /** @var BasketItem $basketItem */
        foreach ($order->getBasket() as $basketItem) {
            if ($basketItem->isDelay()) {
                continue;
            }

            if ($skipGifts && $this->basketService->isGiftProduct($basketItem)) {
                continue;
            }

            $arProduct = \CCatalogProduct::GetByID($basketItem->getProductId());


            if ($arProduct === false || in_array($arProduct['ID'], PiggyBankService::getMarkProductIds())) {
                continue;
            }
            
            $taxType = $arProduct['VAT_ID'] > 0 ? (int)$vatList[$arProduct['VAT_ID']] : -1;

            $quantity = (new ItemQuantity())
                ->setValue($basketItem->getQuantity())
                ->setMeasure($measureList[$arProduct['MEASURE']]);

            $tax = (new ItemTax())->setType($vatGateway[$taxType]);

            $itemPrice = round($basketItem->getPrice() * 100);
            $item = (new Item())
                ->setPositionId(++$position)
                ->setName($basketItem->getField('NAME') ?: '')
                ->setXmlId($this->basketService->getBasketItemXmlId($basketItem))
                ->setQuantity($quantity)
                ->setPrice($itemPrice)
                ->setTotal($itemPrice * (int)$basketItem->getQuantity())
                ->setCode($basketItem->getProductId() . '_' . $position)
                ->setTax($tax);
            $items->add($item);
        }

        $total = \array_reduce($items->toArray(), function ($total, Item $item) {
            return $total + $item->getTotal();
        }, 0);

        if ($innerPayment = $order->getPaymentCollection()->getInnerPayment()) {
            $correction = 0;
            $bonusSum = $innerPayment->getSum() * 100;
            $diff = $total - $bonusSum;

            $items->map(function (Item $item) use (&$correction, $diff, $total) {
                if (!$item->getPrice()) {
                    return;
                }
                $item->setPrice(floor($item->getPrice() * ($diff / $total)));
                $itemOldTotal = $item->getTotal();
                $item->setTotal($item->getPrice() * $item->getQuantity()->getValue());
                $correction += $itemOldTotal - $item->getTotal();
            });

            /**
             * распределяем погрешность по товарам
             */
            $correction = $bonusSum - $correction;
            $items->map(function (Item $item) use (&$correction) {
                if ((int)$correction === 0) {
                    return;
                }
                if (!$item->getPrice()) {
                    return;
                }
                $itemOldTotal = $item->getTotal();

                $item->setPrice(
                    floor($item->getTotal() * ($item->getTotal() - $correction) / $item->getTotal() / $item->getQuantity()->getValue())
                );
                $item->setTotal($item->getPrice() * $item->getQuantity()->getValue());

                $correction -= $itemOldTotal - $item->getTotal();
            });
        }

        if ($order->getDeliveryPrice() > 0) {
            $deliveryPrice = floor($order->getDeliveryPrice() * 100);
            $delivery = (new Item())
                ->setPositionId(++$position)
                ->setName(Loc::getMessage('RBS_PAYMENT_DELIVERY_TITLE') ?: 'Доставка')
                ->setQuantity((new ItemQuantity())
                    ->setValue(1)
                    ->setMeasure(Loc::getMessage('RBS_PAYMENT_MEASURE_DEFAULT') ?: $defaultMeasure)
                )
                ->setXmlId(OrderPayment::GENERIC_DELIVERY_CODE)
                ->setTotal($deliveryPrice)
                ->setCode($order->getId() . '_DELIVERY')
                ->setPrice($deliveryPrice)
                ->setTax((new ItemTax())
                    ->setType(0)
                );

            $items->add($delivery);
        }

        return (new CartItems())->setItems($items);
    }

    /**
     * @param Fiscalization $fiscal
     * @return int
     */
    public function getFiscalTotal(Fiscalization $fiscal): int
    {
        return \array_reduce(
            $fiscal->getFiscal()->getOrderBundle()->getCartItems()->getItems()->toArray(),
            function ($total, Item $item) {
                return $total + $item->getTotal();
            },
            0
        );
    }

    /**
     * @param Fiscalization $fiscal
     * @return array
     */
    public function fiscalToArray(Fiscalization $fiscal): array
    {
        return $this->arrayTransformer->toArray($fiscal);
    }

    /**
     * @param string $number
     *
     * @return OrderInfo
     * @throws SberbankOrderNotFoundException
     * @throws ArgumentException
     */
    public function getSberbankOrderStatusByOrderNumber(string $number): OrderInfo
    {
        $response = $this->getSberbankProcessing()->getOrderStatusByOrderNumber($number);

        /** @var OrderInfo $result */
        $result = $this->arrayTransformer->fromArray($response, OrderInfo::class);

        if ($result->getErrorCode() === Sberbank::ERROR_ORDER_NOT_FOUND) {
            throw new SberbankOrderNotFoundException($result->getErrorMessage(), $result->getErrorCode());
        }

        return $result;
    }

    /**
     * @param string $invoiceId
     *
     * @return OrderInfo
     * @throws ArgumentException
     * @throws SberbankOrderNotFoundException
     */
    public function getSberbankOrderStatusByOrderId(string $invoiceId): OrderInfo
    {
        $response = $this->getSberbankProcessing()->getOrderStatusByOrderId($invoiceId);

        /** @var OrderInfo $result */
        $result = $this->arrayTransformer->fromArray($response, OrderInfo::class);

        if ($result->getErrorCode() === Sberbank::ERROR_ORDER_NOT_FOUND) {
            throw new SberbankOrderNotFoundException($result->getErrorMessage(), $result->getErrorCode());
        }

        return $result;
    }

    /**
     * @param OrderInfo $orderInfo
     *
     * @return string
     * @throws SberBankOrderNumberNotFoundException
     */
    public function getSberbankPaymentUrl(OrderInfo $orderInfo): string
    {
        $orderNumber = $this->getSberbankOrderId($orderInfo);
        $urlParts = \parse_url($this->getSberbankProcessing()->getApiUrl());
        $url = \sprintf(
            self::SBERBANK_PAYMENT_URL_FORMAT,
            $urlParts['scheme'],
            $urlParts['host'],
            $this->getSberbankProcessing()->getMerchantName(),
            $orderNumber
        );

        return $url;
    }

    /**
     * @param Order $order
     * @param       $sberbankOrderId
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws NotImplementedException
     * @throws ObjectException
     * @throws ObjectNotFoundException
     * @throws PaymentException
     * @throws SberbankOrderNotPaidException
     * @throws SberbankOrderPaymentDeclinedException
     * @throws SberbankOrderNotFoundException
     * @throws SberbankPaymentException
     * @throws SystemException
     * @throws \Exception
     */
    public function processOnlinePaymentByOrderId(Order $order, $sberbankOrderId): void
    {
        if (!$this->isOnlinePayment($order)) {
            throw new PaymentException('Invalid order payment type');
        }
        $response = $this->getSberbankOrderStatusByOrderId($sberbankOrderId);
        $this->processOnlinePayment($order, $response);
    }

    /**
     * @param Order $order
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws NotImplementedException
     * @throws ObjectException
     * @throws ObjectNotFoundException
     * @throws PaymentException
     * @throws SberbankOrderNotPaidException
     * @throws SberbankOrderPaymentDeclinedException
     * @throws SberbankOrderNotFoundException
     * @throws SberbankPaymentException
     * @throws SystemException
     * @throws \Exception
     */
    public function processOnlinePaymentByOrderNumber(Order $order): void
    {
        if (!$this->isOnlinePayment($order)) {
            throw new PaymentException('Invalid order payment type');
        }

        $response = $this->getSberbankOrderStatusByOrderNumber($order->getField('ACCOUNT_NUMBER'));
        $this->processOnlinePayment($order, $response);
    }

    /**
     * @param Order $order
     *
     * @throws AddressSplitException
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws NotImplementedException
     * @throws ObjectException
     * @throws ObjectNotFoundException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws \FourPaws\DeliveryBundle\Exception\NotFoundException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function processOnlinePaymentError(Order $order): void
    {
        /** @todo костыль */
        if (!$payment = PaySystemActionTable::getList(['filter' => ['CODE' => OrderPayment::PAYMENT_CASH]])->fetch()) {
            $this->log()->error('cash payment not found');
            return;
        }

        if ($discountEnabled = Manager::isExtendDiscountEnabled()) {
            Manager::disableExtendsDiscount();
        }

        $paySystemId = $payment['ID'];
        $sapConsumer = Application::getInstance()->getContainer()->get(ConsumerRegistry::class);
        $orderService = Application::getInstance()->getContainer()->get(OrderService::class);
        $deliveryId = $order->getField('DELIVERY_ID');
        $deliveryCode = $this->deliveryService->getDeliveryCodeById($deliveryId);
        $updateOrder = function (Order $order) use ($paySystemId, $sapConsumer, $orderService, $deliveryCode) {
            try {
                $payment = $this->getOrderPayment($order);
                if ($payment->isPaid() ||
                    $payment->getPaySystem()->getField('CODE') !== OrderPayment::PAYMENT_ONLINE
                ) {
                    return;
                }
                $newPayment = $order->getPaymentCollection()->createItem();
                $newPayment->setField('SUM', $payment->getSum());
                $newPayment->setField('PAY_SYSTEM_ID', $paySystemId);
                $paySystem = $newPayment->getPaySystem();
                $newPayment->setField('PAY_SYSTEM_NAME', $paySystem->getField('NAME'));
                $payment->delete();
                $newPayment->save();
                $commWay = $orderService->getOrderPropertyByCode($order, 'COM_WAY');
                $commWay->setValue(OrderPropertyService::COMMUNICATION_PAYMENT_ANALYSIS);
                $result = $order->save();
                if (!$result->isSuccess()) {
                    throw new OrderUpdateException(\implode(', ', $result->getErrorMessages()));
                }
                if (!$this->deliveryService->isDostavistaDeliveryCode($deliveryCode)) {
                    $sapConsumer->consume($order);
                }
            } catch (\Exception $e) {
                $this->log()->error(sprintf('failed to process payment error: %s', $e->getMessage()), [
                    'order' => $order->getId(),
                ]);
            }
        };
        $updateOrder($order);
        if ($orderService->hasRelatedOrder($order)) {
            $relatedOrder = $orderService->getRelatedOrder($order);
            if (!$relatedOrder->isPaid()) {
                $updateOrder($relatedOrder);
            }
        }

        /** Отправка данных в достависту если доставка Достависта */
        $deliveryData = ServicesTable::getById($deliveryId)->fetch();
        //проверяем способ доставки, если достависта, то отправляем заказ в достависту
        if ($this->deliveryService->isDostavistaDeliveryCode($deliveryCode)) {
            $this->sendOnlinePaymentDostavistaOrder($order, $deliveryCode, $deliveryData, false);
        }

        if ($discountEnabled) {
            Manager::enableExtendsDiscount();
        }
    }

    /**
     * @param Order $order
     * @param OrderInfo $response
     *
     * @throws AddressSplitException
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws NotImplementedException
     * @throws ObjectException
     * @throws ObjectNotFoundException
     * @throws ObjectPropertyException
     * @throws SberBankOrderNumberNotFoundException
     * @throws SberbankOrderNotPaidException
     * @throws SberbankOrderPaymentDeclinedException
     * @throws SberbankPaymentException
     * @throws SystemException
     * @throws \FourPaws\DeliveryBundle\Exception\NotFoundException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function processOnlinePayment(Order $order, OrderInfo $response): void
    {
        $isSuccess = $response->getErrorCode() === Sberbank::SUCCESS_CODE;
        if ($isSuccess && \in_array(
                $response->getOrderStatus(),
                [
                    Sberbank::ORDER_STATUS_HOLD,
                    Sberbank::ORDER_STATUS_PAID,
                ],
                true
            )
        ) {
            $onlinePayment = $this->getOrderPayment($order);

            $sberbankOrderId = $this->getSberbankOrderId($response);

            $onlinePayment->setPaid('Y');
            $onlinePayment->setField('PS_SUM', $response->getAmount() / 100);
            $onlinePayment->setField('PS_CURRENCY', $response->getCurrency());
            $onlinePayment->setField('PS_RESPONSE_DATE', new \Bitrix\Main\Type\DateTime());
            $onlinePayment->setField('PS_INVOICE_ID', $sberbankOrderId);
            $onlinePayment->setField('PS_STATUS', 'Y');
            $onlinePayment->setField(
                'PS_STATUS_DESCRIPTION',
                $response->getCardAuthInfo()->getPan() . ';' . $response->getCardAuthInfo()->getCardHolderName()
            );
            $onlinePayment->setField('PS_STATUS_CODE', 'Y');
            $onlinePayment->setField('PS_STATUS_MESSAGE', $response->getPaymentAmountInfo()->getPaymentState());
            $onlinePayment->save();
            $order->save();

            /** Отправка данных в достависту если доставка Достависта */
            $deliveryId = $order->getField('DELIVERY_ID');
            $deliveryCode = $this->deliveryService->getDeliveryCodeById($deliveryId);
            $deliveryData = ServicesTable::getById($deliveryId)->fetch();
            //проверяем способ доставки, если достависта, то отправляем заказ в достависту
            if ($this->deliveryService->isDostavistaDeliveryCode($deliveryCode)) {
                $this->sendOnlinePaymentDostavistaOrder($order, $deliveryCode, $deliveryData, true);
            }
        } else {
            if ($response->getOrderStatus() === Sberbank::ORDER_STATUS_DECLINED) {
                throw new SberbankOrderPaymentDeclinedException('Order not paid');
            }

            if ($response->getOrderStatus() === Sberbank::ORDER_STATUS_CREATED) {
                throw new SberbankOrderNotPaidException('Order still can be paid');
            }

            throw new SberbankPaymentException(
                $isSuccess ? ($response->getActionCodeDescription() ? $response->getActionCodeDescription() : $response->getActionDescription()) : $response->getErrorMessage(),
                $response->getErrorCode()
            );
        }
    }

    /**
     * @param Order $order
     * @param string $deliveryCode
     * @param array $deliveryData
     * @param bool $isPaid
     * @throws AddressSplitException
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws NotImplementedException
     * @throws ObjectException
     * @throws ObjectNotFoundException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws \FourPaws\DeliveryBundle\Exception\NotFoundException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function sendOnlinePaymentDostavistaOrder(Order $order, string $deliveryCode, array $deliveryData, bool $isPaid = false)
    {
        $storeService = Application::getInstance()->getContainer()->get('store.service');
        $orderService = Application::getInstance()->getContainer()->get(OrderService::class);
        $orderPropertyCollection = $order->getPropertyCollection();
        $comments = $order->getField('USER_DESCRIPTION');
        if (is_null($comments)) {
            $comments = '';
        }
        /** @var PropertyValue $item */
        foreach ($orderPropertyCollection as $item) {
            switch ($item->getProperty()['CODE']) {
                case 'STORE_FOR_DOSTAVISTA':
                    $storeXmlId = $item->getValue();
                    break;
                case 'NAME':
                    $name = $item->getValue();
                    break;
                case 'PHONE':
                    $phone = $item->getValue();
                    break;
            }
        }
        /** @var Store $selectedStore */
        $nearShop = $storeService->getStoreByXmlId($storeXmlId);
        $periodTo = $deliveryData['CONFIG']['MAIN']['PERIOD']['TO'];
        $address = $orderService->compileOrderAddress($order)->setValid(true);
        if (isset($order) && $name && $phone && $periodTo && $nearShop) {
            $orderService->sendToDostavista($order, $name, $phone, $comments, $periodTo, $nearShop, $isPaid);
        }
    }

    /**
     * @param OrderInfo $orderInfo
     *
     * @return string
     * @throws SberBankOrderNumberNotFoundException
     */
    protected function getSberbankOrderId(OrderInfo $orderInfo): string
    {
        /** @var Attribute $attribute */
        foreach ($orderInfo->getAttributes() as $attribute) {
            if ($attribute->getName() === Sberbank::ORDER_NUMBER_ATTRIBUTE) {
                return $attribute->getValue();
            }
        }

        throw new SberBankOrderNumberNotFoundException('Order number not found');
    }
}
