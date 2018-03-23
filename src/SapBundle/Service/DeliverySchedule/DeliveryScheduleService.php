<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Service\DeliverySchedule;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\SystemException;
use Exception;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Helpers\TaggedCacheHelper;
use FourPaws\SapBundle\Dto\In\DeliverySchedule\DeliverySchedule;
use FourPaws\SapBundle\Dto\In\DeliverySchedule\DeliverySchedules;
use FourPaws\SapBundle\Dto\In\DeliverySchedule\ManualDayItem;
use FourPaws\SapBundle\Dto\In\DeliverySchedule\WeekDayItem;
use FourPaws\SapBundle\Exception\NotFoundScheduleException;
use FourPaws\StoreBundle\Entity\DeliverySchedule as DeliveryScheduleEntity;
use FourPaws\StoreBundle\Exception\BitrixRuntimeException;
use FourPaws\StoreBundle\Exception\ConstraintDefinitionException;
use FourPaws\StoreBundle\Exception\InvalidIdentifierException;
use FourPaws\StoreBundle\Exception\ValidationException;
use FourPaws\StoreBundle\Repository\DeliveryScheduleRepository;
use FourPaws\StoreBundle\Service\DeliveryScheduleService as BaseService;
use JMS\Serializer\Serializer;
use Psr\Log\LoggerAwareInterface;
use RuntimeException;

/**
 * Class DeliveryScheduleService
 *
 * @package FourPaws\SapBundle\Service\DeliverySchedule
 */
class DeliveryScheduleService implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    const CACHE_TAG = 'delivery_schedule';
    /**
     * @var DeliveryScheduleRepository
     */
    private $repository;
    /**
     * @var Serializer
     */
    private $serializer;
    /**
     * @var BaseService
     */
    private $baseService;

    /**
     * DeliveryScheduleService constructor.
     *
     * @param DeliveryScheduleRepository $repository
     * @param BaseService $baseService
     * @param Serializer $serializer
     */
    public function __construct(DeliveryScheduleRepository $repository, BaseService $baseService, Serializer $serializer)
    {
        $this->repository = $repository;
        $this->serializer = $serializer;
        $this->baseService = $baseService;
    }

    /**
     * @param DeliverySchedule $schedule
     *
     * @throws NotFoundScheduleException
     * @return DeliveryScheduleEntity
     *
     */
    public function findSchedule(DeliverySchedule $schedule): DeliveryScheduleEntity
    {
        $scheduleEntity = $this->repository->findBy(['=UF_XML_ID' => $schedule->getXmlId()])->first();

        if (!$scheduleEntity) {
            throw new NotFoundScheduleException(
                \sprintf(
                    'Расписание с DN #%s не найдено',
                    $schedule->getXmlId()
                )
            );
        }

        return $scheduleEntity;
    }

    /**
     * @param DeliverySchedule $schedule
     *
     * @throws RuntimeException
     * @throws Exception
     * @throws ApplicationCreateException
     */
    public function processSchedule(DeliverySchedule $schedule): void
    {
        if ($this->tryDeleteSchedule($schedule)) {
            return;
        }

        $entity = $this->transformDtoToEntity($schedule);

        try {
            $existsEntityId = $this->findSchedule($schedule)->getId();
            $entity->setId($existsEntityId);
            $this->tryUpdateSchedule($entity);
        } catch (NotFoundScheduleException $e) {
            $this->tryAddSchedule($entity);
        }
    }

    /**
     * @param DeliverySchedule $schedule
     *
     * @throws RuntimeException
     * @throws Exception
     * @return bool
     *
     */
    public function tryDeleteSchedule(DeliverySchedule $schedule): bool
    {
        if ($schedule->isDeleted()) {
            try {
                $scheduleId = $this->findSchedule($schedule)->getId();

                $this->repository->delete($scheduleId);

                $this->log()->info(
                    \sprintf(
                        'Расписание #%s удалено',
                        $scheduleId
                    )
                );
            } catch (NotFoundScheduleException | ConstraintDefinitionException | InvalidIdentifierException | BitrixRuntimeException $e) {
                $this->log()->error($e->getMessage());
            }

            return true;
        }

        return false;
    }

    /**
     * @param DeliverySchedules $deliverySchedules
     *
     * @throws Exception
     * @throws RuntimeException
     * @throws SystemException
     * @throws ApplicationCreateException
     */
    public function processSchedules(DeliverySchedules $deliverySchedules)
    {
        foreach ($deliverySchedules->getSchedules() as $schedule) {
            $this->processSchedule($schedule);
        }

        $this->clearCache();
    }

    public function clearCache(): void
    {
        TaggedCacheHelper::clearManagedCache([
            self::CACHE_TAG,
        ]);
    }

    /**
     * @param DeliverySchedule $schedule
     *
     * @return DeliveryScheduleEntity
     *
     * @throws ApplicationCreateException
     */
    private function transformDtoToEntity(DeliverySchedule $schedule): DeliveryScheduleEntity
    {
        $entity = new DeliveryScheduleEntity();

        $entity->setXmlId($schedule->getXmlId())
            ->setSenderCode($schedule->getSenderCode())
            ->setReceiverCode($schedule->getRecipientCode())
            ->setType($this->baseService->getTypeIdByCode($schedule->getScheduleType()))
            ->setName(
                \sprintf(
                    'График поставки из %s в %s',
                    $schedule->getSenderCode(),
                    $schedule->getRecipientCode()
                )
            );

        if ($schedule->getDateFrom()) {
            $entity->setActiveFrom($schedule->getDateFrom());
        }

        if ($schedule->getDateTo()) {
            $entity->setActiveTo($schedule->getDateTo());
        }

        $weekDays = $schedule->getWeekDays();

        if ($weekDays->count()) {
            $days = $this->serializer->toArray($weekDays->first());
            $days = \array_filter(\array_values($days), function ($k, $v) {
                return $k && $v;
            }, \ARRAY_FILTER_USE_BOTH);

            $entity->setDaysOfWeek($days);

            $weekNumbers = $weekDays->map(function ($weekNumber) {
                /**
                 * @var $weekNumber WeekDayItem
                 */
                return $weekNumber->getNumWeek();
            })->toArray();

            $entity->setWeekNumbers($weekNumbers);
        }

        $manualDays = $schedule->getManualDays();

        if ($manualDays->count()) {
            /**
             * @var $manualDay ManualDayItem
             */
            $manualDay = $manualDays->first();

            $entity->setDeliveryNumber($manualDay->getNum());
            $entity->setDeliveryDates($manualDays->map(function ($manualDay) {
                /**
                 * @var $manualDay ManualDayItem
                 */
                return $manualDay->getDate();
            })->toArray());
        }

        return $entity;
    }

    /**
     * @param DeliveryScheduleEntity $entity
     *
     * @throws RuntimeException
     * @throws Exception
     */
    private function tryAddSchedule(DeliveryScheduleEntity $entity)
    {
        try {
            $this->repository->create($entity);

            $this->log()->info(
                \sprintf(
                    'Расписание #%s добавлено',
                    $entity->getXmlId()
                )
            );
        } catch (BitrixRuntimeException | ValidationException $e) {
            $this->log()->error($e->getMessage());
        }
    }

    /**
     * @param DeliveryScheduleEntity $entity
     *
     * @throws RuntimeException
     * @throws Exception
     */
    private function tryUpdateSchedule(DeliveryScheduleEntity $entity)
    {
        try {
            $this->repository->update($entity);

            $this->log()->info(
                \sprintf(
                    'Расписание #%s обновлено',
                    $entity->getXmlId()
                )
            );
        } catch (InvalidIdentifierException | ConstraintDefinitionException | BitrixRuntimeException | ValidationException $e) {
            $this->log()->error($e->getMessage());
        }
    }
}
