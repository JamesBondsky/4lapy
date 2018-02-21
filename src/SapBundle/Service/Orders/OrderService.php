<?php

namespace FourPaws\SapBundle\Service\Orders;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use FourPaws\SaleBundle\Service\OrderService as BaseOrderService;
use JMS\Serializer\ArrayTransformerInterface;
use Psr\Log\LoggerAwareInterface;

/**
 * Class OrderService
 *
 * @package FourPaws\SapBundle\Service\Orders
 */
class OrderService implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /**
     * @var BaseOrderService
     */
    private $baseOrderService;

    public function __construct(BaseOrderService $baseOrderService, ArrayTransformerInterface $arrayTransformer)
    {
        $this->baseOrderService = $baseOrderService;
    }

    public function out(int $orderId)
    {
        $order = $this->baseOrderService->getOrderById($orderId);

        /**
         * @todo consume
         */
    }
}
