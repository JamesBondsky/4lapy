<?php


namespace FourPaws\External\Push\Consumer;

use FourPaws\App\Application;
use FourPaws\AppBundle\Enum\CrudGroups;
use FourPaws\MobileApiBundle\Entity\ApiPushEvent;
use FourPaws\MobileApiBundle\Services\PushEventService;
use FourPaws\UserBundle\EventController\Event;
use JMS\Serializer\DeserializationContext;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class PushSendIosConsumer extends PushBase
{

    public function execute(AMQPMessage $message)
    {
        Event::disableEvents();

        $messageText = json_decode($message->getBody(), true);

        if (!$messageText) {
            return ConsumerInterface::MSG_REJECT;
        }

        /** @var PushEventService $pushEventService */
        $pushEventService = Application::getInstance()->getContainer()->get('FourPaws\MobileApiBundle\Services\PushEventService');

        $pushEvents = $pushEventService->transformer->fromArray(
            [$messageText],
            'array<' . ApiPushEvent::class . '>',
            DeserializationContext::create()->setGroups([CrudGroups::READ])
        );

        $pushEventService->execPushEventsForIos($pushEvents);

        Event::enableEvents();

        return ConsumerInterface::MSG_ACK;
    }
}
