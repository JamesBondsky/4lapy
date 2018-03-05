<?php

namespace FourPaws\SapBundle\Consumer;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Sale\Order;
use FourPaws\SapBundle\Service\Orders\OrderService;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LogLevel;

/**
 * Class OrderOutConsumer
 *
 * @package FourPaws\SapBundle\Consumer
 */
class OrderOutConsumer implements ConsumerInterface, LoggerAwareInterface
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
        
        $this->log()->log(LogLevel::INFO, 'Экспортируется заказ');
        
        try {
            $success = true;
            
            $this->orderService->transformDtoToOrder($orderInfo);
        } catch (\Exception $e) {
            $success = false;
            
            $this->log()->log(LogLevel::CRITICAL, sprintf('Ошибка экспорта заказа: %s', $e->getMessage()));
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
