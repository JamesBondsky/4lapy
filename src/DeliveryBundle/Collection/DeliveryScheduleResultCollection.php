<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\DeliveryBundle\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\Catalog\Model\Offer;
use FourPaws\DeliveryBundle\Entity\DeliveryScheduleResult;
use FourPaws\StoreBundle\Entity\Store;

class DeliveryScheduleResultCollection extends ArrayCollection
{
    /**
     * @param Store $store
     *
     * @return DeliveryScheduleResultCollection
     */
    public function filterByReceiver(Store $store): DeliveryScheduleResultCollection
    {
        return $this->filter(function (DeliveryScheduleResult $result) use ($store) {
            return $result->getScheduleResult()->getReceiverCode() === $store->getXmlId();
        });
    }

    /**
     * @param Store $store
     *
     * @return DeliveryScheduleResultCollection
     */
    public function filterBySender(Store $store): DeliveryScheduleResultCollection
    {
        return $this->filter(function (DeliveryScheduleResult $result) use ($store) {
            return $result->getScheduleResult()->getSenderCode() === $store->getXmlId();
        });
    }

    /**
     * @param $from
     *
     * @return int
     */
    public function getDays(\DateTime $from): int
    {
        $days = [0];

        /** @var DeliveryScheduleResult $item */
        foreach ($this->getIterator() as $item) {
            $days[] = $item->getScheduleResult()->getDays($from);
        }

        return max($days);
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        $price = 0;
        /** @var DeliveryScheduleResult $item */
        foreach ($this->getIterator() as $item) {
            $price += $item->getPrice();
        }

        return $price;
    }

    /**
     * @param Offer $offer
     * @return int
     */
    public function getAmountByOffer(Offer $offer): int
    {
        $total = 0;
        /** @var DeliveryScheduleResult $item */
        foreach ($this->getIterator() as $item) {
            $total += $item->getAmountByOffer($offer);
        }

        return $total;
    }

    /**
     * @param Offer          $offer
     * @param \DateTime|null $for
     * @return DeliveryScheduleResult|null
     */
    public function getByOffer(Offer $offer, ?\DateTime $for = null): ?DeliveryScheduleResult
    {
        return $this->getByOfferId($offer->getId(), $for);
    }

    /**
     * @param int            $offerId
     * @param \DateTime|null $for
     * @return DeliveryScheduleResult|null
     */
    public function getByOfferId(int $offerId, ?\DateTime $for = null): ?DeliveryScheduleResult
    {
        $results = [];
        $for = $for ?: new \DateTime();
        /** @var DeliveryScheduleResult $item */
        foreach ($this->getIterator() as $item) {
            $hasOffer = !$item->getStockResults()->filterByOfferId($offerId)->isEmpty();
            if ($hasOffer) {
                $days = $item->getScheduleResult()->getDays($for);
                $results[$days] = $item;
            }
        }

        krsort($results);

        return !empty($results) ? reset($results) : null;
    }
}
