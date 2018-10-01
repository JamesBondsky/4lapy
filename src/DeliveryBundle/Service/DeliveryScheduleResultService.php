<?php

namespace FourPaws\DeliveryBundle\Service;

use FourPaws\Catalog\Model\Offer;
use FourPaws\DeliveryBundle\Collection\DeliveryScheduleResultCollection;
use FourPaws\DeliveryBundle\Entity\DeliveryScheduleResult;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Exception\NotFoundException;
use FourPaws\StoreBundle\Service\ScheduleResultService;

class DeliveryScheduleResultService
{
    /**
     * @var ScheduleResultService
     */
    protected $scheduleResultService;

    /**
     * DeliveryScheduleResultService constructor.
     *
     * @param ScheduleResultService $scheduleResultService
     */
    public function __construct(ScheduleResultService $scheduleResultService)
    {
        $this->scheduleResultService = $scheduleResultService;
    }

    /**
     * @param DeliveryScheduleResultCollection $collection
     * @param \DateTime                        $from
     *
     * @return DeliveryScheduleResultCollection|null
     * @throws NotFoundException
     */
    public function getFastest(DeliveryScheduleResultCollection $collection, \DateTime $from): ?DeliveryScheduleResultCollection
    {
        $collections = $this->splitByLastSenders($collection, $from);

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
     * @param DeliveryScheduleResultCollection $collection
     * @param \DateTime                        $from
     *
     * @return array
     * @throws NotFoundException
     */
    protected function splitByLastSenders(DeliveryScheduleResultCollection $collection, \DateTime $from): array
    {
        /** @var DeliveryScheduleResultCollection[] $result */
        $results = [];

        /** @var DeliveryScheduleResult $item */
        foreach ($collection->getIterator() as $item) {
            if (!$this->scheduleResultService->getReceiver($item->getScheduleResult())->isShop()) {
                /**
                 * Если поставка напрямую на нужный склад, то искать кратчайший маршрут не нужно
                 */
                $xmlId = $item->getScheduleResult()->getReceiverCode();
            } else {
                $xmlId = $this->scheduleResultService->getLastSender($item->getScheduleResult())->getXmlId();
            }

            /** @var DeliveryScheduleResultCollection $res */
            $result = $results[$xmlId] ?? new DeliveryScheduleResultCollection();

            /** @var Offer $offer */
            foreach ($item->getStockResults()->getOffers() as $offer) {
                $offerId = $offer->getId();
                $resultByOffer = $result[$offerId];
                if (null === $resultByOffer) {
                    $result[$offerId] = $item;
                } else {
                    if ($item->getAmountByOffer($offer) > $resultByOffer->getAmountByOffer($offer)) {
                        $result[$offerId] = $item;
                    } else {
                        /** @var DeliveryScheduleResult $resultByOffer */
                        $days = $resultByOffer->getScheduleResult()->getDays($from);
                        if ($days > $item->getScheduleResult()->getDays($from)) {
                            $result[$offerId] = $item;
                        }
                    }
                }
            }

            $results[$xmlId] = $result;
        }

        return $results;
    }
}
