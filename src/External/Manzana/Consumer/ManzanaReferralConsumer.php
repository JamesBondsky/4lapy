<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\External\Manzana\Consumer;

use FourPaws\External\Exception\ManzanaServiceException;
use FourPaws\External\Manzana\Exception\ReferralAddException;
use FourPaws\External\Manzana\Model\Client;
use FourPaws\External\Manzana\Model\ReferralParams;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class ManzanaReferralConsumer
 *
 * @package FourPaws\External\Manzana\Consumer
 */
class ManzanaReferralConsumer extends ManzanaConsumerBase
{
    /**
     * @inheritdoc
     */
    public function execute(AMQPMessage $message): bool
    {
        try {
            /** @var ReferralParams $referralParams */
            $referralParams = $this->serializer->deserialize($message->getBody(), Client::class, 'json');

            if (null === $referralParams || (!$referralParams->phone && !$referralParams->cardNumber)) {
                /** @noinspection ExceptionsAnnotatingAndHandlingInspection
                 имхо  -исключение ловится ниже в catch - шторм - открой глаза*/
                throw new ReferralAddException('Неожиданное сообщение');
            }

            $this->manzanaService->addReferralByBonusCard($referralParams);
        } catch (ReferralAddException $e) {
            $this->log()->error(sprintf(
                'Contact update error: %s',
                $e->getMessage()
            ));
        } catch (ManzanaServiceException $e) {
            $this->log()->error(sprintf(
                'Manzana error: %s',
                $e->getMessage()
            ));

            return false;
        }

        return true;
    }
}
