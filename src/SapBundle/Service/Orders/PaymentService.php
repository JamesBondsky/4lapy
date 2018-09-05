<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Service\Orders;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\IO\InvalidPathException;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Order as SaleOrder;
use Bitrix\Sale\Payment;
use FourPaws\Helpers\DateHelper;
use FourPaws\SaleBundle\Dto\Fiscalization\Fiscalization;
use FourPaws\SaleBundle\Dto\Fiscalization\Item as FiscalItem;
use FourPaws\SaleBundle\Exception\PaymentException as SalePaymentException;
use FourPaws\SaleBundle\Service\OrderService as SaleOrderService;
use FourPaws\SaleBundle\Service\PaymentService as SalePaymentService;
use FourPaws\SapBundle\Dto\In\ConfirmPayment\Item;
use FourPaws\SapBundle\Dto\In\ConfirmPayment\Order;
use FourPaws\SapBundle\Dto\Out\Payment\Debit as OutDebit;
use FourPaws\SapBundle\Enum\SapOrder;
use FourPaws\SapBundle\Exception\NotFoundOrderException;
use FourPaws\SapBundle\Exception\PaymentException;
use FourPaws\SapBundle\Service\SapOutFile;
use FourPaws\SapBundle\Service\SapOutInterface;
use FourPaws\UserBundle\Service\UserService;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class PaymentService
 *
 * @package FourPaws\SapBundle\Service\Orders
 */
class PaymentService implements LoggerAwareInterface, SapOutInterface
{
    use LazyLoggerAwareTrait, SapOutFile;

    private const MODULE_PROVIDER_CODE = 'sberbank.ecom';
    private const OPTION_FISCALIZATION_CODE = 'FISCALIZATION';

    /**
     * @var OrderService
     */
    private $orderService;
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
     * @var SalePaymentService
     */
    private $salePaymentService;

    /**
     * PaymentService constructor.
     *
     * @param SaleOrderService    $saleOrderService
     * @param OrderService        $orderService
     * @param SerializerInterface $serializer
     * @param Filesystem          $filesystem
     * @param SalePaymentService  $salePaymentService
     * @param UserService         $userService
     */
    public function __construct(
        SaleOrderService $saleOrderService,
        OrderService $orderService,
        SerializerInterface $serializer,
        Filesystem $filesystem,
        SalePaymentService $salePaymentService,
        UserService $userService
    )
    {
        $this->orderService = $orderService;
        $this->serializer = $serializer;
        $this->salePaymentService = $salePaymentService;
        $this->setFilesystem($filesystem);
    }

    /**
     * @param Order $paymentTask
     *
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws NotImplementedException
     * @throws ObjectException
     * @throws ObjectNotFoundException
     * @throws SalePaymentException
     * @throws SystemException
     * @throws \Exception
     */
    public function paymentTaskPerform(Order $paymentTask)
    {
        /**
         * Check order existence
         */
        $order = SaleOrder::loadByAccountNumber($paymentTask->getBitrixOrderId());
        if (!$order) {
            throw new NotFoundOrderException(
                sprintf('Order with number %s not found', $paymentTask->getBitrixOrderId())
            );
        }

        if (!$paymentTask->getSumPayed() && !$paymentTask->getSumTotal()) {
            throw new PaymentException('Сумма на списание и сумма заказа равны нулю');
        }

        if (!$orderInvoiceId = $this->salePaymentService->getOrderInvoiceId($order)) {
            throw new PaymentException('У заказа не указан номер инвойса');
        }

        $orderInfo = $this->salePaymentService->getSberbankOrderStatusByOrderId($orderInvoiceId);
        if ($fiscalization = $this->getFiscalization($order, $paymentTask)) {
            $this->salePaymentService->validateFiscalization($fiscalization, $orderInfo);
        }

        $amount = $paymentTask->getSumPayed();
        $return = $paymentTask->getSumReturned();

        if ($amount) {
            $fiscal = [];
            if ($fiscalization) {
                $fiscal = $this->salePaymentService->fiscalToArray($fiscalization);
                $amount = $this->salePaymentService->getFiscalTotal($fiscalization);
            }

            $this->salePaymentService->depositPayment($order, $amount, $fiscal);

            $debit = (new OutDebit())
                ->setPayMerchantCode(SapOrder::ORDER_PAYMENT_ONLINE_MERCHANT_ID)
                ->setSapOrderId($paymentTask->getSapOrderId())
                ->setBitrixOrderId($paymentTask->getBitrixOrderId())
                ->setClientId($order->getUserId())
                ->setClientFio($this->orderService->getPropertyValueByCode($order, 'NAME'))
                ->setClientPhone($this->orderService->getPropertyValueByCode($order, 'PHONE'))
                ->setClientAddress($this->orderService->getDeliveryAddress($order)->__toString())
                ->setPayHoldTransaction($orderInvoiceId)
                ->setPayStatus(SapOrder::ORDER_PAYMENT_STATUS_PAYED);
            $deliveryDate = \DateTime::createFromFormat(
                'd.m.Y',
                $this->orderService->getPropertyValueByCode($order, 'DELIVERY_DATE')
            );
            if ($deliveryDate instanceof \DateTime) {
                $debit->setDeliveryDate($deliveryDate);
            }

            $datePaid = null;
            /** @var Payment $payment */
            foreach ($order->getPaymentCollection() as $payment) {
                if ($payment->isInner()) {
                    continue;
                }

                $datePaid = $payment->getField('DATE_PAID');
            }

            if ($datePaid instanceof DateTime) {
                $debit->setPaymentDate(DateHelper::convertToDateTime($datePaid));
            }

            $this->out($debit);
        } elseif ($return) {
            $this->salePaymentService->cancelPayment($order);
        }
    }

    /**
     * @param OutDebit $debit
     *
     * @throws IOException
     */
    public function out(OutDebit $debit)
    {
        $xml = $this->serializer->serialize($debit, 'xml');

        $this->filesystem->dumpFile($this->getFileName($debit), $xml);
    }

    /**
     * @param OutDebit $debit
     *
     * @return string
     */
    public function getFileName($debit): string
    {
        return \sprintf(
            '/%s/%s%s.xml',
            \trim($this->outPath, '/'),
            $this->outPrefix,
            $debit->getBitrixOrderId()
        );
    }

    /**
     * @param SaleOrder $order
     * @param Order     $paymentTask
     *
     * @return Fiscalization|null
     *
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws ObjectNotFoundException
     * @throws SystemException
     * @throws InvalidPathException
     * @throws ObjectPropertyException
     */
    private function getFiscalization(SaleOrder $order, Order $paymentTask): ?Fiscalization
    {
        $config = Option::get(self::MODULE_PROVIDER_CODE, self::OPTION_FISCALIZATION_CODE, []);
        /** @noinspection UnserializeExploitsInspection */
        $config = \unserialize($config, []);

        if ($config['ENABLE'] !== 'Y') {
            return null;
        }

        /** @var array[] $paymentTaskItems */
        $paymentTaskItems = [];
        $paymentTask->getItems()->map(function (Item $item) use (&$paymentTaskItems) {
            $xmlId = $item->getOfferXmlId();
            if (!isset($paymentTaskItems[$xmlId])) {
                $paymentTaskItems[$xmlId] = [];
            }

            $found = false;
            /** @var Item $pti */
            foreach ($paymentTaskItems[$xmlId] as $pti) {
                if ($pti->getPrice() === $item->getPrice()) {
                    $pti->setQuantity((int)$pti->getQuantity() + (int)$item->getQuantity());
                    $pti->setSumPrice($pti->getPrice() * (int)$pti->getQuantity());
                    $found = true;
                }
            }

            if (!$found) {
                $paymentTaskItems[$xmlId][] = clone $item;
            }
        });

        /**
         * Сортируем позиции по возрастанию цены, исходя из того,
         * что в корзине позиция со скидкой всегда первая
         */
        foreach ($paymentTaskItems as $items) {
            \usort($items, function (Item $item1, Item $item2) {
                return $item1->getPrice() <=> $item2->getPrice();
            });
        }

        $fiscalization = $this->salePaymentService->getFiscalization($order, (int)$config['TAX_SYSTEM']);
        $items = $fiscalization->getFiscal()->getOrderBundle()->getCartItems()->getItems();

        $fiscalization->getFiscal()->getOrderBundle()->getCartItems()->setItems(
            $items->map(
                function (FiscalItem $item) use (&$paymentTaskItems) {
                    $xmlId = $item->getXmlId();
                    if (!isset($paymentTaskItems[$xmlId])) {
                        $item->getQuantity()->setValue(0);
                    } else {
                        /** @var Item $pti */
                        foreach ($paymentTaskItems[$xmlId] as $i => $pti) {
                            $price = $pti->getPrice() * 100;

                            $newQuantity = $pti->getQuantity() > $item->getQuantity()->getValue()
                                ? $item->getQuantity()->getValue()
                                : $pti->getQuantity();
                            $remainingQuantity = $pti->getQuantity() - $newQuantity;
                            $item->getQuantity()->setValue((int)$newQuantity);
                            $item->setTotal(round($pti->getPrice() * $newQuantity * 100));
                            $item->setPrice(round($price));

                            if ($remainingQuantity > 0) {
                                $pti->setQuantity($remainingQuantity);
                            } else {
                                unset($paymentTaskItems[$xmlId][$i]);
                            }
                            break;
                        }
                    }

                    return $item;
                }
            )->filter(
                function (FiscalItem $item) {
                    return $item->getQuantity()->getValue() > 0;
                }
            )
        );

        return $fiscalization;
    }
}
