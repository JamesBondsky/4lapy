<?php

namespace FourPaws\SapBundle\Service\Orders;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use FourPaws\SaleBundle\Service\OrderService;
use FourPaws\SapBundle\Dto\In\ConfirmPayment\Debit;
use FourPaws\SapBundle\Dto\In\ConfirmPayment\Order;
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
    use LazyLoggerAwareTrait;

    /**
     * @var OrderService
     */
    private $orderService;
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
        $this->filesystem = $filesystem;
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
     * @param Debit $debit
     *
     * @return string
     */
    public function getFileName($debit): string
    {
        return sprintf(
            '/%s/%s-%s_%s',
            trim($this->outPath, '/'),
            $debit->getPaymentDate()->format('Ymd'),
            $this->outPrefix,
            $debit->getBitrixOrderId()
        );
    }

    /**
     * @return string
     */
    public function getOutPrefix(): string
    {
        return $this->outPrefix;
    }

    /**
     * @param string $outPrefix
     */
    public function setOutPrefix(string $outPrefix): void
    {
        $this->outPrefix = $outPrefix;
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

    public function tryPaymentRefund(Order $order)
    {
        /**
         * @todo refund
         */
    }
}
