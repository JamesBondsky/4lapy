<?php

namespace FourPaws\External\Manzana\Consumer;

use FourPaws\External\Manzana\Model\Contact;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class ManzanaContactConsumer
 *
 * @package FourPaws\External\Manzana\Consumer
 */
class ManzanaContactConsumer extends ManzanaConsumerBase
{
    /**
     * @inheritdoc
     */
    public function execute(AMQPMessage $message) : bool
    {
        try {
            $contact = $this->serializer->deserialize($message->getBody(), Contact::class, 'json');
            $this->manzanaService->updateContact($contact);
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
