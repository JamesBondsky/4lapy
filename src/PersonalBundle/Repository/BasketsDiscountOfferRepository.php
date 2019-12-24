<?php

namespace FourPaws\PersonalBundle\Repository;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\ORM\Data\AddResult;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Type\DateTime;
use FourPaws\PersonalBundle\Exception\RuntimeException;
use FourPaws\PersonalBundle\Repository\Table\BasketsDiscountOfferTable;

class BasketsDiscountOfferRepository
{
    /**
     * @param int|null $fUserId
     * @param int|null $userId
     * @param bool $isFromMobile
     * @return int
     * @throws RuntimeException
     * @throws \Bitrix\Main\ObjectException
     */
    public static function addBasket(?int $fUserId, ?int $userId, bool $isFromMobile): int
    {
        if (!$userId && !$fUserId) {
            throw new RuntimeException('Не удалось добавить корзину в таблицу по акции, т.к. не заданы $userId и $fUserId');
        }

        /** @var AddResult $result */
        $result = BasketsDiscountOfferTable::add([
            'fUserId' => $fUserId,
            'userId' => $userId,
            'date_insert' => new DateTime(),
            'date_update' => new DateTime(),
            'order_created' => 0,
            'isFromMobile' => $isFromMobile,
        ]);
        if (!$result->isSuccess()) {
            throw new RuntimeException('Не удалось добавить корзину в таблицу по акции. Errors: ' . implode('. ', $result->getErrorMessages()));
        }

        $logger = LoggerFactory::create('CouponPoolRepository', '20-20');
        $logger->info(__METHOD__ . '. new row id: ' . $result->getId());

        return $result->getId();
    }

    /**
     * @param int|null $fUserId
     * @param int|null $userId
     * @return array|bool
     * @throws RuntimeException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getRegisteredOfferBasket(?int $fUserId = null, ?int $userId = null)
    {
        if (!$userId && !$fUserId) {
            throw new RuntimeException('Не удалось проверить наличие зарегистрированной корзины в таблице по акции, т.к. не заданы $userId и $fUserId');
        }

        $subFilter = [];
        if ($userId) {
            $subFilter[] = ['userId', $userId];
        }
        if ($fUserId) {
            $subFilter[] = ['fUserId', $fUserId];
        }

        $result = BasketsDiscountOfferTable::query()
            ->where(Query::filter()
                ->logic('or')
                ->where($subFilter)
            )
            ->where('date_insert', '>=', (new DateTime())->setTime(0, 0, 0))
            ->where('order_created', false)
            ->setOrder(['id' => 'desc'])
            ->setSelect([
                'id',
                'fUserId',
                'userId',
                'promoCode',
                'isFromMobile',
            ])
            ->setLimit(1)
            ->exec()
            ->fetch();

        if (!$result) {
            return false;
        }

        if ($userId && !$result['userId']) {
            BasketsDiscountOfferTable::update($result['id'], ['userId' => $userId, 'date_update' => new DateTime()]);
        }
        if ($fUserId && !$result['fUserId']) {
            BasketsDiscountOfferTable::update($result['id'], ['fUserId' => $fUserId, 'date_update' => new DateTime()]);
        }

        return $result;
    }

    /**
     * @param int $offerBasketId
     * @param string $promoCode
     * @throws \Bitrix\Main\ObjectException
     * @throws \Exception
     */
    public static function setPromocode(int $offerBasketId, string $promoCode): void
    {
        BasketsDiscountOfferTable::update($offerBasketId, ['promoCode' => $promoCode, 'date_update' => new DateTime()]);
    }

    /**
     * @param int $fromFUserId
     * @param int $toFUserId
     * @return string|void
     * @throws RuntimeException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function changePromoCodeFUserOwner(int $fromFUserId, int $toFUserId)
    {
        if ($fromFUserId <= 0 || $toFUserId <= 0) {
            throw new RuntimeException(__METHOD__ . '. не удалось перенести промокод на другой fuser, т.к. не заполнены $fromFUserId, $toFUserId. $fromFUserId: ' . $fromFUserId . ', $toFUserId: ' . $toFUserId);
        }
        $fromUser = self::getRegisteredOfferBasket($fromFUserId);

        if (!$fromUser) {
            return;
        }

        $toUser = BasketsDiscountOfferTable::query()
            ->where('fUserId', $toFUserId)
            ->where('date_insert', '>=', (new DateTime())->setTime(0, 0, 0))
            ->setSelect([
                'id',
                'order_created',
                'promoCode',
            ])
            ->setOrder(['id' => 'desc'])
            ->setLimit(1)
            ->exec()
            ->fetch();

        $logger = LoggerFactory::create('CouponPoolRepository', '20-20');
        $logger->info(__METHOD__ . '. $fromFUserId: ' . $fromFUserId . '. $toFUserId: ' . $toFUserId);

        if (!$toUser || $toUser['order_created']) {
            BasketsDiscountOfferTable::update($fromUser['id'], ['fUserId' => $toFUserId]);
        } else {
            BasketsDiscountOfferTable::update($fromUser['id'], ['promoCode' => null]);
            BasketsDiscountOfferTable::update($toUser['id'], ['promoCode' => $fromUser['promoCode'] ?: ($toUser['promoCode'] ?: null)]);
        }

        return $fromUser['promoCode'];
    }
}
