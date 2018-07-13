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
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Order as SaleOrder;
use Bitrix\Sale\Payment;
use FourPaws\Helpers\DateHelper;
use FourPaws\SaleBundle\Exception\PaymentException as SalePaymentException;
use FourPaws\SaleBundle\Service\OrderService as SaleOrderService;
use FourPaws\SaleBundle\Service\PaymentService as SalePaymentService;
use FourPaws\SapBundle\Dto\In\ConfirmPayment\Item;
use FourPaws\SapBundle\Dto\In\ConfirmPayment\Order;
use FourPaws\SapBundle\Dto\Out\Payment\Debit as OutDebit;
use FourPaws\SapBundle\Enum\SapOrder;
use FourPaws\SapBundle\Exception\NotFoundOrderException;
use FourPaws\SapBundle\Exception\NotFoundOrderUserException;
use FourPaws\SapBundle\Exception\PaymentException;
use FourPaws\SapBundle\Service\SapOutFile;
use FourPaws\SapBundle\Service\SapOutInterface;
use FourPaws\UserBundle\Entity\User;
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
     * @var SaleOrderService
     */
    private $saleOrderService;
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
     * @var UserService
     */
    private $userService;

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
        $this->saleOrderService = $saleOrderService;
        $this->orderService = $orderService;
        $this->serializer = $serializer;
        $this->salePaymentService = $salePaymentService;
        $this->userService = $userService;
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
        $user = $this->userService->getUserRepository()->find($order->getUserId());

        if (null === $user) {
            throw new NotFoundOrderUserException(
                \sprintf(
                    'User with id %s is not found',
                    $order->getUserId()
                )
            );
        }

        if (!$paymentTask->getSumPayed() && !$paymentTask->getSumTotal()) {
            throw new PaymentException('Сумма на списание и сумма заказа равны нулю');
        }

        $fiscalization = $this->getFiscalization($order, $user, $paymentTask);
        $orderInvoiceId = $this->salePaymentService->getOrderInvoiceId($order);

        $amount = $paymentTask->getSumPayed();
        $return = $paymentTask->getSumReturned();

        if ($amount) {
            $this->salePaymentService->depositPayment($order, $amount, $fiscalization);

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
     * @param User $user
     * @param Order $paymentTask
     *
     * @return array|null
     *
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     */
    private function getFiscalization(SaleOrder $order, User $user, Order $paymentTask): ?array
    {
        $config = Option::get(self::MODULE_PROVIDER_CODE, self::OPTION_FISCALIZATION_CODE, []);
        /** @noinspection UnserializeExploitsInspection */
        $config = \unserialize($config, []);

        if ($config['ENABLE'] !== 'Y') {
            return null;
        }

        $fiscalization = $this->salePaymentService->getFiscalization($order, (int)$config['TAX_SYSTEM']);
        $map = $fiscalization['itemMap'];
        $itemsAfter = [];

        /** @noinspection ForeachSourceInspection */
        foreach ($fiscalization['fiscal']['orderBundle']['cartItems']['items'] as $item) {
            $itemsAfter[] = $paymentTask->getItems()->map(function (Item $v) use (&$map, $item) {
                if ($v->getQuantity() && (
                        /* Доставка */
                        ($v->getOfferXmlId() >= 2000000 && $item['name'] === null)
                        /* или товар */
                        || (int)$map[$v->getOfferXmlId()]['id'] === $item['itemCode']
                    )
                ) {
                    $newItem = $item;
                    $newItem['quantity']['value'] = $v->getQuantity();
                    $newItem['itemPrice'] = (int)($v->getPrice() * 100);
                    $newItem['itemAmount'] = (int)($v->getSumPrice() * 100);

                    if (($newItem['itemAmount'] <= $item['itemAmount']) &&
                        ($newItem['quantity']['value'] <= $item['quantity']['value'])
                    ) {
                        $map[$v->getOfferXmlId()]['count']--;
                        if ($map[$v->getOfferXmlId()]['count'] < 0) {
                            unset($map[$v->getOfferXmlId()]);
                        } else {
                            return $newItem;
                        }
                    }
                }

                return null;
            })->filter(function ($v) {
                return null !== $v;
            })->toArray();
        }

        $amount = 0;
        $fiscalization['fiscal']['orderBundle']['cartItems']['items'] = \array_reduce($itemsAfter, function ($to, $from) use (&$amount) {
            $to = $to ?? [];

            if ($from) {
                $amount += current($from)['itemAmount'];
                return \array_merge($to, $from);
            }

            return $to;
        });
        $fiscalization['amount'] = (int)$amount;

        return $fiscalization;
    }
}
