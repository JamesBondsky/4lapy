<?php

namespace FourPaws\External\ExpertSender\Consumer;

use FourPaws\External\Exception\ExpertsenderServiceException;
use PhpAmqpLib\Message\AMQPMessage;

class ExpertSenderPetConsumer extends ExpertSenderConsumerBase
{
    /**
     * @param AMQPMessage $message
     * @return int
     */
    public function execute(AMQPMessage $message): int
    {
        $data = $this->serializer->deserialize($message->getBody(), 'array', 'json');

        $newPetId = $data['NEW_PET_ID'];
        $oldPetId = $data['OLD_PET_ID'];
        $userId = $data['USER_ID'];

        if (!$newPetId && !$oldPetId) {
            return self::MSG_REJECT;
        }

        if ($newPetId === $oldPetId) {
            return self::MSG_ACK;
        }

        try {
            $result = $this->expertSenderService->sendAfterPetUpdate($userId, $newPetId, $oldPetId);
            if ($result) {
                return self::MSG_ACK;
            }

            return self::MSG_REJECT;
        } catch (ExpertsenderServiceException $e) {
            $this->log()->error(sprintf('Fail to send ExpertSender request: code - [%s], error - %s', $e->getCode(), $e->getMessage()));
            return self::MSG_REJECT;
        }
    }
}
