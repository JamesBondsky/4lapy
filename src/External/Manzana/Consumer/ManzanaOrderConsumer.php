<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\External\Manzana\Consumer;

use Exception;
use FourPaws\App\Application;
use FourPaws\External\Exception\ManzanaServiceContactSearchNullException;
use FourPaws\PersonalBundle\Service\OrderService;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\EventController\Event;
use FourPaws\UserBundle\Exception\NotFoundException;
use FourPaws\UserBundle\Exception\UserException;
use FourPaws\UserBundle\Service\UserSearchInterface;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class ManzanaOrderConsumer
 *
 * @package FourPaws\External\Manzana\Consumer
 */
class ManzanaOrderConsumer extends ManzanaConsumerBase
{
    /**
     * @inheritdoc
     *
     * @throws Exception
     */
    public function execute(AMQPMessage $message)
    {
        Event::disableEvents();

        try {
            global $USER;

            $user = $this->serializer->deserialize($message->getBody(), User::class, 'json');

            /** @var OrderService $orderService */
            $orderService = Application::getInstance()->getContainer()->get('order.service');

            $userId = $user->getId();
            if ($userId <= 0)
            {
                throw new UserException(\sprintf(
                    'Can\'t import user\'s orders: wrong user id: %s',
                    $userId
                ));
            }

            if ($userId > 0 && $USER->GetID() != $userId) {
                $USER->Authorize($userId, false, false);
            }
            $orderService->importOrdersFromManzana($user);
        } catch (\Exception $e) {
            $this->log()->error(\sprintf(
                'Manzana order consumer /service/ error: %s, message: %s',
                $e->getMessage(),
                $message->getBody()
            ));
        }

        Event::enableEvents();

//        if (isset($userId)) {
//            $userService = Application::getInstance()->getContainer()->get(UserSearchInterface::class);
//            $manzanaOrdersImportUserRepository = $userService->getManzanaOrdersImportUserRepository();
//            try
//            {
//                $manzanaOrdersImportUserRepository->deleteUser($userId);
//            } catch (NotFoundException $e)
//            {
//            }
//        }

        return static::MSG_ACK;
    }
}
