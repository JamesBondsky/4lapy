<?php


namespace FourPaws\External\Push\Consumer;


use FourPaws\App\Application;
use FourPaws\BitrixOrm\Collection\ImageCollection;
use FourPaws\External\PushService;
use FourPaws\MobileApiBundle\Entity\ApiPushMessage;
use FourPaws\MobileApiBundle\Services\PushEventService;
use JMS\Serializer\Serializer;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

abstract class PushBase implements ConsumerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var Serializer
     */
    protected $serializer;

    /** @var PushService $pushService */
    protected $pushService;

    public function __construct(Serializer $serializer, PushService $pushService)
    {
        $this->serializer = $serializer;
        $this->pushService = $pushService;
    }

    /**
     * @inheritdoc
     */
    abstract public function execute(AMQPMessage $message);

    /**
     * @return LoggerInterface
     */
    protected function log() : LoggerInterface
    {
        return $this->logger;
    }

    /**
     * Преобразование сообщения из рэббита в массив объектов
     * @param $messageText
     * @return ApiPushMessage[]
     */
    protected function decodeMessage($messageText)
    {
        /** @var PushEventService $pushEventService */
        $pushEventService = Application::getInstance()->getContainer()->get('FourPaws\MobileApiBundle\Services\PushEventService');

        foreach ($messageText as &$messageTextItem) {
            if ($messageTextItem['UF_PHOTO']) {
                $messageTextItem['PHOTO_URL'] = \CFile::GetPath($messageTextItem['UF_PHOTO']);
            }
        }

        /** @var ApiPushMessage[] $pushMessages */
        $pushMessages = $pushEventService->transformer->fromArray(
            $messageText,
            'array<' . ApiPushMessage::class . '>'
        );

        return $pushMessages;
    }
}
