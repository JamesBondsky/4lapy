<?php

namespace FourPaws\DeliveryBundle\Entity\CalculationResult;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\SystemException;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Catalog\Model\Offer;
use FourPaws\DeliveryBundle\Collection\IntervalCollection;
use FourPaws\DeliveryBundle\Entity\Interval;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Exception\NotFoundException as StoreNotFoundException;

class DostavistaDeliveryResult extends BaseResult implements DeliveryResultInterface
{
    protected $freePriceFrom;
    protected $intervals;
    protected $offset;
    protected $days;
    protected $selectedInterval;

    /** @var Store */
    protected $nearShop;

    /**
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws StoreNotFoundException
     * @throws SystemException
     */
    public function getDeliveryDate(): \DateTime
    {
        $date = parent::getDeliveryDate();

        return $date;
    }

    /**
     * @return Store
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws StoreNotFoundException
     */
    public function getSelectedShop(): Store
    {
        return $this->getSelectedStore();
    }

    /**
     * @param Store $selectedStore
     *
     * @return PickupResultInterface
     */
    public function setSelectedShop(Store $selectedStore): PickupResultInterface
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->setSelectedStore($selectedStore);
    }

    /**
     * @param array $userCoords
     * @return Store
     */
    public function getNearShop(array $userCoords): Store
    {
        if (null === $this->nearShop) {
            $stores = $this->fullstockResult->getStores();
            if ($userCoords[0] != '' && $userCoords[1] != '') {
                $minDistance = null;
                /** @var Store $store */
                foreach ($stores as $store) {
                    if (!$store->isExpressStore()) {
                        continue;
                    }
                    $storeCoords = [$store->getLatitude(), $store->getLongitude()];
                    $distance = $this->LatLngDist($userCoords, $storeCoords);
                    if ($minDistance == null || $minDistance > $distance) {
                        $minDistance = $distance;
                        $this->nearShop = $store;
                    }
                }
            } else {
                $this->nearShop = $stores->first();
            }
        }

        return $this->nearShop;
    }

    /** @noinspection SenselessProxyMethodInspection */

    private function LatLngDist($p, $q) {
        $R = 6371; // Earth radius in km

        $dLat = (($q[0] - $p[0]) * pi() / 180);
        $dLon = (($q[1] - $p[1]) * pi() / 180);
        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos($p[0] * pi() / 180) * cos($q[0] * pi() / 180) *
            sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $R * $c;
    }

    /**
     * @param bool $internalCall
     * @return bool
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws StoreNotFoundException
     * @throws SystemException
     */
    public function isSuccess($internalCall = false)
    {
        return parent::isSuccess($internalCall);
    }

    /**
     * @param Offer $offer
     *
     * @return bool
     * @throws ApplicationCreateException
     */
    protected function checkIsDeliverable(Offer $offer): bool
    {
        return parent::checkIsDeliverable($offer) && $offer->getProduct()->isDeliveryAvailable();
    }

    /**
     * @param $freePriceFrom
     * @return DostavistaDeliveryResult
     */
    protected function setFreePriceFrom($freePriceFrom)
    {
        $this->freePriceFrom = $freePriceFrom;

        return $this;
    }

    /**
     * @return DostavistaDeliveryResult
     */
    protected function getFreePriceFrom()
    {
        return $this->freePriceFrom;
    }

    /**
     * @return IntervalCollection
     */
    public function getIntervals(): IntervalCollection
    {
        return new IntervalCollection();
    }

    /**
     * @param IntervalCollection $intervals
     *
     * @return DeliveryResultInterface
     */
    public function setIntervals(IntervalCollection $intervals): DeliveryResultInterface
    {
        $this->intervals = $intervals;

        return $this;
    }

    /**
     * @return int
     */
    public function getDateOffset(): int
    {
        return 0;
    }

    /**
     * @param int $offset
     *
     * @return DeliveryResultInterface
     */
    public function setDateOffset(int $offset): DeliveryResultInterface
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * @return int[]
     */
    public function getWeekDays(): array
    {
        return [];
    }

    /**
     * @param int[] $days
     *
     * @return DeliveryResultInterface
     */
    public function setWeekDays(array $days): DeliveryResultInterface
    {
        $this->days = $days;
        return $this;
    }

    /**
     * @return IntervalCollection
     */
    public function getAvailableIntervals(): IntervalCollection
    {
        return new IntervalCollection;
    }

    /**
     * @return Interval
     */
    public function getSelectedInterval(): ?Interval
    {
        return new Interval;
    }

    /**
     * @param Interval $selectedInterval
     *
     * @return DeliveryResultInterface
     */
    public function setSelectedInterval(Interval $selectedInterval): DeliveryResultInterface
    {
        $this->selectedInterval = $selectedInterval;

        return $this;
    }

    /**
     * @return int
     */
    public function getIntervalOffset(): int
    {
        return 0;
    }
}
