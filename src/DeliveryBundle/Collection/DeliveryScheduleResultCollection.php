<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\DeliveryBundle\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\Catalog\Model\Offer;
use FourPaws\DeliveryBundle\Entity\DeliveryScheduleResult;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Exception\NotFoundException;

class DeliveryScheduleResultCollection extends ArrayCollection
{
    /**
     * @param \DateTime $from
     *
     * @throws NotFoundException
     * @return DeliveryScheduleResultCollection|null
     */
    public function getFastest(\DateTime $from): ?DeliveryScheduleResultCollection
    {
        $collections = $this->splitByLastSenders($from);

        usort(
            $collections,
            function (
                DeliveryScheduleResultCollection $collection1,
                DeliveryScheduleResultCollection $collection2
            ) use ($from) {
                $price1 = $collection1->getPrice();
                $price2 = $collection2->getPrice();
                if ($price1 !== $price2) {
                    return $price2 <=> $price1;
                }

                $date1 = $collection1->getDays($from);
                $date2 = $collection1->getDays($from);
                return $date1 <=> $date2;
            }
        );

        return empty($collections) ? null : reset($collections);
    }

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
     * @return StoreCollection
     * @throws NotFoundException
     */
    public function getReceivers(): StoreCollection
    {
        $result = new StoreCollection();
        /** @var DeliveryScheduleResult $item */
        foreach ($this->getIterator() as $item) {
            $result->add($item->getScheduleResult()->getReceiver());
        }

        return $result;
    }

    /**
     * @throws NotFoundException
     * @return StoreCollection
     */
    public function getSenders(): StoreCollection
    {
        $result = new StoreCollection();
        /** @var DeliveryScheduleResult $item */
        foreach ($this->getIterator() as $item) {
            $result->add($item->getScheduleResult()->getSender());
        }

        return $result;
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
     * @param \DateTime $from
     * @return array
     * @throws NotFoundException
     */
    protected function splitByLastSenders(\DateTime $from): array
    {
        /** @var DeliveryScheduleResultCollection[] $result */
        $results = [];

        /** @var DeliveryScheduleResult $item */
        foreach ($this->getIterator() as $item) {
            $xmlId = $item->getScheduleResult()->getLastSender()->getXmlId();
            /** @var DeliveryScheduleResultCollection $res */
            $result = $results[$xmlId] ?? new static();

            /** @var Offer $offer */
            foreach ($item->getStockResults()->getOffers() as $offer) {
                $offerId = $offer->getId();
                $resultByOffer = $result[$offerId];
                if (null === $resultByOffer) {
                    $result[$offerId] = $item;
                } else {
                    /** @var DeliveryScheduleResult $resultByOffer */
                    $days = $resultByOffer->getScheduleResult()->getDays($from);
                    if ($days > $item->getScheduleResult()->getDays($from)) {
                        $result[$offerId] = $item;
                    }
                }
            }

            $results[$xmlId] = $result;
        }

        return $results;
    }
}
