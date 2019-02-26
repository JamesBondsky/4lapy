<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\StoreBundle\Service;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\BitrixOrmBundle\Exception\NotFoundRepository;
use FourPaws\BitrixOrmBundle\Orm\BitrixOrm;
use FourPaws\StoreBundle\Collection\ScheduleResultCollection;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Entity\DeliverySchedule;
use FourPaws\StoreBundle\Entity\ScheduleResult;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Exception\NotFoundException;
use FourPaws\StoreBundle\Repository\ScheduleResultRepository;
use Psr\Log\LoggerAwareInterface;

class ScheduleResultService implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    public const MAX_TRANSITION_COUNT = 1;

    /**
     * Кол-во дней, добавляемых к дате доставки перед поиском графиков поставок
     */
    public const SCHEDULE_DATE_MODIFIER = 1;

    /**
     * Кол-во дней на обработку товара на РЦ
     */
    public const DC_PROCESSING_DATE_MODIFIER = 1;

    /**
     * @var DeliveryScheduleService
     */
    protected $deliveryScheduleService;

    /**
     * @var StoreService
     */
    protected $storeService;

    /**
     * @var ScheduleResultRepository
     */
    protected $repository;

    /**
     * ScheduleResultService constructor.
     *
     * @param DeliveryScheduleService $deliveryScheduleService
     * @param StoreService            $storeService
     * @param BitrixOrm               $bitrixOrm
     *
     * @throws NotFoundRepository
     */
    public function __construct(
        DeliveryScheduleService $deliveryScheduleService,
        StoreService $storeService,
        BitrixOrm $bitrixOrm
    )
    {
        $this->deliveryScheduleService = $deliveryScheduleService;
        $this->storeService = $storeService;
        $this->repository = $bitrixOrm->getD7Repository(ScheduleResult::class);
    }

    /**
     * @param ScheduleResultCollection $results
     *
     * @return int[]
     * @throws NotFoundException
     * @throws \RuntimeException
     */
    public function updateResults(ScheduleResultCollection $results): array
    {
        $deleted = 0;
        $created = 0;
        $senders = $this->getSenders($results);
        /** @var Store $sender */
        foreach ($senders as $sender) {
            /** @var ScheduleResult $item */
            foreach ($this->findResultsBySender($sender) as $item) {
                $this->deleteResult($item);
                $deleted++;
            }

            /** @var ScheduleResult $item */
            foreach ($results->filterBySender($sender) as $item) {
                $this->createResult($item);
                $created++;
            }
        }

        return [
            $created,
            $deleted,
        ];
    }

    /**
     * @param Store $sender
     *
     * @return int
     * @throws \RuntimeException
     */
    public function deleteResultsForSender(Store $sender): int
    {
        $deleted = 0;
        foreach ($this->findResultsBySender($sender) as $item) {
            $this->deleteResult($item);
            $deleted++;
        }

        return $deleted;
    }

    /**
     * @param ScheduleResult $result
     *
     * @return bool
     */
    public function updateResult(ScheduleResult $result): bool
    {
        return $this->repository->update($result);
    }

    /**
     * @param ScheduleResult $result
     *
     * @return bool
     */
    public function createResult(ScheduleResult $result): bool
    {
        return $this->repository->create($result);
    }

    /**
     * @param ScheduleResult $result
     *
     * @return bool
     */
    public function deleteResult(ScheduleResult $result): bool
    {
        return $this->repository->delete($result->getId());
    }

    /**
     * @param int $id
     *
     * @return ScheduleResult
     */
    public function findResultById(int $id): ScheduleResult
    {
        return $this->repository->find($id);
    }

    /**
     * @param Store $sender
     *
     * @return ScheduleResultCollection
     * @throws \RuntimeException
     */
    public function findResultsBySender(Store $sender): ScheduleResultCollection
    {
        $result = null;
        try {
            $result = $this->repository->findBySender($sender->getXmlId());
        } catch (\Exception $e) {
            $this->log()->error(
                sprintf('failed to get schedule results: %s: %s', \get_class($e), $e->getMessage()),
                ['sender' => $sender->getXmlId()]
            );
        }

        return $result ?? new ScheduleResultCollection();
    }

    /**
     * @param Store $receiver
     *
     * @return ScheduleResultCollection
     * @throws \RuntimeException
     */
    public function findResultsByReceiver(Store $receiver): ScheduleResultCollection
    {
        $result = null;
        try {
            $result = $this->repository->findByReceiver($receiver->getXmlId());
        } catch (\Exception $e) {
            $this->log()->error(
                sprintf('failed to get schedule results: %s: %s', \get_class($e), $e->getMessage()),
                ['receiver' => $receiver->getXmlId()]
            );
        }

        return $result ?? new ScheduleResultCollection();
    }

    /**
     * @param Store $sender
     * @param Store $receiver
     *
     * @return ScheduleResultCollection
     * @throws \RuntimeException
     */
    public function findResultsBySenderAndReceiver(Store $sender, Store $receiver): ScheduleResultCollection
    {
        $result = null;
        try {
            $result = $this->repository->findBySender($sender->getXmlId())->filterByReceiver($receiver);
        } catch (\Exception $e) {
            $this->log()->error(
                sprintf('failed to get schedule results: %s: %s', \get_class($e), $e->getMessage()),
                [
                    'sender'   => $sender->getXmlId(),
                    'receiver' => $receiver->getXmlId(),
                ]
            );
        }

        return $result ?? new ScheduleResultCollection();
    }

    /**
     * @param ScheduleResult $scheduleResult
     *
     * @return Store
     * @throws NotFoundException
     */
    public function getReceiver(ScheduleResult $scheduleResult): Store
    {
        return $this->storeService->getStoreByXmlId($scheduleResult->getReceiverCode());
    }

    /**
     * @param ScheduleResult $scheduleResult
     *
     * @return Store
     * @throws NotFoundException
     */
    public function getSender(ScheduleResult $scheduleResult): Store
    {
        return $this->storeService->getStoreByXmlId($scheduleResult->getSenderCode());
    }

    /**
     * @param ScheduleResult $scheduleResult
     *
     * @return Store
     * @throws NotFoundException
     */
    public function getLastSender(ScheduleResult $scheduleResult): Store
    {
        $keys = array_reverse($scheduleResult->getRouteCodes());

        return $this->storeService->getStoreByXmlId($keys[1]);
    }

    /**
     * @param ScheduleResultCollection $collection
     *
     * @return StoreCollection
     * @throws NotFoundException
     */
    public function getReceivers(ScheduleResultCollection $collection): StoreCollection
    {
        $result = new StoreCollection();
        /** @var ScheduleResult $item */
        foreach ($collection->getIterator() as $item) {
            $xmlId = $item->getReceiverCode();
            if (isset($result[$xmlId])) {
                continue;
            }

            $result[$xmlId] = $this->storeService->getStoreByXmlId($xmlId);
        }

        return $result;
    }

    /**
     * @param ScheduleResultCollection $collection
     *
     * @return StoreCollection
     * @throws NotFoundException
     */
    public function getSenders(ScheduleResultCollection $collection): StoreCollection
    {
        $result = new StoreCollection();
        /** @var ScheduleResult $item */
        foreach ($collection->getIterator() as $item) {
            $xmlId = $item->getSenderCode();
            if (isset($result[$xmlId])) {
                continue;
            }

            $result[$xmlId] = $this->storeService->getStoreByXmlId($xmlId);
        }

        return $result;
    }

    /**
     * @param \DateTime $date
     * @param int|null  $transitionCount
     *
     * @return ScheduleResultCollection
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws NotFoundException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws \Exception
     */
    public function calculateForAll(
        \DateTime $date,
        ?int $transitionCount = null
    ): ScheduleResultCollection
    {
        $result = [];
        $senders = $this->storeService->getStores(StoreService::TYPE_ALL_WITH_SUPPLIERS);

        /** @var Store $sender */
        foreach ($senders as $sender) {
            $results = $this->calculateForSender($sender, $date, $transitionCount);
            if (!$results->isEmpty()) {
                $result[] = $results->toArray();
            }
        }

        return !empty($result)
            ? new ScheduleResultCollection(\array_merge(...$result))
            : new ScheduleResultCollection();
    }

    /**
     * @param Store     $sender
     * @param \DateTime $date
     * @param int|null  $transitionCount
     *
     * @return ScheduleResultCollection
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws NotFoundException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws \Exception
     * @throws \RuntimeException
     */
    public function calculateForSender(
        Store $sender,
        \DateTime $date,
        ?int $transitionCount = null
    ): ScheduleResultCollection
    {
        if (null === $transitionCount) {
            $transitionCount = self::MAX_TRANSITION_COUNT;
        }

        $receivers = $this->storeService->getStores(StoreService::TYPE_ALL_WITH_SUPPLIERS);
        //$receivers = [$this->storeService->getStoreByXmlId('R060')];

        $result = [];
        /** @var Store $receiver */
        foreach ($receivers as $receiver) {
            if ($sender->getXmlId() === $receiver->getXmlId()) {
                continue;
            }

            $results = $this->calculateForSenderAndReceiver($sender, $receiver, $date, $transitionCount);
            if (!$results->isEmpty()) {
                $result[] = $results->toArray();
            }
        }

        return empty($result)
            ? new ScheduleResultCollection()
            : new ScheduleResultCollection(\array_merge(...$result));
    }/** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param Store          $sender
     * @param Store          $receiver
     * @param \DateTime|null $from
     * @param int            $maxTransitions
     *
     * @throws ArgumentException
     * @throws NotFoundException
     * @throws ApplicationCreateException
     * @return ScheduleResultCollection
     * @throws \RuntimeException
     */
    public function calculateForSenderAndReceiver(
        Store $sender,
        Store $receiver,
        ?\DateTime $from = null,
        int $maxTransitions = self::MAX_TRANSITION_COUNT
    ): ScheduleResultCollection
    {
        if (null === $from) {
            $from = new \DateTime();
        }
        $dates = [
            11 => (clone $from)->setTime(10, 0, 0, 0),
            13 => (clone $from)->setTime(12, 0, 0, 0),
            18 => (clone $from)->setTime(17, 0, 0, 0),
            24 => (clone $from)->setTime(23, 0, 0, 0),
        ];

        return $this->doCalculateScheduleDate($sender, $receiver, $dates, $maxTransitions);
    }/** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param Store                $sender
     * @param Store                $receiver
     * @param \DateTime[]          $dates
     * @param int                  $maxTransitions
     * @param StoreCollection|null $route
     *
     * @throws ArgumentException
     * @throws NotFoundException
     * @throws ApplicationCreateException
     * @return ScheduleResultCollection
     * @throws \RuntimeException
     */
    protected function doCalculateScheduleDate(
        Store $sender,
        Store $receiver,
        array $dates,
        int $maxTransitions = self::MAX_TRANSITION_COUNT,
        ?StoreCollection $route = null
    ): ScheduleResultCollection
    {
        static $transitionCount = 0;
        static $startDates;
        if ($transitionCount === 0) {
            foreach ($dates as $hour => $date) {
                $startDates[$hour] = clone $date;
            }
        }

        $result = new ScheduleResultCollection();

        if ($transitionCount < $maxTransitions) {
            /** @var \DateTime[] $from */
            $from = [];
            foreach ($dates as $hour => $date) {
                $from[$hour] = clone $date;
            }

            if ($sender->isSupplier()) {
                if ($transitionCount === 0) {
                    $maxTransitions++;
                }
            }

            /** На второй итерации маршрута товар готов к отгрузке уже в 9 утра */
            if($transitionCount > 0){
                /** @var \DateTime $date */
                foreach ($from as $hour => $date) {
                    $date->setTime(9, 0, 0, 0);

                    /** время у стартовых дат нужно тоже изменить, чтобы корректно работал diff */
                    /** @var \DateTime $startDate */
                    $startDate = $startDates[$hour];
                    $startDate->setTime(9, 0, 0, 0);
                }
            }

            //$modifier = 0;
            //$modifier += static::SCHEDULE_DATE_MODIFIER;

            if (null === $route) {
                $route = new StoreCollection();
            }
            $route[$sender->getXmlId()] = $sender;

            /** @var DeliverySchedule $schedule */
            foreach ($this->deliveryScheduleService->findBySender($sender) as $schedule) {
                /**
                 * Поиск даты поставки
                 */
                $nextDeliveries = [];
                foreach ($from as $hour => $date) {
                    /** Дата отгрузки со склада (работало раньше, когда не было расписаний у магазина) */
                    /*$shipmentDate = $this->storeService->getStoreShipmentDate($schedule->getReceiver(), $date);
                    if(!$sender->isSupplier()){
                        $shipmentDate->modify(sprintf('+%s days', $modifier));
                    }*/

                    /** Дата поставки на $receiver */
                    $nextDelivery = $schedule->getNextDelivery($date);

                    /** Если расписания для магазина нет - берём график отгрузок */
                    if (null === $nextDelivery && $schedule->getReceiver()->isShop()) {
                        $nextDelivery = $this->storeService->getStoreShipmentDate($schedule->getReceiver(), $date);
                    }

                    if (null === $nextDelivery) {
                        continue;
                    }

                    /** Время на сборку товара после прихода на склад */
                    if ($sender->isSupplier()) {
                        $nextDelivery->modify(sprintf('+%s days', static::DC_PROCESSING_DATE_MODIFIER));
                    }

                    $nextDeliveries[$hour] = $nextDelivery;
                }

                if (empty($nextDeliveries)) {
                    continue;
                }

                /**
                 * Найдена конечная точка
                 */
                if ($schedule->getReceiver()->getXmlId() === $receiver->getXmlId()) {
                    $route[$receiver->getXmlId()] = $receiver;

                    $res = (new ScheduleResult())
                        ->setSenderCode($route->first()->getXmlId())
                        ->setReceiverCode($schedule->getReceiverCode())
                        ->setRouteCodes($route->getKeys());

                    /**
                     * @var int       $hour
                     * @var \DateTime $date
                     */
                    foreach ($nextDeliveries as $hour => $date) {
                        $days = $date->diff($startDates[$hour])->days;
                        $setter = 'setDays' . $hour;
                        if (!method_exists($res, $setter)) {
                            $this->log()->error(sprintf(
                                'method %s not found in %s',
                                $setter,
                                \get_class($res)
                            ));
                            continue;
                        }

                        $res->$setter($days);
                    }

                    $result->add($res);
                    continue;
                }

                $transitionCount++;
                $results = $this->doCalculateScheduleDate(
                    $schedule->getReceiver(),
                    $receiver,
                    $nextDeliveries,
                    $maxTransitions,
                    $route
                );
                $transitionCount--;

                if (!$results->isEmpty()) {
                    $result = new ScheduleResultCollection(\array_merge($result->toArray(), $results->toArray()));
                }
            }
        }

        return $result;
    }
}
