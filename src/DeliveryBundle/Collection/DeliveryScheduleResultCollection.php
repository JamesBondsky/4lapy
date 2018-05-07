<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\DeliveryBundle\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\DeliveryBundle\Entity\DeliveryScheduleResult;
use FourPaws\Catalog\Model\Offer;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Exception\NotFoundException;

class DeliveryScheduleResultCollection extends ArrayCollection
{
    /**
     * @param \DateTime $from
     *
     * @throws NotFoundException
     * @return DeliveryScheduleResult|null
     */
    public function getFastest(\DateTime $from): ?DeliveryScheduleResultCollection
    {
        $collections = $this->splitByLastSenders();

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
            $price += $item->getOffer()->getPrice() * $item->getAmount();
        }

        return $price;
    }

    /**
     * @param Offer $offer
     *
     * @return DeliveryScheduleResultCollection
     */
    public function filterByOffer(Offer $offer): DeliveryScheduleResultCollection
    {
        return $this->filterByOfferId($offer->getId());
    }

    /**
     * @param int $offerId
     *
     * @return DeliveryScheduleResultCollection
     */
    public function filterByOfferId(int $offerId): DeliveryScheduleResultCollection
    {
        return $this->filter(function (DeliveryScheduleResult $result) use ($offerId) {
            return $result->getOffer()->getId() === $offerId;
        });
    }

    /**
     * @return array
     * @throws NotFoundException
     */
    protected function splitByLastSenders(): array
    {
        $result = [];
        /** @var DeliveryScheduleResult $item */
        foreach ($this->getIterator() as $item) {
            $lastSender = $item->getScheduleResult()->getLastSender();

            if (null === $result[$lastSender->getXmlId()]) {
                $result[$lastSender->getXmlId()] = new static();
            }

            $result[$lastSender->getXmlId()]->add($item);
        }

        return $result;
    }
}
