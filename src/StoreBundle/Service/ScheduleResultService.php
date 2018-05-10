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
use FourPaws\StoreBundle\Collection\ScheduleResultCollection;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Entity\DeliverySchedule;
use FourPaws\StoreBundle\Entity\ScheduleResult;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Exception\BitrixRuntimeException;
use FourPaws\StoreBundle\Exception\ConstraintDefinitionException;
use FourPaws\StoreBundle\Exception\InvalidIdentifierException;
use FourPaws\StoreBundle\Exception\NotFoundException;
use FourPaws\StoreBundle\Exception\ValidationException;
use FourPaws\StoreBundle\Repository\ScheduleResultRepository;
use Psr\Log\LoggerAwareInterface;
use WebArch\BitrixCache\BitrixCache;

class ScheduleResultService implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    public const MAX_TRANSITION_COUNT = 1;

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
     * @param DeliveryScheduleService  $deliveryScheduleService
     * @param StoreService             $storeService
     * @param ScheduleResultRepository $repository
     */
    public function __construct(
        DeliveryScheduleService $deliveryScheduleService,
        StoreService $storeService,
        ScheduleResultRepository $repository
    )
    {
        $this->deliveryScheduleService = $deliveryScheduleService;
        $this->storeService = $storeService;
        $this->repository = $repository;
    }

    /**
     * @param ScheduleResultCollection $results
     *
     * @throws ArgumentException
     * @throws ConstraintDefinitionException
     * @throws InvalidIdentifierException
     * @throws NotFoundException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws BitrixRuntimeException
     * @throws ValidationException
     * @return int[]
     */
    public function updateResults(ScheduleResultCollection $results): array
    {
        $deleted = 0;
        $created = 0;
        $senders = $results->getSenders();
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

        return [$created, $deleted];
    }

    /**
     * @param Store $sender
     *
     * @throws ArgumentException
     * @throws BitrixRuntimeException
     * @throws ConstraintDefinitionException
     * @throws InvalidIdentifierException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @return int
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
     * @throws \Exception
     * @throws BitrixRuntimeException
     * @throws ConstraintDefinitionException
     * @throws InvalidIdentifierException
     * @throws ValidationException
     */
    public function updateResult(ScheduleResult $result): bool
    {
        return $this->repository->update($result);
    }

    /**
     * @param ScheduleResult $result
     *
     * @return bool
     * @throws BitrixRuntimeException
     * @throws ValidationException
     */
    public function createResult(ScheduleResult $result)
    {
        return $this->repository->create($result);
    }

    /**
     * @param ScheduleResult $result
     *
     * @return bool
     * @throws BitrixRuntimeException
     * @throws ConstraintDefinitionException
     * @throws InvalidIdentifierException
     */
    public function deleteResult(ScheduleResult $result)
    {
        return $this->repository->delete($result->getId());
    }

    /**
     * @param int $id
     *
     * @throws ArgumentException
     * @throws ConstraintDefinitionException
     * @throws InvalidIdentifierException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws NotFoundException
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
     */
    public function findResultsBySender(Store $sender): ScheduleResultCollection
    {
        $getResults = function () use ($sender) {
            return ['result' => $this->repository->findBySender($sender)];
        };

        $result = null;
        try {
            $result = (new BitrixCache())->withId(__METHOD__ . $sender->getXmlId())
                          ->withTag('catalog:store:schedule:results')
                          ->resultOf($getResults)['result'];
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
     */
    public function findResultsByReceiver(Store $receiver): ScheduleResultCollection
    {
        $getResults = function () use ($receiver) {
            return ['result' => $this->repository->findByReceiver($receiver)];
        };

        $result = null;
        try {
            $result = (new BitrixCache())->withId(__METHOD__ . $receiver->getXmlId())
                          ->withTag('catalog:store:schedule:results')
                          ->resultOf($getResults)['result'];
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
     */
    public function findResultsBySenderAndReceiver(Store $sender, Store $receiver): ScheduleResultCollection
    {
        $getResults = function () use ($sender, $receiver) {
            return ['result' => $this->repository->findBySenderAndReceiver($sender, $receiver)];
        };

        $result = null;
        try {
            $result = (new BitrixCache())->withId(__METHOD__ . $sender->getXmlId() . '_' . $receiver->getXmlId())
                          ->withTag('catalog:store:schedule:results')
                          ->resultOf($getResults)['result'];
        } catch (\Exception $e) {
            $this->log()->error(
                sprintf('failed to get schedule results: %s: %s', \get_class($e), $e->getMessage()),
                ['receiver' => $receiver->getXmlId()]
            );
        }

        return $result ?? new ScheduleResultCollection();
    }

    /**
     * @param \DateTime $date
     * @param int|null  $transitionCount
     *
     * @throws ArgumentException
     * @throws NotFoundException
     * @throws ApplicationCreateException
     * @return ScheduleResultCollection
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
            11 => (clone $from)->setTime(10, 0, 0),
            13 => (clone $from)->setTime(12, 0, 0),
            18 => (clone $from)->setTime(17, 0, 0),
            24 => (clone $from)->setTime(23, 0, 0),
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
            $from = [];
            foreach ($dates as $hour => $date) {
                $from[$hour] = clone $date;
            }

            $modifier = 0;
            if ($sender->isSupplier()) {
                if ($transitionCount === 0) {
                    $maxTransitions++;
                }

                /**
                 * Для товаров под заказ добавляем два дня к дате доставки
                 */
                $modifier = 2;
            }

            /**
             * Добавляем один день к дате доставки
             */
            $modifier++;

            foreach ($from as $date) {
                $date->modify(sprintf('+%s days', $modifier));
            }

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
                    /**
                     * Дата отгрузки со склада
                     */
                    $shipmentDate = $schedule->getReceiver()->getShipmentDate($date);

                    $nextDelivery = $schedule->getNextDelivery($shipmentDate);

                    if (null !== $nextDelivery) {
                        $nextDeliveries[$hour] = $nextDelivery;
                    }
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
                        ->setSender($route->first())
                        ->setReceiver($schedule->getReceiver())
                        ->setRoute($route);

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
