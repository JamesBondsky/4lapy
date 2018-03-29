<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Service\Orders;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use FourPaws\SaleBundle\Service\OrderService;
use FourPaws\SapBundle\Dto\In\ConfirmPayment\Debit;
use FourPaws\SapBundle\Dto\In\ConfirmPayment\Order;
use FourPaws\SapBundle\Service\SapOutFile;
use FourPaws\SapBundle\Service\SapOutInterface;
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
     * PaymentService constructor.
     *
     * @param OrderService $orderService
     * @param SerializerInterface $serializer
     * @param Filesystem $filesystem
     */
    public function __construct(
        OrderService $orderService,
        SerializerInterface $serializer,
        Filesystem $filesystem
    )
    {
        $this->orderService = $orderService;
        $this->serializer = $serializer;
        $this->setFilesystem($filesystem);
    }

    /**
     * @param Order $paymentTask
     */
    public function paymentTaskPerform(Order $paymentTask)
    {
        /**
         * @todo
         */
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
     * @param Order $order
     */
    public function tryPaymentRefund(Order $order)
    {
        /**
         * @todo refund
         */
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

}
