<?php


namespace FourPaws\External\Import\Consumer;


use FourPaws\External\ExpertsenderService;
use FourPaws\External\Import\Model\ImportOffer;
use FourPaws\UserBundle\EventController\Event;
use PhpAmqpLib\Message\AMQPMessage;
use Bitrix\Main\Type\DateTime;

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

        $this->userService->sendNotifications([$importOffer->user], $importOffer->offerId, null, $importOffer->promoCode, new \DateTime($importOffer->activeFrom), $importOffer->activeTo ? new \DateTime($importOffer->activeTo) : null, false,'ID');
        $this->userService->sendNotifications([$importOffer->user], $importOffer->offerId, ExpertsenderService::PERSONAL_OFFER_COUPON_START_SEND_EMAIL, $couponId, new \DateTime($importOffer->activeFrom), $importOffer->activeTo ? new \DateTime($importOffer->activeTo) : null, true,'ID');

        Event::enableEvents();

        return true;
    }
}
