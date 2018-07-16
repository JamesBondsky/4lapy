<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\External\Manzana\Consumer;

use Exception;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\External\Exception\ManzanaServiceContactSearchMoreOneException;
use FourPaws\External\Exception\ManzanaServiceContactSearchNullException;
use FourPaws\External\Exception\ManzanaServiceException;
use FourPaws\External\Manzana\Exception\ContactUpdateException;
use FourPaws\External\Manzana\Exception\WrongContactMessageException;
use FourPaws\External\Manzana\Model\Client;
use FourPaws\UserBundle\EventController\Event;
use FourPaws\UserBundle\Exception\TooManyUserFoundException;
use FourPaws\UserBundle\Exception\UsernameNotFoundException;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class ManzanaContactConsumer
 *
 * @package FourPaws\External\Manzana\Consumer
 */
class ManzanaContactConsumer extends ManzanaConsumerBase
{
    /**
     * @inheritdoc
     *
     * @throws Exception
     * @throws UsernameNotFoundException
     * @throws TooManyUserFoundException
     * @throws WrongContactMessageException
     */
    public function execute(AMQPMessage $message): bool
    {
        Event::disableEvents();

        try {
            /** @var Client $contact */
            $contact = $this->serializer->deserialize($message->getBody(), Client::class, 'json');

            if (null === $contact || (empty($contact->phone) && empty($contact->contactId))) {
                throw new WrongContactMessageException('Неожиданное сообщение: контакт пуст.');
            }

            if (empty($contact->contactId)) {
                try {
                    $contact->contactId = $this->manzanaService->getContactIdByPhone($contact->phone);
                    /** иначе создание пользователя */
                } catch (ManzanaServiceContactSearchNullException $e) {
                    /**
                     * Создание пользователя
                     */
                }
            }

            $contact = $this->manzanaService->updateContact($contact);
            /**
             * Пропускаем если нет телефона - ибо не найден пользователя для привзяки,
             * также пропускаем, если нет маназановского id
             */
            if ($contact->phone && $contact->contactId) {
                $this->manzanaService->updateUserCardByClient($contact);
            }
        } catch (ContactUpdateException | WrongContactMessageException $e) {
            $this->log()->error(sprintf(
                'Contact update error: %s',
                $e->getMessage()
            ));
        } catch (ManzanaServiceContactSearchMoreOneException $e) {
            $this->log()->info(sprintf(
                'Too many user`s found: %s',
                $e->getMessage()
            ));
            /** не перезапускаем очередь */
        } catch (ManzanaServiceException $e) {
            $this->log()->error(sprintf(
                'Manzana contact consumer error: %s, message: %s',
                $e->getMessage(),
                $message->getBody()
            ));

            sleep(5);

            try {
                $this->manzanaService->updateContactAsync($contact);
            } catch (ApplicationCreateException | ServiceNotFoundException | ServiceCircularReferenceException $e) {
                $this->log()->error(sprintf(
                    'Manzana contact consumer /service/ error: %s, message: %s',
                    $e->getMessage(),
                    $message->getBody()
                ));
            }
        }
        Event::enableEvents();

        return true;
    }
}
