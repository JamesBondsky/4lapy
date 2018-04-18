<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\External\Manzana\Consumer;

use FourPaws\External\Exception\ManzanaServiceContactSearchMoreOneException;
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
    public function execute(AMQPMessage $message): bool
    {
        try {
            /** @var Client $contact */
            $contact = $this->serializer->deserialize($message->getBody(), Client::class, 'json');

            if (null === $contact || (!$contact->phone && !$contact->contactId)) {
                throw new ContactUpdateException('Неожиданное сообщение');
            }

            if (!$contact->contactId) {
                try {
                    $contact->contactId = $this->manzanaService->getContactIdByPhone($contact->phone);
                } catch (ManzanaServiceContactSearchNullException $e) {
                    /**
                     * Создание пользователя
                     */
                }
            }

            $contact = $this->manzanaService->updateContact($contact);
            $this->manzanaService->updateUserCardByClient($contact);
        } catch (ContactUpdateException $e) {
            $this->log()->error(sprintf(
                'Contact update error: %s',
                $e->getMessage()
            ));
        } catch (ManzanaServiceContactSearchMoreOneException $e) {
            $this->log()->error(sprintf(
                'Too many user`s found: %s',
                $e->getMessage()
            ));
        } catch (ManzanaServiceException $e) {
            $this->log()->error(sprintf(
                'Manzana contact consumer error: %s, message: %s',
                $e->getMessage(),
                $message->getBody()
            ));

            return false;
        }

        return true;
    }
}
