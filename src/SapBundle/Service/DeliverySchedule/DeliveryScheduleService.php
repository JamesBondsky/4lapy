<?php

namespace FourPaws\SapBundle\Service\DeliverySchedule;

use FourPaws\SapBundle\Dto\In\DeliverySchedule\DeliverySchedule;
use FourPaws\SapBundle\Dto\In\DeliverySchedule\DeliverySchedules;
use FourPaws\StoreBundle\Repository\DeliveryScheduleRepository;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * Class DeliveryScheduleService
 *
 * @package FourPaws\SapBundle\Service\DeliverySchedule
 */
class DeliveryScheduleService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private $repository;

    public function __construct(DeliveryScheduleRepository $repository)
    {
        $this->repository = $repository;
    }

    public function find($item)
    {
        $this->repository->findBy();
    }

    /**
     * @param DeliverySchedules $deliverySchedules
     */
    public function processSchedules(DeliverySchedules $deliverySchedules)
    {
        foreach ($deliverySchedules->getSchedules() as $schedule) {
            $this->processSchedule($schedule);
        }
        dump($deliverySchedules);
        die;
    }

    public function processSchedule(DeliverySchedule $schedule)
    {

    }
}
