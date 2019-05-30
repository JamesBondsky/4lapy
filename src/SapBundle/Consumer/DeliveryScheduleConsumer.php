<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Consumer;

use FourPaws\SapBundle\Dto\In\DeliverySchedule\DeliverySchedules;
use FourPaws\SapBundle\Service\DeliverySchedule\DeliveryScheduleService;
use RuntimeException;

/**
 * Class DeliveryScheduleConsumer
 *
 * @package FourPaws\SapBundle\Consumer
 */
class DeliveryScheduleConsumer extends SapConsumerBase
{
    /**
     * @var DeliveryScheduleService
     */
    private $deliveryScheduleService;

    /**
     * DeliveryScheduleConsumer constructor.
     *
     * @param DeliveryScheduleService $deliveryScheduleService
     */
    public function __construct(DeliveryScheduleService $deliveryScheduleService)
    {
        $this->deliveryScheduleService = $deliveryScheduleService;
    }

    /**
     * @param $scheduleInfo
     *
     * @throws RuntimeException
     * @return bool
     */
    public function consume($scheduleInfo): bool
    {
        if (!$this->support($scheduleInfo)) {
            return false;
        }

        $this->log()->info('Импорт расписания погрузок');

        try {
            $success = true;

            $this->deliveryScheduleService->processSchedules($scheduleInfo);
        } catch (\Exception $e) {
            $success = false;

            $this->log()->critical(
                \sprintf(
                    'Ошибка импорта расписания погрузок: %s',
                    $e->getMessage()
                )
            );
        }

        return $success;
    }

    /**
     * @param $data
     *
     * @return bool
     */
    public function support($data): bool
    {
        return \is_object($data) && $data instanceof DeliverySchedules;
    }
}
