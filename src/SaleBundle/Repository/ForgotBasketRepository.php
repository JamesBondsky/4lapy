<?php

namespace FourPaws\SaleBundle\Repository;

use Bitrix\Main\ObjectException;
use Bitrix\Main\Type\DateTime;
use Doctrine\Common\Collections\Collection;
use FourPaws\BitrixOrmBundle\Orm\D7Repository;
use FourPaws\SaleBundle\Entity\ForgotBasket;
use FourPaws\SaleBundle\Enum\ForgotBasketEnum;
use FourPaws\SaleBundle\Exception\ForgotBasket\NotFoundException;
use FourPaws\SaleBundle\Exception\ForgotBasket\UnknownTypeException;

class ForgotBasketRepository extends D7Repository
{
    /**
     * @param int    $userId
     * @param string $type
     *
     * @return ForgotBasket
     * @throws NotFoundException
     * @throws UnknownTypeException
     */
    public function findByUserId(int $userId, string $type = ForgotBasketEnum::TYPE_NOTIFICATION): ForgotBasket
    {
        $filter = \array_merge(['UF_USER_ID' => $userId], $this->getTypeFilter($type));
        $result = parent::findBy($filter)->first();
        if (!$result instanceof ForgotBasket) {
            throw new NotFoundException(\sprintf('Task for user #%s not found', $userId));
        }

        return $result;
    }

    /**
     * @param string $type
     * @param bool   $useDateFilter
     *
     * @return Collection
     * @throws UnknownTypeException
     * @throws ObjectException
     */
    public function getActive(string $type, bool $useDateFilter): Collection
    {
        $filter = [
            'UF_ACTIVE' => true,
        ];

        $filter = \array_merge($filter, $this->getTypeFilter($type));

        if ($useDateFilter) {
            $filter = \array_merge($filter, $this->getDateFilter($type));
        }

        return $this->findBy($filter);
    }

    /**
     * @param string $type
     *
     * @return array
     * @throws UnknownTypeException
     */
    protected function getTypeFilter(string $type): array
    {
        switch (true) {
            case ForgotBasketEnum::TYPE_NOTIFICATION:
            case ForgotBasketEnum::TYPE_REMINDER:
                $result = ['UF_TASK_TYPE' => $type];
                break;
            default:
                throw new UnknownTypeException(\sprintf('Unknown type %s', $type));
        }

        return $result;
    }

    /**
     * @param string $type
     *
     * @return array
     * @throws UnknownTypeException
     * @throws ObjectException
     */
    protected function getDateFilter(string $type): array
    {
        $time = time();
        $filter = [];
        switch ($type) {
            case ForgotBasketEnum::TYPE_NOTIFICATION:
                $time -= ForgotBasketEnum::INTERVAL_NOTIFICATION;
                $filter[] = [
                    'LOGIC'         => 'OR',
                    '<UF_DATE_EXEC' => DateTime::createFromTimestamp(time() - ForgotBasketEnum::BLOCK_NOTIFICATION),
                    'UF_DATE_EXEC'  => false,
                ];

                break;
            case ForgotBasketEnum::TYPE_REMINDER:
                $time -= ForgotBasketEnum::INTERVAL_REMINDER;
                break;
            default:
                throw new UnknownTypeException(\sprintf('Unknown type %s', $type));
        }

        $filter['<UF_DATE_UPDATE'] = DateTime::createFromTimestamp($time);
        return $filter;
    }
}
