<?php

namespace FourPaws\SaleBundle\Repository\OrderPropertyEnum;

class ComWayRepository extends BaseRepository
{
    const CODE_SMS = '01';

    const CODE_PHONE = '02';

    public function getPropertyCode(): string
    {
        return 'COM_WAY';
    }

    public function getAvailableValueCodes(): array
    {
        return [
            self::CODE_SMS,
            self::CODE_PHONE
        ];
    }
}
