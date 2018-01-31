<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\AppBundle\Callback\Consumer;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use GuzzleHttp\ClientInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerAwareInterface;

abstract class CallbackConsumerBase implements ConsumerInterface, LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /**
     * @var ClientInterface
     */
    protected $guzzle;


    /**
     * CallbackConsumerBase constructor.
     *
     * @param ClientInterface $guzzle
     *
     * @throws \RuntimeException
     */
    public function __construct(ClientInterface $guzzle)
    {
        $this->guzzle = $guzzle;
    }

    /**
     * @inheritdoc
     */
    abstract public function execute(AMQPMessage $message): bool;
}
