<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\DeliveryBundle\Collection;

use Bitrix\Main\LoaderException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\DeliveryBundle\Entity\DeliveryScheduleResult;
use FourPaws\Catalog\Model\Offer;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Exception\NotFoundException;

class DeliveryScheduleResultCollection extends ArrayCollection
{
    /**
     * @return DeliveryScheduleResult|null
     */
    public function getFastest(): ?DeliveryScheduleResultCollection
    {
        $collections = $this->splitByLastSenders();

        usort(
            $collections,
            function (DeliveryScheduleResultCollection $collection1, DeliveryScheduleResultCollection $collection2) {
                $price1 = $collection1->getPrice();
                $price2 = $collection2->getPrice();
                if ($price1 !== $price2) {
                    return $price2 <=> $price1;
                }

                $date1 = $collection1->getDays();
                $date2 = $collection1->getDays();
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
     * @return int
     */
    public function getDays(): int
    {
        $days = [0];

        /** @var DeliveryScheduleResult $item */
        foreach ($this->getIterator() as $item) {
            $days[] = $item->getScheduleResult()->getDays();
        }

        return max($days);
    }

    /**
     * @return float
     * @throws LoaderException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
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
        return $this->filter(function (DeliveryScheduleResult $result) use ($offer) {
            return $result->getOffer()->getId() === $offer->getId();
        });
    }

    protected function splitByLastSenders(): array
    {
        $getLastSender = function (DeliveryScheduleResult $item): ?Store {
            $route = $item->getScheduleResult()->getRoute();
            $keys = array_reverse($route->getKeys());
            return $route->get($keys[1]);
        };

        $result = [];
        /** @var DeliveryScheduleResult $item */
        foreach ($this->getIterator() as $item) {
            $lastSender = $getLastSender($item);
            if (!$lastSender) {
                continue;
            }
            if (null === $result[$lastSender->getXmlId()]) {
                $result[$lastSender->getXmlId()] = new DeliveryScheduleResultCollection();
            }

            $result[$lastSender->getXmlId()]->add($item);
        }

        return $result;
    }
}
