<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Consumer;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use FourPaws\SapBundle\Dto\In\ConfirmPayment\Order;
use FourPaws\SapBundle\Exception\Payment\InvalidOrderNumberException;
use FourPaws\SapBundle\Exception\Payment\NotFoundInvoiceException;
use FourPaws\SapBundle\Exception\Payment\NotFoundOrderException;
use FourPaws\SapBundle\Exception\Payment\OrderZeroPriceException;
use FourPaws\SapBundle\Service\Orders\PaymentService;
use Psr\Log\LoggerAwareInterface;
use RuntimeException;

/**
 * Class PaymentConsumer
 *
 * @package FourPaws\SapBundle\Consumer
 */
class PaymentConsumer implements ConsumerInterface, LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /**
     * @var PaymentService
     */
    private $paymentService;

    /**
     * PaymentConsumer constructor.
     *
     * @param PaymentService $paymentService
     */
    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Consume payment info
     *
     * @param $paymentInfo
     *
     * @throws RuntimeException
     * @return bool
     */
    public function consume($paymentInfo): bool
    {
        if (!$this->support($paymentInfo)) {
            return false;
        }

        $this->log()->info('Обработка задания на оплату');

        $success = false;
        try {
            $this->paymentService->paymentTaskPerform($paymentInfo);
            $success = true;
        } catch (NotFoundOrderException | NotFoundInvoiceException | OrderZeroPriceException | InvalidOrderNumberException $e) {
            /** @var Order $paymentInfo */
            $this->log()->notice(\sprintf(
                'Ошибка обработки задания на оплату: %s: %s',
                \get_class($e),
                $e->getMessage()),
                [
                    'order'      => $paymentInfo->getBitrixOrderId(),
                    'sapOrderId' => $paymentInfo->getSapOrderId(),
                ]
            );
        } catch (\Exception $e) {
            /** @var Order $paymentInfo */
            $this->log()->critical(\sprintf(
                'Ошибка обработки задания на оплату: %s: %s',
                \get_class($e),
                $e->getMessage()),
                [
                    'order'      => $paymentInfo->getBitrixOrderId(),
                    'sapOrderId' => $paymentInfo->getSapOrderId(),
                ]
            );
        }

        return $success;
    }

    /**
     * @param $data
     *
     * @return bool
     */
    public function support($data): bool
    {
        return \is_object($data) && $data instanceof Order;
    }
}
