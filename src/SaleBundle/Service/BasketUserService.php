<?php

namespace FourPaws\SaleBundle\Service;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Sale\Fuser;
use FourPaws\SaleBundle\Exception\BasketUserInitializeException;

class BasketUserService
{
    /**
     * @return int
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws BasketUserInitializeException
     */
    public function getCurrentUserId(): int
    {
        $result = Fuser::getId();

        if (!$result) {
            throw new BasketUserInitializeException('Failed to initialize basket user');
        }

        return $result;
    }

    /**
     * @param int $userId
     *
     * @return int
     * @throws ArgumentException
     * @throws BasketUserInitializeException
     */
    public function getByUserId(int $userId): int
    {
        $result = Fuser::getIdByUserId($userId);

        if (!$result) {
            throw new BasketUserInitializeException('Failed to initialize basket user');
        }

        return $result;
    }
}
