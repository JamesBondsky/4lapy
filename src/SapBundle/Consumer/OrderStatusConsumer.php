<?php

namespace FourPaws\SapBundle\Consumer;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use FourPaws\SapBundle\Dto\In\Orders\Order;
use FourPaws\SapBundle\Exception\CantUpdateOrderException;
use FourPaws\SapBundle\Service\Orders\OrderService;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LogLevel;
use RuntimeException;

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
     * @param $order
     *
     * @return bool
     * @throws RuntimeException
     */
    public function consume($order): bool
    {
        if (!$this->support($order)) {
            return false;
        }
        
        $this->log()->log(LogLevel::INFO, 'Импортируется статус заказа');
        
        try {
            $success = true;

            $order = $this->orderService->transformDtoToOrder($order);
            $result = $order->save();
            
            if (!$result->isSuccess()) {
                throw new CantUpdateOrderException(sprintf(
                    'Не удалось обновить заказ #%s: %s',
                    $order->getId(),
                    implode(', ', $result->getErrorMessages())
                ));
            }

            if ($warnings = $result->getWarningMessages()) {
                $this->log()->error(sprintf(
                    'Ошибки обновлении заказа #%s произошли ошибки: %s',
                    $order->getId(),
                    implode(', ', $warnings)
                ));
            }
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
