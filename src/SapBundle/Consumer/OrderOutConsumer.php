<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Consumer;

use Bitrix\Sale\Order;
use FourPaws\SapBundle\Exception\CantUpdateOrderException;
use FourPaws\SapBundle\Service\Orders\OrderService;
use Psr\Log\LogLevel;
use RuntimeException;

/**
 * Class OrderOutConsumer
 *
 * @package FourPaws\SapBundle\Consumer
 */
class OrderOutConsumer extends SapConsumerBase
{
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
     * @param Order $order
     *
     * @throws RuntimeException
     * @return bool
     */
    public function consume($order): bool
    {
        if (!$this->support($order)) {
            return false;
        }
        /** @var Order $order */
        $this->log()->log(
            LogLevel::INFO,
            \sprintf('Экспортируется заказ #%s (%s)', $order->getId(), $order->getField('ACCOUNT_NUMBER'))
        );

        try {
            $success = true;

            $this->orderService->out($order);
            $this->orderService->setPropertyValue($order->getPropertyCollection(), 'IS_EXPORTED', 'Y');
            $result = $order->save();
            if (!$result->isSuccess()) {
                throw new CantUpdateOrderException(\implode(', ', $result->getErrorMessages()));
            }
        } catch (\Exception $e) {
            $success = false;
            
            $this->log()->log(
                LogLevel::CRITICAL,
                sprintf(
                    'Ошибка экспорта заказа #%s (%s): %s',
                    $order->getId(),
                    $order->getField('ACCOUNT_NUMBER'),
                    $e->getMessage()
                )
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
