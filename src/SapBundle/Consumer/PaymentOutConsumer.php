<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Consumer;

use FourPaws\SapBundle\Dto\In\ConfirmPayment\Debit;
use FourPaws\SapBundle\Service\Orders\PaymentService;
use Psr\Log\LogLevel;
use RuntimeException;

/**
 * Class PaymentOutConsumer
 *
 * @package FourPaws\SapBundle\Consumer
 */
class PaymentOutConsumer extends SapConsumerBase
{
    /**
     * @var PaymentService
     */
    private $paymentService;

    /**
     * PaymentOutConsumer constructor.
     *
     * @param PaymentService $paymentService
     */
    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Consume order
     *
     * @param $debit
     *
     * @throws RuntimeException
     * @return bool
     */
    public function consume($debit): bool
    {
        if (!$this->support($debit)) {
            return false;
        }
        
        $this->log()->log(LogLevel::INFO, 'Экспортируется оплата');
        
        try {
            $success = true;

            $this->paymentService->out($debit);
        } catch (\Exception $e) {
            $success = false;
            
            $this->log()->log(LogLevel::CRITICAL, \sprintf('Ошибка экспорта оплаты: %s', $e->getMessage()));
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
        return \is_object($data) && $data instanceof Debit;
    }
}
