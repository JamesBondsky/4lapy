<?php

namespace FourPaws\Callback\Consumer;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use GuzzleHttp\ClientInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

abstract class CallbackConsumerBase implements ConsumerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;
    
    /**
     * @var ClientInterface
     */
    protected $guzzle;
    
    protected $logger;
    
    public function __construct(ClientInterface $guzzle)
    {
        $this->guzzle = $guzzle;
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->logger = LoggerFactory::create('callbackService');
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
