<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\StoreBundle\Service;

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

class ScheduleResultService
{
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
     * @param DeliveryScheduleService $deliveryScheduleService
     * @param StoreService $storeService
     * @param ScheduleResultRepository $repository
     */
    public function __construct(
        DeliveryScheduleService $deliveryScheduleService,
        StoreService $storeService,
        ScheduleResultRepository $repository
    ) {
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
     */
    public function updateResults(ScheduleResultCollection $results): void
    {
        $senders = $results->getSenders();
        /** @var Store $sender */
        foreach ($senders as $sender) {
            /** @var ScheduleResult $item */
            foreach ($this->findResultsBySender($sender) as $item) {
                $this->deleteResult($item);
            }

            /** @var ScheduleResult $item */
            foreach ($results->filterBySender($sender) as $item) {
                $this->createResult($item);
            }
        }
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
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function findResultsBySender(Store $sender): ScheduleResultCollection
    {
        return $this->repository->findBySender($sender);
    }

    /**
     * @param Store $receiver
     *
     * @return ScheduleResultCollection
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function findResultsByReceiver(Store $receiver): ScheduleResultCollection
    {
        return $this->repository->findByReceiver($receiver);
    }

    /**
     * @param Store $sender
     * @param Store $receiver
     *
     * @return ScheduleResultCollection
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function findResultsBySenderAndReceiver(Store $sender, Store $receiver): ScheduleResultCollection
    {
        return $this->repository->findBySenderAndReceiver($sender, $receiver);
    }

    /**
     * @param \DateTime $date
     * @param int|null $transitionCount
     *
     * @throws ArgumentException
     * @throws NotFoundException
     * @throws ApplicationCreateException
     * @return ScheduleResultCollection
     */
    public function calculateForAll(
        \DateTime $date,
        ?int $transitionCount = null
    ): ScheduleResultCollection {
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
     * @param Store $sender
     * @param \DateTime $date
     * @param int|null $transitionCount
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
    ) {
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
     * @param Store $sender
     * @param Store $receiver
     * @param \DateTime|null $from
     * @param int $maxTransitions
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
    ): ScheduleResultCollection {
        return $this->doCalculateScheduleDate($sender, $receiver, $from, $maxTransitions);
    }/** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param Store $sender
     * @param Store $receiver
     * @param \DateTime|null $from
     * @param int $maxTransitions
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
        ?\DateTime $from = null,
        int $maxTransitions = self::MAX_TRANSITION_COUNT,
        ?StoreCollection $route = null
    ): ScheduleResultCollection {
        $from = $from instanceof \DateTime ? clone $from : new \DateTime();

        static $transitionCount = 0;
        static $startDate;
        if ($transitionCount === 0) {
            $startDate = $from;
        }

        $result = new ScheduleResultCollection();

        if ($transitionCount < $maxTransitions) {
            $from = $from instanceof \DateTime ? clone $from : new \DateTime();

            if ($sender->isSupplier()) {
                if ($transitionCount === 0) {
                    $maxTransitions++;
                }
                /**
                 * Для товаров под заказ добавляем два дня к дате доставки
                 */
                $from->modify('+2 days');
            } else {
                /**
                 * Для обычных товаров добавляем один день к дате доставки
                 */
                $from->modify('+1 day');
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
                $nextDelivery = $schedule->getNextDelivery($from);
                if (null === $nextDelivery) {
                    continue;
                }

                /**
                 * Найдена конечная точка
                 */
                if ($schedule->getReceiver()->getXmlId() === $receiver->getXmlId()) {
                    $route[$receiver->getXmlId()] = $receiver;
                    $result->add((new ScheduleResult())->setDays($nextDelivery->diff($startDate)->days)
                        ->setSender($route->first())
                        ->setReceiver($schedule->getReceiver())
                        ->setRoute($route));
                    continue;
                }

                $transitionCount++;
                $results = $this->doCalculateScheduleDate(
                    $schedule->getReceiver(),
                    $receiver,
                    $nextDelivery,
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
