<?php

namespace FourPaws\SapBundle\Service\Orders;

use FourPaws\SaleBundle\Service\OrderService;
use FourPaws\SapBundle\Dto\In\ConfirmPayment\Order;
use JMS\Serializer\SerializerInterface;

/**
 * Class PaymentService
 *
 * @package FourPaws\SapBundle\Service\Orders
 */
class PaymentService
{
    /**
     * @var OrderService
     */
    private $orderService;
    /**
     * @var SerializerInterface
     */
    private $serializer;
    
    /**
     * PaymentService constructor.
     *
     * @param OrderService        $orderService
     * @param SerializerInterface $serializer
     */
    public function __construct(
        OrderService $orderService,
        SerializerInterface $serializer
    ) {
        $this->orderService = $orderService;
        $this->serializer = $serializer;
    }
    
    /**
     * @param Order $paymentTask
     */
    public function paymentTaskPerform(Order $paymentTask) {
        /**
         * @todo
         */
    }
    
    /**
     * @todo ...dto?
     *
     * @param $orderInfo
     */
    public function out($orderInfo)
    {
        /**
         * @todo
         */
    }
}
