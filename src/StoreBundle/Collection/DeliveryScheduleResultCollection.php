<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\StoreBundle\Collection;


use Bitrix\Main\ArgumentException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use FourPaws\Catalog\Model\Offer;
use FourPaws\StoreBundle\Entity\DeliveryScheduleResult;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Exception\NotFoundException;

class DeliveryScheduleResultCollection extends BaseCollection
{
    /**
     * @return DeliveryScheduleResult|null
     * @throws ArgumentException
     * @throws NotFoundException
     */
    public function getFastest(): ?DeliveryScheduleResultCollection
    {
        $senders = $this->getSenders();
        $collections = [];
        /** @var Store $sender */
        foreach ($senders as $sender) {
            $collections[] = $this->filterBySender($sender);
        }

        usort(
            $collections,
            function (DeliveryScheduleResultCollection $collection1, DeliveryScheduleResultCollection $collection2) {
                $price1 = $collection1->getPrice();
                $price2 = $collection2->getPrice();
                if ($price1 !== $price2) {
                    return $price2 <=> $price1;
                }

                $date1 = $collection1->getDate();
                $date2 = $collection1->getDate();
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
            return $result->getSchedule()->getReceiverCode() === $store->getXmlId();
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
            return $result->getSchedule()->getSenderCode() === $store->getXmlId();
        });
    }

    /**
     * @return StoreCollection
     * @throws ArgumentException
     * @throws NotFoundException
     */
    public function getReceivers(): StoreCollection
    {
        $result = new StoreCollection();
        /** @var DeliveryScheduleResult $item */
        foreach ($this->getIterator() as $item) {
            $result->add($item->getSchedule()->getReceiver());
        }

        return $result;
    }

    /**
     * @throws ArgumentException
     * @throws NotFoundException
     * @return StoreCollection
     */
    public function getSenders(): StoreCollection
    {
        $result = new StoreCollection();
        /** @var DeliveryScheduleResult $item */
        foreach ($this->getIterator() as $item) {
            $result->add($item->getSchedule()->getSender());
        }

        return $result;
    }

    /**
     * @return \DateTime
     */
    public function getDate(): \DateTime
    {
        $date = new \DateTime();
        /** @var DeliveryScheduleResult $item */
        foreach ($this->getIterator() as $item) {
            $date = $item->getDate() > $date ? $item->getDate() : $date;
        }

        return $date;
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
}
