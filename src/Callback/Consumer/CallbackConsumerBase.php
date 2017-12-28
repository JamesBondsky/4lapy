<?php

namespace FourPaws\Callback\Consumer;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use FourPaws\App\Application;
use FourPaws\External\ManzanaService;
use JMS\Serializer\Serializer;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

abstract class CallbackConsumerBase implements ConsumerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;
    
    /**
     * @var Serializer
     */
    protected $serializer;
    
    /**
     * @var ManzanaService
     */
    protected $manzanaService;
    
    public function __construct(Serializer $serializer, ManzanaService $manzanaService)
    {
        Application::includeBitrix();
        
        $this->serializer     = $serializer;
        $this->manzanaService = $manzanaService;
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->setLogger(LoggerFactory::create('ManzanaConsumer'));
    }
    
    /**
     * @inheritdoc
     */
    abstract public function execute(AMQPMessage $message) : bool;
    
    /**
     * @return LoggerInterface
     */
    protected function log() : LoggerInterface
    {
        return $this->logger;
    }
    
}
