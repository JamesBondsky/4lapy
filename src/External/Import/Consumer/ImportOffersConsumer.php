<?php


namespace FourPaws\External\Import\Consumer;


use FourPaws\External\Import\Model\ImportOffer;
use FourPaws\UserBundle\EventController\Event;
use PhpAmqpLib\Message\AMQPMessage;

class ImportOffersConsumer extends ImportConsumerBase
{
    /**
     * @inheritdoc
     *
     * @return bool
     */
    public function execute(AMQPMessage $message): bool
    {
        Event::disableEvents();

        /** @var ImportOffer $importOffer */
        $importOffer = $this->serializer->deserialize($message->getBody(), ImportOffer::class, 'json');

        Event::enableEvents();

        return true;
    }
}
