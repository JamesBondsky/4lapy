<?php

namespace FourPaws\DeliveryBundle\Entity\CalculationResult;

use Bitrix\Main\ArgumentException;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\DeliveryBundle\Entity\IntervalRule\BaseRule;
use FourPaws\DeliveryBundle\Entity\IntervalRule\TimeRuleInterface;
use FourPaws\DeliveryBundle\Exception\NotFoundException;
use FourPaws\DeliveryBundle\Service\IntervalService;
use FourPaws\StoreBundle\Exception\NotFoundException as StoreNotFoundException;

class DeliveryResult extends BaseResult
{
    /**
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws NotFoundException
     * @throws StoreNotFoundException
     */
    public function doCalculateDeliveryDate(): void
    {
        parent::doCalculateDeliveryDate();

        /** @var IntervalService $intervalService */
        $intervalService = Application::getInstance()->getContainer()->get(IntervalService::class);

        if ($this->stockResult && !$this->stockResult->getDelayed()->isEmpty()) {
            return;
        }

        /**
         * Если интервал не выбран, подбираем наиболее подходящий (с минимальной датой доставки)
         */
        if (null === $this->selectedInterval) {
            try {
                $this->selectedInterval = $intervalService->getFirstInterval(
                    $this,
                    $this->getIntervals()
                );
            } catch (NotFoundException $e) {
                return;
            }
        }

        if ($this->getIntervals()->isEmpty()) {
            return;
        }

        /**
         * Расчет даты доставки с учетом правил интервалов
         */
        $interval = $this->getSelectedInterval();
        /** @var BaseRule $rule */
        foreach ($interval->getRules() as $rule) {
            if (!$rule instanceof TimeRuleInterface) {
                continue;
            }

            $rule->apply($this);
        }
    }

    /**
     * @return int
     * @throws NotFoundException
     * @throws ArgumentException
     * @throws ApplicationCreateException
     * @throws StoreNotFoundException
     */
    public function getPeriodTo(): int
    {
        return $this->getPeriodFrom() + 10;
    }
}
