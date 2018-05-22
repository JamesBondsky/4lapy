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
use Bitrix\Sale\Order as SaleOrder;
use Bitrix\Sale\Order as BitrixOrder;
use Bitrix\Sale\Payment;
use FourPaws\Helpers\BusinessValueHelper;
use FourPaws\SaleBundle\Exception\PaymentException as SalePaymentException;
use FourPaws\SaleBundle\Payment\Sberbank;
use FourPaws\SaleBundle\Service\OrderService;
use FourPaws\SaleBundle\Service\PaymentService as SalePaymentService;
use FourPaws\SapBundle\Dto\In\ConfirmPayment\Debit;
use FourPaws\SapBundle\Dto\In\ConfirmPayment\Item;
use FourPaws\SapBundle\Dto\In\ConfirmPayment\Order;
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
     * @var Sberbank
     */
    private $sberbankProcessing;

    /**
     * PaymentService constructor.
     *
     * @param OrderService $orderService
     * @param SerializerInterface $serializer
     * @param Filesystem $filesystem
     * @param SalePaymentService $salePaymentService
     * @param UserService $userService
     */
    public function __construct(
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
        $this->userService = $userService;
        $this->setFilesystem($filesystem);

        $this->initPayment();
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
        $order = $this->orderService->getOrderById($paymentTask->getBitrixOrderId());
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
        $amount = $paymentTask->getSumPayed();
        $return = $paymentTask->getSumReturned();

        $orderInvoiceId = $this->getOrderInvoiceId($order);
        if ($amount) {
            $this->response(function () use ($orderInvoiceId, $amount, $fiscalization) {
                return $this->sberbankProcessing->depositPayment($orderInvoiceId, $amount, $fiscalization);
            });
        }

        if ($return === $paymentTask->getSumTotal()) {
            $this->tryPaymentReverse($order);
        } else {
            $this->tryPaymentRefund($order, $amount, $fiscalization);
        }
    }

    /**
     * @param Debit $debit
     *
     * @throws IOException
     */
    public function out(Debit $debit)
    {
        $xml = $this->serializer->serialize($debit, 'xml');

        $this->filesystem->dumpFile($this->getFileName($debit), $xml);
    }

    /**
     * @param BitrixOrder $order
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws NotImplementedException
     * @throws ObjectNotFoundException
     * @throws SalePaymentException
     * @throws ObjectException
     * @throws SystemException
     * @throws \Exception
     */
    public function tryPaymentReverse(BitrixOrder $order) {
        $orderInvoiceId = $this->getOrderInvoiceId($order);
        $this->response(function () use ($orderInvoiceId) {
            return $this->sberbankProcessing->reversePayment($orderInvoiceId);
        });

        /** @var Payment $payment */
        foreach ($order->getPaymentCollection() as $payment) {
            $payment->setPaid('N');
            $payment->save();
        }

        $order->save();
    }

    /**
     * @param BitrixOrder $order
     * @param float       $amount
     * @param array       $fiscalization
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
    public function tryPaymentRefund(BitrixOrder $order, float $amount, array $fiscalization): void
    {
        $orderInvoiceId = $this->getOrderInvoiceId($order);

        /** @var Payment[] $payments */
        $payments = [];
        /** @var Payment $payment */
        foreach ($order->getPaymentCollection() as $payment) {
            if ($payment->isInner()) {
                $payments['inner'] = $payment;
            } else {
                $payments['external'] = $payment;
            }
        }

        $this->response(function () use ($orderInvoiceId, $fiscalization) {
            return $this->sberbankProcessing->refundPayment($orderInvoiceId, $fiscalization['amount']);
        });
        $payments['external']->setField('PS_SUM', $payment->getSumPaid() - $amount);
        $payments['external']->setPaid('N');
        $payments['external']->save();
        $order->save();
    }

    /**
     * @param Debit $debit
     *
     * @return string
     */
    public function getFileName($debit): string
    {
        return \sprintf(
            '/%s/%s-%s_%s',
            \trim($this->outPath, '/'),
            $debit->getPaymentDate()->format('Ymd'),
            $this->outPrefix,
            $debit->getBitrixOrderId()
        );
    }

    /**
     * Init payment
     *
     * @todo shit code
     *
     * @return void
     */
    public function initPayment(): void
    {
        /** @noinspection PhpIncludeInspection */
        require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/sberbank.ecom/config.php';
        $settings = BusinessValueHelper::getPaysystemSettings(3, ['USER_NAME', 'PASSWORD', 'TEST_MODE', 'TWO_STAGE', 'LOGGING']);

        $this->sberbankProcessing = new Sberbank(
            $settings['USER_NAME'],
            $settings['PASSWORD'],
            $settings['TWO_STAGE'] === 'Y',
            $settings['TEST_MODE'] === 'Y',
            $settings['LOGGING'] === 'Y'
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

        $fiscalization = $this->salePaymentService->getFiscalization($order, ['name' => $user->getFullName(), 'email' => $user->getEmail()], (int)$config['TAX_SYSTEM']);
        $map = $fiscalization['itemMap'];
        $itemsAfter = [];

        /** @noinspection ForeachSourceInspection */
        foreach ($fiscalization['fiscal']['orderBundle']['cartItems']['items'] as $item) {
            $itemsAfter[] = $paymentTask->getItems()->map(function (Item $v) use ($map, $item) {
                if (
                    /* Доставка */
                    ($v->getOfferXmlId() >= 2000000 && $item['name'] === null)
                    /* или товар */
                    || $map[$v->getOfferXmlId()] === $item['itemCode']
                ) {
                    $newItem = [];
                    $newItem['quantity']['value'] = $v->getQuantity();
                    $newItem['itemPrice']['value'] = $v->getPrice() * 100;
                    $newItem['itemAmount'] = $v->getSumPrice() * 100;

                    return \array_merge($item, $newItem);
                }

                return null;
            })->filter(function ($v) {
                return null !== $v;
            })->toArray();
        }

        $fiscalization['fiscal']['orderBundle']['cartItems']['items'] = \array_reduce($itemsAfter, function ($to, $from) {
            $to = $to ?? [];

            if ($from) {
                return \array_merge($to, $from);
            }

            return $to;
        });

        return $fiscalization;
    }

    /**
     * @todo CopyPaste from Sberbank pay system.
     * Do refactor.
     *
     * @param callable $responseCallback
     *
     * @return bool
     *
     * @throws SalePaymentException
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

        return $this->sberbankProcessing->parseResponse($response);
    }

    /**
     * @param BitrixOrder $order
     *
     * @return string
     *
     * @throws ObjectNotFoundException
     */
    private function getOrderInvoiceId(BitrixOrder $order): string
    {
        $result = '';
        /** @var Payment $payment */
        foreach ($order->getPaymentCollection() as $payment) {
            if ($payment->isInner()) {
                continue;
            }

            $result = $payment->getField('PS_INVOICE_ID');
        }

        return $result;
    }
}
