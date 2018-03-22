<?php

namespace FourPaws\SapBundle\Consumer;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use FourPaws\SapBundle\Dto\In\ConfirmPayment\Order;
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

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Consume payment info
     *
     * @param $paymentInfo
     *
     * @return bool
     * @throws RuntimeException
     */
    public function consume($paymentInfo): bool
    {
        if (!$this->support($paymentInfo)) {
            return false;
        }

        $this->log()->info('Обработка задания на оплату');

        try {
            $success = true;

            $this->paymentService->paymentTaskPerform($paymentInfo);
        } catch (\Exception $e) {
            $success = false;

            $this->log()->critical(sprintf('Ошибка обработки задания на оплату: %s', $e->getMessage()));
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
