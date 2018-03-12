<?php

namespace FourPaws\DeliveryBundle\Entity\CalculationResult;

use FourPaws\App\Application;
use FourPaws\DeliveryBundle\Entity\IntervalRule\BaseRule;
use FourPaws\DeliveryBundle\Entity\IntervalRule\TimeRuleInterface;
use FourPaws\DeliveryBundle\Exception\NotFoundException;
use FourPaws\DeliveryBundle\Service\IntervalService;

class DeliveryResult extends BaseResult
{
    public function doCalculateDeliveryDate(): void
    {
        parent::doCalculateDeliveryDate();

        /** @var IntervalService $intervalService */
        $intervalService = Application::getInstance()->getContainer()->get(IntervalService::class);

        if (!$this->getStockResult()->getDelayed()->isEmpty()) {
            /* @todo для зоны 2 должен в этом случае добавляться срок поставки в базовый магазин */
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

        $currentDate = new \DateTime();

        if ($this->deliveryDate->format('z') !== $currentDate->format('z')) {
            return;
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
}
