<?php


namespace FourPaws\External\Import\Consumer;


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
        Event::enableEvents();

        return true;
    }
}
