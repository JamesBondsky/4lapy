<?php

namespace FourPaws\External\Manzana\Consumer;

use FourPaws\External\Exception\ManzanaServiceContactSearchNullException;
use FourPaws\External\Exception\ManzanaServiceException;
use FourPaws\External\Manzana\Exception\ContactUpdateException;
use FourPaws\External\Manzana\Model\Client;
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
            $contact = $this->serializer->deserialize($message->getBody(), Client::class, 'json');
           
            if (!$contact->phone) {
                throw new ContactUpdateException('Неожиданное сообщение');
            }
            
            try {
                $contact->contactId = $this->manzanaService->getContactIdByPhone($contact->phone);
            } catch (ManzanaServiceContactSearchNullException $e) {
                /**
                 * Создание пользователя
                 */
            }
            
            $contact = $this->manzanaService->updateContact($contact);
            $this->manzanaService->updateUserCardByClient($contact);
        } catch (ContactUpdateException $e) {
            $this->log()->error(sprintf('Contact update error: %s',
                                        $e->getMessage()));
        } catch (ManzanaServiceException $e) {
            $this->log()->error(sprintf('Manzana error: %s',
                                        $e->getMessage()));
    
            return false;
        }
    
        return true;
    }
}
