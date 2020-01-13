<?php


namespace FourPaws\PersonalBundle\Consumer;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use FourPaws\App\Application;
use FourPaws\PersonalBundle\Service\OrderSubscribeService;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class OrderSubscriptionCreatingConsumer implements ConsumerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var OrderSubscribeService
     */
    private $orderSubscribeService;

    public function __construct() {
        $this->setLogger(LoggerFactory::create('OrderSubscriptionCreatingConsumer'));
    }

    public function execute(AMQPMessage $message)
    {
        $subscriptionId = (int)$message->getBody();

        if ($subscriptionId <= 0) {
            // Скорее всего, в таком случае ошибка у паблишера
            $this->logger->alert('Не создан заказ по подписке (неверный id: ' . $subscriptionId .')');
            return ConsumerInterface::MSG_REJECT;
        }

        $appCont = Application::getInstance()->getContainer();
        $this->orderSubscribeService = $appCont->get('order_subscribe.service');

        $this->orderSubscribeService->sendOrders(1, '', true, [$subscriptionId]);

        return ConsumerInterface::MSG_ACK;
    }
}
