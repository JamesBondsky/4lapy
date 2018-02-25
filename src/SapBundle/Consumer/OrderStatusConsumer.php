<?php

namespace FourPaws\SapBundle\Consumer;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use FourPaws\SapBundle\Dto\In\Orders\Order;
use FourPaws\SapBundle\Service\Orders\OrderService;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LogLevel;

/**
 * Class OrderStatusConsumer
 *
 * @package FourPaws\SapBundle\Consumer
 */
class OrderStatusConsumer implements ConsumerInterface, LoggerAwareInterface
{
    use LazyLoggerAwareTrait;
    
    /**
     * @var OrderService
     */
    private $orderService;
    
    /**
     * OrderStatusConsumer constructor.
     *
     * @param OrderService $orderService
     */
    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }
    
    /**
     * Consume order info (save sap order`s change)
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
        
        $this->log()->log(LogLevel::INFO, 'Импортируется статус заказа');
        
        try {
            $success = true;
            
            $this->orderService->transformDtoToOrder($orderInfo);
        } catch (\Exception $e) {
            $success = false;
            
            $this->log()->log(LogLevel::ERROR, sprintf('Ошибка импорта статуса заказа: %s', $e->getMessage()));
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
