<?php

namespace FourPaws\Callback;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use GuzzleHttp\ClientInterface;

class CallbackConsumer implements ConsumerInterface
{
    const HREF = 'https://srv_03:F6RIikaO9QvhlZ7C@4584.vats-on.ru/execsvcscriptplain?name=[VATS-ON] SiteCallBack&startparam1=#phone#&startparam2=#dateTime#&async=0&timeout=#timeout#';
    
    public function __construct(ClientInterface $guzzle, LoggerFactory $logger)
    {
        $this->guzzle = $guzzle;
        $this->logger = LoggerFactory::create('callbackService');
        //https://srv_03:F6RIikaO9QvhlZ7C@4584.vats-on.ru/execsvcscriptplain?name=[VATS-ON] SiteCallBack&startparam1=84995516639&startparam2=2017-11-10 00:00:00&async=0&timeout=10
    }
    
    /**
     * @param AMQPMessage $msg The message
     *
     * @return mixed false to reject and requeue, any other value to acknowledge
     */
    public function execute(AMQPMessage $msg)
    {
        $href    = $msg->getBody();
        $promise = $this->guzzle->sendAsync($href)->then(
            function ($response) {
            },
            function ($response) {
                $this->logger->critical('');
            }
        );
        $promise->wait();
    }
}