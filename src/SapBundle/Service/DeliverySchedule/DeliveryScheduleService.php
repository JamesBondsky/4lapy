<?php

namespace FourPaws\SapBundle\Service\DeliverySchedule;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\Application;
use Bitrix\Main\SystemException;
use Exception;
use FourPaws\SapBundle\Dto\In\DeliverySchedule\DeliverySchedule;
use FourPaws\SapBundle\Dto\In\DeliverySchedule\DeliverySchedules;
use FourPaws\SapBundle\Exception\NotFoundScheduleException;
use FourPaws\StoreBundle\Entity\DeliverySchedule as DeliveryScheduleEntity;
use FourPaws\StoreBundle\Exception\BitrixRuntimeException;
use FourPaws\StoreBundle\Exception\ConstraintDefinitionException;
use FourPaws\StoreBundle\Exception\InvalidIdentifierException;
use FourPaws\StoreBundle\Exception\ValidationException;
use FourPaws\StoreBundle\Repository\DeliveryScheduleRepository;
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
     * DeliveryScheduleService constructor.
     *
     * @param DeliveryScheduleRepository $repository
     */
    public function __construct(DeliveryScheduleRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param DeliverySchedule $schedule
     *
     * @return DeliveryScheduleEntity
     *
     * @throws NotFoundScheduleException
     */
    public function findSchedule(DeliverySchedule $schedule): DeliveryScheduleEntity
    {
        $scheduleEntity = $this->repository->findBy(['=XML_ID' => $schedule->getDocId()])->first();

        if (null === $schedule) {
            throw new NotFoundScheduleException(sprintf(
                'Расписание с DN #%s не найдено',
                $schedule->getDocId()
            ));
        }

        return $scheduleEntity;
    }

    /**
     * @param DeliverySchedule $schedule
     *
     * @throws RuntimeException
     * @throws Exception
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
     * @return bool
     *
     * @throws RuntimeException
     * @throws Exception
     */
    public function tryDeleteSchedule(DeliverySchedule $schedule): bool
    {
        if ($schedule->isDeleted()) {
            try {
                $scheduleId = $this->findSchedule($schedule)->getId();

                $this->repository->delete($scheduleId);
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
     */
    public function processSchedules(DeliverySchedules $deliverySchedules)
    {
        foreach ($deliverySchedules->getSchedules() as $schedule) {
            $this->processSchedule($schedule);
        }

        $this->clearCache();
    }

    /**
     * @throws SystemException
     */
    public function clearCache()
    {
        $cache = Application::getInstance()->getTaggedCache();

        $cache->clearByTag(self::CACHE_TAG);
    }

    /**
     * @param DeliverySchedule $schedule
     *
     * @return DeliveryScheduleEntity
     */
    private function transformDtoToEntity(DeliverySchedule $schedule): DeliveryScheduleEntity
    {
        $entity = new DeliveryScheduleEntity();

        $entity->setXmlId($schedule->getDocId())
            ->setSender($schedule->getSenderCode())
            ->setReceiver($schedule->getRecipientCode())
            ->setType($schedule->getScheduleType())
            ->setActive(true);

        if ($schedule->getDateFrom()) {
            $entity->setActiveFrom($schedule->getDateFrom());
        }

        if ($schedule->getDateTo()) {
            $entity->setActiveTo($schedule->getDateTo());
        }

        $weekDays = $schedule->getWeekDays();

        if ($weekDays->count()) {

        }

        $manualDays = $schedule->getManualDays();

        if ($manualDays->count()) {

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

            $this->log()->info(sprintf(
                'Расписание #%s добавлено',
                $entity->getXmlId()
            ));
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

            $this->log()->info(sprintf(
                'Расписание #%s обновлено',
                $entity->getXmlId()
            ));
        } catch (InvalidIdentifierException | ConstraintDefinitionException | BitrixRuntimeException | ValidationException $e) {
            $this->log()->error($e->getMessage());
        }
    }
}
