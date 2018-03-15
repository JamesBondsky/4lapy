<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\StoreBundle\Service;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use FourPaws\StoreBundle\Collection\DeliveryScheduleCollection;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Entity\DeliverySchedule;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Repository\DeliveryScheduleRepository;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use WebArch\BitrixCache\BitrixCache;

class DeliveryScheduleService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var DeliveryScheduleRepository
     */
    protected $repository;

    public function __construct(DeliveryScheduleRepository $repository)
    {
        $this->repository = $repository;
        $this->setLogger(LoggerFactory::create('DeliveryScheduleService'));
    }

    /**
     * @param Store $receiver
     * @param StoreCollection $senders
     * @return DeliveryScheduleCollection
     */
    public function findByReceiver(Store $receiver, StoreCollection $senders = null): DeliveryScheduleCollection
    {
        $getSchedules = function () use ($receiver) {
            return ['result' => $this->repository->findByReceiver($receiver)];
        };

        try {
            /** @var DeliveryScheduleCollection $schedules */
            $schedules = (new BitrixCache())
                ->withId(__METHOD__)
                ->withTag('delivery_schedule')
                ->resultOf($getSchedules)['result'];
            if ($senders && !$senders->isEmpty()) {
                $schedules = $schedules->filterBySenders($senders);
                /** @var DeliverySchedule $item */
                foreach ($schedules as $item) {
                    $item->setReceiver($receiver);
                    if (isset($senders[$item->getSenderCode()])) {
                        $item->setSender($senders[$item->getSenderCode()]);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->error(
                sprintf('failed to get delivery schedules: %s', $e->getMessage()),
                ['receiver' => $receiver->getXmlId()]
            );

            $schedules = new DeliveryScheduleCollection();
        }

        return $schedules;
    }

    /**
     * @param Store $sender
     * @param null|StoreCollection $receivers
     * @return DeliveryScheduleCollection
     */
    public function findBySender(Store $sender, StoreCollection $receivers = null): DeliveryScheduleCollection
    {
        $getSchedules = function () use ($sender) {
            return ['result' => $this->repository->findBySender($sender)];
        };

        try {
            /** @var DeliveryScheduleCollection $schedules */
            $schedules = (new BitrixCache())
                ->withId(__METHOD__)
                ->withTag('delivery_schedule')
                ->resultOf($getSchedules)['result'];
            if ($receivers && !$receivers->isEmpty()) {
                $schedules = $schedules->filterByReceivers($receivers);
                /** @var DeliverySchedule $item */
                foreach ($schedules as $item) {
                    $item->setSender($sender);
                    if (isset($receivers[$item->getReceiverCode()])) {
                        $item->setReceiver($receivers[$item->getReceiverCode()]);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->error(
                sprintf('failed to get delivery schedules: %s', $e->getMessage()),
                ['sender' => $sender->getXmlId()]
            );

            $schedules = new DeliveryScheduleCollection();
        }

        return $schedules;
    }

    /**
     * @param string $xmlId
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     * @return DeliverySchedule
     */
    public function findByXmlId(string $xmlId): DeliverySchedule
    {
        return $this->repository->findByXmlId($xmlId);
    }
}
