<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\StoreBundle\Service;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\UserFieldTable;
use Bitrix\Main\Type\DateTime as BitrixDateTime;
use DateTime;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\AppBundle\Collection\UserFieldEnumCollection;
use FourPaws\AppBundle\Entity\UserFieldEnumValue;
use FourPaws\AppBundle\Service\UserFieldEnumService;
use FourPaws\BitrixOrmBundle\Exception\NotFoundRepository;
use FourPaws\BitrixOrmBundle\Orm\BitrixOrm;
use FourPaws\Enum\HlblockCode;
use FourPaws\Helpers\HighloadHelper;
use FourPaws\StoreBundle\Collection\ScheduleResultCollection;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Entity\DeliverySchedule;
use FourPaws\SapBundle\Service\DeliverySchedule\DeliveryScheduleService as SapDeliveryScheduleService;
use FourPaws\StoreBundle\Entity\ScheduleResult;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Exception\NotFoundException;
use FourPaws\StoreBundle\Repository\ScheduleResultRepository;
use Psr\Log\LoggerAwareInterface;
use Bitrix\Main\Application as BitrixApplication;

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
     * Тип поставки "нерегулярный"
     */
    public const FAST_DELIV = 'Z2';

    /**
     * Файл для хранения результатов
     */
    public const FILENAME = '/local/php_interface/resources/scheduleResults.txt';

    /**
     * @var DeliveryScheduleService
     */
    protected $deliveryScheduleService;

    /**
     * @var SapDeliveryScheduleService
     */
    protected $sapDeliveryScheduleService;

    /**
     * @var StoreService
     */
    protected $storeService;

    /**
     * @var ScheduleResultRepository
     */
    protected $repository;

    /**
     * @var UserFieldEnumCollection
     */
    protected $regular;

    /**
     * ScheduleResultService constructor.
     *
     * @param DeliveryScheduleService $deliveryScheduleService
     * @param DeliveryScheduleService $deliveryScheduleService
     * @param StoreService $storeService
     * @param BitrixOrm $bitrixOrm
     * @throws NotFoundRepository
     */
    public function __construct(
        DeliveryScheduleService $deliveryScheduleService,
        SapDeliveryScheduleService $sapDeliveryScheduleService,
        StoreService $storeService,
        BitrixOrm $bitrixOrm
    )
    {
        $this->deliveryScheduleService = $deliveryScheduleService;
        $this->sapDeliveryScheduleService = $sapDeliveryScheduleService;
        $this->storeService = $storeService;
        $this->repository = $bitrixOrm->getD7Repository(ScheduleResult::class);
    }

    /**
     * @param ScheduleResultCollection $results
     * @param DateTime $dateDelete
     * @return int[]
     * @throws NotFoundException
     */
    public function updateResults(ScheduleResultCollection $results, DateTime $dateDelete): array
    {
        $deleted = 0;
        $created = 0;
        $senders = $this->getSenders($results);
        /** @var Store $sender */
        foreach ($senders as $sender) {
            BitrixApplication::getConnection()->queryExecute("SELECT CURRENT_TIMESTAMP");
            /** @var ScheduleResult $item */
            foreach ($this->findResultsBySender($sender)->filterByDateActive($dateDelete) as $item) {
                $this->deleteResult($item);
                $deleted++;
            }

            /** @var ScheduleResult $item */
            foreach ($results->filterBySender($sender) as $item) {
                BitrixApplication::getConnection()->queryExecute("SELECT CURRENT_TIMESTAMP");
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
     * @param DateTime $dateDelete
     * @return int
     */
    public function deleteResultsForSender(Store $sender, DateTime $dateDelete, $regularId): int
    {
        $deleted = 0;
        foreach ($this->findResultsBySenderDateActiveAndRegular($sender, $dateDelete, $regularId) as $item) {
            BitrixApplication::getConnection()->queryExecute("SELECT CURRENT_TIMESTAMP");
            $this->deleteResult($item);
            $deleted++;
        }

        return $deleted;
    }

    /**
     * @param ScheduleResult $result
     * @return bool
     */
    public function updateResult(ScheduleResult $result): bool
    {
        return $this->repository->update($result);
    }

    /**
     * @param ScheduleResult $result
     * @return bool
     */
    public function createResult(ScheduleResult $result): bool
    {
        return $this->repository->create($result);
    }

    /**
     * @param ScheduleResult $result
     * @return bool
     */
    public function deleteResult(ScheduleResult $result): bool
    {
        return $this->repository->delete($result->getId());
    }

    /**
     * @param ScheduleResult $result
     * @return bool
     */
    public function deleteAllResults(): bool
    {
        return $this->repository->clearTable();
    }

    /**
     * @param int $id
     * @return ScheduleResult
     */
    public function findResultById(int $id): ScheduleResult
    {
        return $this->repository->find($id);
    }


    public function findAllResults(): ScheduleResultCollection
    {
        $result = null;
        try {
            $result = $this->repository->findAll();
        } catch (\Exception $e) {
            $this->log()->error(
                sprintf('failed to get schedule results: %s: %s', \get_class($e), $e->getMessage()),
                []
            );
        }

        return $result ?? new ScheduleResultCollection();
    }

    /**
     * @param Store $sender
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

    public function findResultsBySenderDateActiveAndRegular(Store $sender, DateTime $dateActive, int $regular): ScheduleResultCollection
    {
        $result = null;
        try {
            $scheduleResults = $this->repository->findBy([
                'UF_SENDER' => $sender->getXmlId(),
                '<=UF_DATE_ACTIVE' => $dateActive->format('d.m.Y'),
                'UF_REGULARITY' => $regular
            ]);
            $result = new ScheduleResultCollection($scheduleResults->toArray());
        } catch (\Exception $e) {
            $this->log()->error(
                sprintf('failed to get schedule results: %s: %s', \get_class($e), $e->getMessage()),
                ['sender' => $sender->getXmlId()]
            );
        }

        return $result ?? new ScheduleResultCollection();
    }

    /**
     * @param Store $sender
     * @param Store $receiver
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
                    'sender' => $sender->getXmlId(),
                    'receiver' => $receiver->getXmlId(),
                ]
            );
        }

        return $result ?? new ScheduleResultCollection();
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $_SERVER['DOCUMENT_ROOT'].self::FILENAME;
    }

    /**
     * @param ScheduleResult $scheduleResult
     * @return Store
     * @throws NotFoundException
     */
    public function getReceiver(ScheduleResult $scheduleResult): Store
    {
        return $this->storeService->getStoreByXmlId($scheduleResult->getReceiverCode());
    }

    /**
     * @param ScheduleResult $scheduleResult
     * @return Store
     * @throws NotFoundException
     */
    public function getSender(ScheduleResult $scheduleResult): Store
    {
        return $this->storeService->getStoreByXmlId($scheduleResult->getSenderCode());
    }

    /**
     * @param ScheduleResult $scheduleResult
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
     * Возвращает значения списка регулярность
     *
     * @param $xmlId
     * @return UserFieldEnumValue|null
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws \Bitrix\Main\LoaderException
     */
    public function getRegularityEnumByXmlId($xmlId)
    {
        $regular = $this->getRegularityEnumAll()->filter(function ($item) use ($xmlId) {
            return $item->getXmlId() == $xmlId;
        })->current();
        return $regular;
    }

    /**
     * @return UserFieldEnumCollection
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws \Bitrix\Main\LoaderException
     */
    public function getRegularityEnumAll()
    {
        if (null === $this->regular) {
            /** @var UserFieldEnumService $userFieldEnumService */
            $userFieldEnumService = Application::getInstance()->getContainer()->get('userfield_enum.service');
            $userFieldId = UserFieldTable::query()->setSelect(['ID', 'XML_ID'])->setFilter(
                [
                    'FIELD_NAME' => 'UF_REGULARITY',
                    'ENTITY_ID' => 'HLBLOCK_' . HighloadHelper::getIdByName(HlblockCode::DELIVERY_SCHEDULE_RESULT),
                ]
            )->exec()->fetch()['ID'];
            $this->regular = $userFieldEnumService->getEnumValueCollection($userFieldId);
        }
        return $this->regular;
    }

    /**
     * Возвращает ID значения в списке регулярности
     * в таблице результатов у регулярности другие id значений
     *
     * @param int $regularityId
     * @return mixed
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws \Bitrix\Main\LoaderException
     */
    protected function getRegularityIdByScheduleRegularityId(int $regularityId)
    {
        /** @var UserFieldEnumService $userFieldEnumService */
        $userFieldEnumService = Application::getInstance()->getContainer()->get('userfield_enum.service');
        $scheduleRegularity = $userFieldEnumService->getEnumValueEntity($regularityId);
        $regularity = $this->getRegularityEnumByXmlId($scheduleRegularity->getXmlId());
        return $regularity ? $regularity->getId() : null;
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
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws \Exception
     * @throws \RuntimeException
     */
    public function calculateForSender(
        Store $sender,
        \DateTime $date,
        int $regularityId,
        ?int $transitionCount = null
    ): ScheduleResultCollection
    {
        if (null === $transitionCount) {
            $transitionCount = self::MAX_TRANSITION_COUNT;
        }

        $receivers = $this->storeService->getStores(StoreService::TYPE_ALL_WITH_SUPPLIERS);
        //$receivers = [$this->storeService->getStoreByXmlId('R111')];

        $result = [];
        /** @var Store $receiver */
        foreach ($receivers as $receiver) {
            if ($sender->getXmlId() === $receiver->getXmlId()) {
                continue;
            }

            BitrixApplication::getConnection()->queryExecute("SELECT CURRENT_TIMESTAMP");

            $results = $this->calculateForSenderAndReceiver($sender, $receiver, $regularityId, $date, $transitionCount);
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
     * @throws \RuntimeException
     */
    public function calculateForSenderAndReceiver(
        Store $sender,
        Store $receiver,
        int $regularityId,
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
            21 => (clone $from)->setTime(20, 0, 0, 0),
            24 => (clone $from)->setTime(23, 0, 0, 0),
        ];

        return $this->doCalculateScheduleDate($sender, $receiver, $regularityId, $dates, $from, $maxTransitions);
    }/** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param Store $sender
     * @param Store $receiver
     * @param \DateTime[] $dates
     * @param int $maxTransitions
     * @param StoreCollection|null $route
     * @param DateTime $dateActive
     * @return ScheduleResultCollection
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws NotFoundException
     */
    protected function doCalculateScheduleDate(
        Store $sender,
        Store $receiver,
        int $regularityId,
        array $dates,
        DateTime $dateActive,
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
            if ($transitionCount > 0) {
                /** @var \DateTime $date */
                foreach ($from as $hour => $date) {
                    $date->setTime(9, 0, 0, 0);

                    /** время у стартовых дат нужно тоже изменить, чтобы корректно работал diff */
                    /** @var \DateTime $startDate */
                    $startDate = $startDates[$hour];
                    $startDate->setTime(9, 0, 0, 0);
                }
            }

            if (null === $route) {
                $route = new StoreCollection();
            }
            $route[$sender->getXmlId()] = $sender;

            /** @var DeliverySchedule $schedule */
            foreach ($this->deliveryScheduleService->findBySenderAndRegularity($sender, $regularityId) as $schedule) {
                // Если хотя бы одно из значений даты и времени не входит в дату действия расписания, то поставка была бы рассчитана неправильно
                if (end($from) < $schedule->getActiveFrom() || array_values($from)[0] > $schedule->getActiveTo()) {
                    continue;
                }

                /**
                 * Поиск даты поставки
                 */
                $nextDeliveries = [];
                foreach ($from as $hour => $date) {
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

                    $regularityIdResult = $this->getRegularityIdByScheduleRegularityId($regularityId);

                    $res = (new ScheduleResult())
                        ->setSenderCode($route->first()->getXmlId())
                        ->setReceiverCode($schedule->getReceiverCode())
                        ->setRouteCodes($route->getKeys())
                        ->setDateActive($dateActive->format(ScheduleResult::DATE_ACTIVE_FORMAT))
                        ->setRegularity($regularityIdResult);
                    /**
                     * @var int $hour
                     * @var \DateTime $date
                     */
                    foreach ($nextDeliveries as $hour => $date) {
                        $days = $date->diff($startDates[$hour])->days;

                        if ($days < 0) {
                            $this->log()->error(sprintf(
                                'delivery date can not be in the past'
                            ));
                            continue;
                        }

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
                    $regularityId,
                    $nextDeliveries,
                    $dateActive,
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

    public function clearOldResults()
    {
        $results = $this->repository->findBy(['<UF_DATE_ACTIVE' => new BitrixDateTime(date('d.m.y', strtotime('-3 days')))]);

    }
}
