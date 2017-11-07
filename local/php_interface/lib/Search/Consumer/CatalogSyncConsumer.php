<?php

namespace FourPaws\Search\Consumer;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use FourPaws\Search\Model\CatalogSyncMsg;
use JMS\Serializer\Serializer;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class CatalogSyncConsumer implements ConsumerInterface
{
    /**
     * @var Serializer
     */
    protected $serializer;

    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    public function execute(AMQPMessage $msg)
    {

        $cagalogSyncMessage = $this->serializer->deserialize(
            $msg->getBody(),
            CatalogSyncMsg::class,
            'json'
        );

        LoggerFactory::create('CatalogConsumer')->debug(
            sprintf('Message is %s', var_export($cagalogSyncMessage, true))
        );

    }

}
