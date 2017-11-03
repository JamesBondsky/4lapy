<?php

namespace FourPaws\Catalog;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class CatalogConsumer implements ConsumerInterface
{
    public function execute(AMQPMessage $msg)
    {
        LoggerFactory::create('CatalogConsumer')->debug(sprintf('Message is %s', $msg->getBody()));

    }

}
