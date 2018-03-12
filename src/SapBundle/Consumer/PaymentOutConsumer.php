<?php

namespace FourPaws\SapBundle\Consumer;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Sale\Payment;
use FourPaws\SapBundle\Service\Orders\PaymentService;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LogLevel;

/**
 * Class PaymentOutConsumer
 *
 * @package FourPaws\SapBundle\Consumer
 */
class PaymentOutConsumer implements ConsumerInterface, LoggerAwareInterface
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
     * Consume order
     *
     * @param $orderInfo
     *
     * @return bool
     */
    public function consume($orderInfo): bool
    {
        if (!$this->support($orderInfo)) {
            return false;
        }
        
        $this->log()->log(LogLevel::INFO, 'Экспортируется оплата');
        
        try {
            $success = true;

            $this->paymentService->out($orderInfo);
        } catch (\Exception $e) {
            $success = false;
            
            $this->log()->log(LogLevel::CRITICAL, sprintf('Ошибка экспорта оплаты: %s', $e->getMessage()));
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
        /**
         * @todo support... dto?
         */
        return \is_object($data) && $data instanceof Payment;
    }
}
