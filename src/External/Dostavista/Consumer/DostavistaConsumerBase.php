<?php

namespace FourPaws\External\Dostavista\Consumer;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use FourPaws\App\Application;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use JMS\Serializer\Serializer;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerAwareInterface;
use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Psr\Log\LoggerInterface;
use FourPaws\External\DostavistaService;
use FourPaws\SaleBundle\Service\OrderService;

abstract class DostavistaConsumerBase implements ConsumerInterface, LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var OrderService
     */
    protected $orderService;

    /**
     * @var DeliveryService
     */
    protected $deliveryService;

    /**
     * @var DostavistaService
     */
    protected $dostavistaService;

    public function __construct(Serializer $serializer, DostavistaService $dostavistaService, OrderService $orderService, DeliveryService $deliveryService)
    {
        Application::includeBitrix();

        $this->serializer = $serializer;
        $this->dostavistaService = $dostavistaService;
        $this->orderService = $orderService;
        $this->deliveryService = $deliveryService;
    }

    /**
     * @inheritdoc
     */
    abstract public function execute(AMQPMessage $message): bool;

    /**
     * @return LoggerInterface
     */
    protected function log(): LoggerInterface
    {
        if ($this->logger === null) {
            $this->withLogType('dostavista');
            $this->logger = LoggerFactory::create($this->getLogName(), $this->getLogType());
        }

        return $this->logger;
    }

}
