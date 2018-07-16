<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SaleBundle\Service;

use Adv\Bitrixtools\Tools\BitrixUtils;
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
use Bitrix\Sale\Order;
use Bitrix\Sale\Payment;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\DeliveryBundle\Entity\Terminal;
use FourPaws\Helpers\BusinessValueHelper;
use FourPaws\Helpers\DateHelper;
use FourPaws\SaleBundle\Dto\Fiscalization\CartItems;
use FourPaws\SaleBundle\Dto\Fiscalization\CustomerDetails;
use FourPaws\SaleBundle\Dto\Fiscalization\Fiscal;
use FourPaws\SaleBundle\Dto\Fiscalization\Fiscalization;
use FourPaws\SaleBundle\Dto\Fiscalization\Item;
use FourPaws\SaleBundle\Dto\Fiscalization\ItemQuantity;
use FourPaws\SaleBundle\Dto\Fiscalization\ItemTax;
use FourPaws\SaleBundle\Dto\Fiscalization\OrderBundle;
use FourPaws\SaleBundle\Exception\NotFoundException;
use FourPaws\SaleBundle\Exception\PaymentException;
use FourPaws\SaleBundle\Exception\PaymentReverseException;
use FourPaws\SaleBundle\Payment\Sberbank;
use FourPaws\StoreBundle\Entity\Store;
use JMS\Serializer\ArrayTransformerInterface;

/**
 * Class PaymentService
 *
 * @package FourPaws\SaleBundle\Service
 */
class PaymentService
{
    /**
     * @var ArrayTransformerInterface
     */
    protected $arrayTransformer;

    /**
     * @var BasketService
     */
    protected $basketService;

    /**
     * @var Sberbank
     */
    private $sberbankProcessing;

    /**
     * PaymentService constructor.
     * @param BasketService $basketService
     */
    public function __construct(BasketService $basketService, ArrayTransformerInterface $arrayTransformer)
    {
        $this->arrayTransformer = $arrayTransformer;
        $this->basketService = $basketService;
    }

    /**
     * @param Store $store
     * @param float $paymentSum
     * @return array
     */
    public function getAvailablePaymentsForStore(Store $store, float $paymentSum = 0): array
    {
        $result = [OrderService::PAYMENT_ONLINE];
        if ($store instanceof Terminal) {
            if ($store->isNppAvailable() && $store->getNppValue() >= $paymentSum) {
                if ($store->hasCardPayment()) {
                    $result[] = OrderService::PAYMENT_CASH_OR_CARD;
                } elseif ($store->hasCashPayment()) {
                    $result[] = OrderService::PAYMENT_CASH;
                }
            }
        } else {
            $result[] = OrderService::PAYMENT_CASH_OR_CARD;
        }

        return $result;
    }

    /**
     * @todo переделать на сериализацию
     *
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
            $result = $this->getOrderPaymentType($order) === OrderService::PAYMENT_ONLINE;
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
     * @throws ObjectNotFoundException
     * @throws PaymentException
     * @return bool
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
     * @throws ObjectNotFoundException
     * @throws PaymentException
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

        $email = $name = null;
        /** @var \Bitrix\Sale\PropertyValue $propertyValue */
        foreach ($order->getPropertyCollection() as $propertyValue) {
            if ($propertyValue->getProperty()['IS_PAYER'] === BitrixUtils::BX_BOOL_TRUE) {
                $name = $propertyValue->getValue();
            } elseif ($propertyValue->getProperty()['IS_EMAIL'] === BitrixUtils::BX_BOOL_TRUE) {
                $email = $propertyValue->getValue();
            }
        }

        return $result
            ->setEmail($email ?: 'email')
            ->setContact($name ?: 'name');
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
        $dbMeasure = \CCatalogMeasure::getList();
        while ($arMeasure = $dbMeasure->GetNext()) {
            $measureList[$arMeasure['ID']] = $arMeasure['MEASURE_TITLE'];
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
        ];

        $position = 0;
        /** @var BasketItem $basketItem */
        foreach ($order->getBasket() as $basketItem) {
            if ($skipGifts && $this->basketService->isGiftProduct($basketItem)) {
                continue;
            }

            $arProduct = \CCatalogProduct::GetByID($basketItem->getProductId());
            $taxType = $arProduct['VAT_ID'] > 0 ? (int)$vatList[$arProduct['VAT_ID']] : -1;

            $quantity = (new ItemQuantity())
                ->setValue($basketItem->getQuantity())
                ->setMeasure($measureList[$arProduct['MEASURE']]);

            $tax = (new ItemTax())->setType($vatGateway[$taxType]);

            $itemPrice = floor($basketItem->getPrice() * 100);
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

        $innerPayment = $order->getPaymentCollection()->getInnerPayment();
        if ($innerPayment &&
            $innerPayment->isPaid()
        ) {
            $correction = 0;
            $bonusSum = $innerPayment->getSum() * 100;
            $diff = $total - $bonusSum;

            $items->map(function (Item $item) use (&$correction, $diff, $total) {
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
                ->setName(Loc::getMessage('RBS_PAYMENT_DELIVERY_TITLE') ?: '')
                ->setQuantity((new ItemQuantity())
                    ->setValue(1)
                    ->setMeasure(Loc::getMessage('RBS_PAYMENT_MEASURE_DEFAULT') ?: '')
                )
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
}
