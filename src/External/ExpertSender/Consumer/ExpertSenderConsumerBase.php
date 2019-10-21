<?php

namespace FourPaws\External\ExpertSender\Consumer;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use FourPaws\App\Application;
use FourPaws\External\ExpertsenderService;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use JMS\Serializer\Serializer;
use Psr\Log\LoggerInterface;


abstract class ExpertSenderConsumerBase implements ConsumerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var Serializer
     */
    protected $serializer;

    protected $logName = 'ExpertSenderConsumer';

    /**
     * @var ExpertsenderService
     */
    protected $expertSenderService;

    /**
     * @param Serializer $serializer
     * @param ExpertsenderService $expertSenderService
     */
    public function __construct(Serializer $serializer, ExpertsenderService $expertSenderService)
    {
        Application::includeBitrix();

        $this->serializer = $serializer;
        $this->setLogger(LoggerFactory::create($this->logName, 'expertSender'));
        $this->expertSenderService = $expertSenderService;
    }

    /**
     * @inheritdoc
     */
    abstract public function execute(AMQPMessage $message);

    /**
     * @return LoggerInterface
     */
    protected function log(): LoggerInterface
    {
        return $this->logger;
    }
}
