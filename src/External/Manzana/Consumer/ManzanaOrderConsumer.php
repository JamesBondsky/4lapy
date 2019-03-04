<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\External\Manzana\Consumer;

use Exception;
use FourPaws\App\Application;
use FourPaws\External\Manzana\Exception\WrongContactMessageException;
use FourPaws\PersonalBundle\Service\OrderService;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Exception\TooManyUserFoundException;
use FourPaws\UserBundle\Exception\UsernameNotFoundException;
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
     * @throws UsernameNotFoundException
     * @throws TooManyUserFoundException
     */
    public function execute(AMQPMessage $message): bool
    {
        global $USER;

        $user = $this->serializer->deserialize($message->getBody(), User::class, 'json');

        try {
            /** @var OrderService $orderService */
            $orderService = Application::getInstance()->getContainer()->get('order.service');

            $userId = $user->getId();
            if ($USER->GetID() !== $userId) {
                $USER->Authorize($userId);
            }
            $orderService->importOrdersFromManzana($user);
        } catch (\Exception $e) {
            $this->log()->error(\sprintf(
                'Manzana order consumer /service/ error: %s, message: %s',
                $e->getMessage(),
                $message->getBody()
            ));
        }

        return true;
    }
}
