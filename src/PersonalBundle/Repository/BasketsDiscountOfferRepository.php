<?php

namespace FourPaws\PersonalBundle\Repository;

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
     * @return int
     * @throws RuntimeException
     * @throws \Bitrix\Main\ObjectException
     */
    public static function addBasket(?int $fUserId, ?int $userId): int
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
        ]);
        if (!$result->isSuccess()) {
            throw new RuntimeException('Не удалось добавить корзину в таблицу по акции. Errors: ' . implode('. ', $result->getErrorMessages()));
        }

        return $result->getId();
    }

    /**
     * @param int|null $fUserId
     * @param int|null $userId
     * @return array|bool
     * @throws RuntimeException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectException
     */
    public static function getRegisteredOfferBasket(?int $fUserId, ?int $userId)
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
     */
    public static function setPromocode(int $offerBasketId, string $promoCode): void
    {
        BasketsDiscountOfferTable::update($offerBasketId, ['promoCode' => $promoCode, 'date_update' => new DateTime()]);
    }
}
