<?php


namespace FourPaws\External\Push\Consumer;


use FourPaws\App\Application;
use FourPaws\MobileApiBundle\Services\PushEventService;
use FourPaws\UserBundle\EventController\Event;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class PushProcessingConsumer extends PushBase
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

        $pushMessages = $this->decodeMessage($messageText);

        foreach ($pushMessages as $pushMessage) {
            $sessions = $pushEventService->findUsersSessions($pushMessage);

            if (!empty($sessions)) {
                foreach ($sessions as $session) {
                    $pushEvent = $pushEventService->convertToPushEvent($pushMessage, $session);
                    $res = $pushEventService->apiPushEventRepository->createEvent($pushEvent);

                    if ($res->isSuccess() && $pushEvent->getPlatform() == 'ios') {
                        /** @noinspection MissingService */
                        $producer = Application::getInstance()->getContainer()->get('old_sound_rabbit_mq.push_send_ios_producer');
                        $data = $res->getData();
                        $data['ID'] = $res->getId();
                        $data['MESSAGE_TEXT'] = $pushMessage->getMessage();
                        $data['MESSAGE_TYPE'] = $pushMessage->getTypeEntity()->getId();
                        $data['EVENT_ID'] = $pushMessage->getEventId();
                        $data['PHOTO_URL'] = $pushMessage->getPhotoUrl();
                        $producer->publish(json_encode($data));
                    }
                }
            }
        }

        Event::enableEvents();

        return ConsumerInterface::MSG_ACK;
    }
}
