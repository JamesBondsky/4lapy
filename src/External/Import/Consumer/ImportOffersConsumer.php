<?php


namespace FourPaws\External\Import\Consumer;


use FourPaws\External\ExpertsenderService;
use FourPaws\External\Import\Model\ImportOffer;
use FourPaws\UserBundle\EventController\Event;
use PhpAmqpLib\Message\AMQPMessage;
use Bitrix\Main\Type\DateTime;
use PhpAmqpLib\Wire\AMQPWriter;

class ImportOffersConsumer extends ImportConsumerBase
{
    /**
     * @inheritdoc
     *
     * @return bool
     */
    public function execute(AMQPMessage $message): bool
    {
        Event::disableEvents();

        /** @var ImportOffer $importOffer */
        $importOffer = $this->serializer->deserialize($message->getBody(), ImportOffer::class, 'json');

        if ($importOffer->activeFrom) {
            $currentDate = new \DateTime();
            $currentDate->setTimezone(new \DateTimeZone('Europe/Moscow'));
            $dateActive = new \DateTime($importOffer->activeFrom);

            if ($dateActive > $currentDate ?? !$dateActive) {
                return self::MSG_REJECT_REQUEUE;
            }
        }

        $couponId = $this->personalCouponManager::add([
            'UF_PROMO_CODE' => $importOffer->promoCode,
            'UF_OFFER' => $importOffer->offerId,
            'UF_DATE_CREATED' => new DateTime($importOffer->dateCreate),
            'UF_DATE_CHANGED' => new DateTime($importOffer->dateChanged),
        ])->getId();

        $this->personalCouponUsersManager::add([
            'UF_USER_ID' => $importOffer->user,
            'UF_COUPON' => $couponId,
            'UF_DATE_CREATED' => new DateTime($importOffer->dateCreate),
            'UF_DATE_CHANGED' => new DateTime($importOffer->dateChanged),
        ]);

        $this->keepAlive($message);

        $this->userService->sendNotifications([$importOffer->user], $importOffer->offerId, null, $importOffer->promoCode, new \DateTime($importOffer->activeFrom), $importOffer->activeTo ? new \DateTime($importOffer->activeTo) : null, false,'ID');
        $this->userService->sendNotifications([$importOffer->user], $importOffer->offerId, ExpertsenderService::PERSONAL_OFFER_COUPON_START_SEND_EMAIL, $importOffer->promoCode, new \DateTime($importOffer->activeFrom), $importOffer->activeTo ? new \DateTime($importOffer->activeTo) : null, true,'ID', $couponId);

        $this->keepAlive($message);
        Event::enableEvents();

        return true;
    }

    public function keepAlive($message)
    {
        if (!isset($message->delivery_info['channel'])) {
            return;
        }

        /** @var AMQPChannel $channel */
        $channel = $message->delivery_info['channel'];

        $pkt = new AMQPWriter();
        $pkt->write_octet(8);
        $pkt->write_short(0);
        $pkt->write_long(0);
        $pkt->write_octet(0xCE);

        $channel->getConnection()->write($pkt->getvalue());
    }
}
