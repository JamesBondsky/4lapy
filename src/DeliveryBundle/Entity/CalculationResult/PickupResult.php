<?php

namespace FourPaws\DeliveryBundle\Entity\CalculationResult;

use Bitrix\Main\ArgumentException;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\DeliveryBundle\Exception\NotFoundException;
use FourPaws\StoreBundle\Exception\NotFoundException as StoreNotFoundException;

class PickupResult extends BaseResult
{
    protected function doCalculatePeriod(): void
    {
        parent::doCalculatePeriod();
        $days = $this->deliveryDate->diff($this->getCurrentDate())->days;
        if ($days === 0) {
            $hours = $this->deliveryDate->diff($this->getCurrentDate())->h;
            $this->setPeriodFrom($hours >= 1 ? $hours : 1);
            $this->setPeriodType(self::PERIOD_TYPE_HOUR);
        }
    }

    /**
     * @return int
     * @throws ArgumentException
     * @throws ApplicationCreateException
     * @throws NotFoundException
     * @throws StoreNotFoundException
     */
    public function getPeriodTo(): int
    {
        return $this->getPeriodFrom();
    }
}
