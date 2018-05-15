<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\StoreBundle\Service;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use FourPaws\StoreBundle\Collection\DeliveryScheduleCollection;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Entity\DeliverySchedule;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Exception\NotFoundException;
use FourPaws\StoreBundle\Repository\DeliveryScheduleRepository;
use Psr\Log\LoggerAwareInterface;
use WebArch\BitrixCache\BitrixCache;

class DeliveryScheduleService implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    protected const TYPE_FIELD_CODE = 'UF_TYPE';

    /**
     * @var DeliveryScheduleRepository
     */
    protected $repository;

    public function __construct(DeliveryScheduleRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param Store $receiver
     * @param StoreCollection $senders
     *
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
                ->withId(__METHOD__ . $receiver->getXmlId())
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
            $this->log()->error(
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
     *
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
                ->withId(__METHOD__ . $sender->getXmlId())
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
            $this->log()->error(
                sprintf('failed to get delivery schedules: %s', $e->getMessage()),
                ['sender' => $sender->getXmlId()]
            );

            $schedules = new DeliveryScheduleCollection();
        }

        return $schedules;
    }

    /**
     * @param StoreCollection $stores
     *
     * @return DeliveryScheduleCollection
     */
    public function findByReceivers(StoreCollection $stores): DeliveryScheduleCollection
    {
        $results = [];
        /** @var Store $store */
        foreach ($stores as $store) {
            $results[] = $this->findByReceiver($store)->toArray();
        }

        if (!empty($results)) {
            $results = array_merge(...$results);
        }

        return new DeliveryScheduleCollection($results);
    }

    /**
     * @param StoreCollection $stores
     *
     * @return DeliveryScheduleCollection
     */
    public function findBySenders(StoreCollection $stores): DeliveryScheduleCollection
    {
        $results = [];
        /** @var Store $store */
        foreach ($stores as $store) {
            $results[] = $this->findBySender($store)->toArray();
        }

        if (!empty($results)) {
            $results = array_merge(...$results);
        }

        return new DeliveryScheduleCollection($results);
    }

    /**
     * @param string $xmlId
     *
     * @throws NotFoundException
     * @return DeliverySchedule
     */
    public function findByXmlId(string $xmlId): DeliverySchedule
    {
        return $this->repository->findByXmlId($xmlId);
    }

    /**
     * @param int $typeId
     *
     * @return null|string
     */
    public function getTypeCodeById(int $typeId): ?string
    {
        return $this->getTypes()[$typeId];
    }

    /**
     * @param string $code
     *
     * @return int|null
     */
    public function getTypeIdByCode(string $code): ?int
    {
        return array_flip($this->getTypes())[$code];
    }

    /**
     * @return array
     */
    public function getTypes(): array
    {
        $getTypes = function () {
            $result = [];

            if ($enums = (new \CUserFieldEnum())->GetList([], ['USER_FIELD_NAME' => static::TYPE_FIELD_CODE])) {
                while ($enum = $enums->Fetch()) {
                    $result[(int)$enum['ID']] = $enum['XML_ID'];
                }
            }

            return ['result' => $result];
        };

        $result = [];
        try {
            $result = (new BitrixCache())
                ->withId(__METHOD__)
                ->withTag('delivery_schedule')
                ->resultOf($getTypes)['result'];
        } catch (\Exception $e) {
            $this->log()->error(sprintf('failed to get enum list: %s', $e->getMessage()));
        }

        return $result;
    }
}
