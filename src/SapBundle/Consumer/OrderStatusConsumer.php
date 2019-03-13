<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Consumer;

use FourPaws\SapBundle\Dto\In\Orders\Order;
use FourPaws\SapBundle\Exception\CantUpdateOrderException;
use FourPaws\SapBundle\Service\Orders\OrderService;
use FourPaws\SapBundle\Service\Orders\PaymentService;
use Psr\Log\LogLevel;
use RuntimeException;

/**
 * Class OrderStatusConsumer
 *
 * @package FourPaws\SapBundle\Consumer
 */
class OrderStatusConsumer extends SapConsumerBase
{
    /**
     * @var OrderService
     */
    private $orderService;
    /**
     * @var PaymentService
     */
    private $paymentService;

    /**
     * OrderStatusConsumer constructor.
     *
     * @param OrderService $orderService
     * @param PaymentService $paymentService
     */
    public function __construct(OrderService $orderService, PaymentService $paymentService)
    {
        $this->orderService = $orderService;
        $this->paymentService = $paymentService;
    }

    /**
     * Consume order info (save sap order`s change)
     *
     * @param $order
     *
     * @throws RuntimeException
     * @return bool
     */
    public function consume($order): bool
    {
        /** @var Order $order */
        if (!$this->support($order)) {
            return false;
        }

        $this->log()->log(LogLevel::INFO, 'Импортируется статус заказа', ['orderId' => $order->getId(), 'status' => $order->getStatus()]);
        
        try {
            $success = true;

            $saleOrder = $this->orderService->transformDtoToOrder($order);
            $result = $saleOrder->save();

            if (!$result->isSuccess()) {
                throw new CantUpdateOrderException(sprintf(
                    'Не удалось обновить заказ #%s: %s',
                    $order->getId(),
                    implode(', ', $result->getErrorMessages())
                ));
            }

            if ($warnings = $result->getWarningMessages()) {
                $this->log()->error(sprintf(
                    'Ошибки обновлении заказа #%s: %s',
                    $saleOrder->getId(),
                    implode(', ', $warnings)
                ));
            }
        } catch (\Exception $e) {
            $success = false;

            $this->log()->log(LogLevel::ERROR, sprintf(
                'Ошибка импорта статуса заказа: %s: %s', \get_class($e), $e->getMessage()),
                ['orderId' => $order->getId()]
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
