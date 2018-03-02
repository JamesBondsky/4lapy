<?php

namespace FourPaws\DeliveryBundle\Service;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use FourPaws\DeliveryBundle\Collection\IntervalCollection;
use FourPaws\DeliveryBundle\Collection\IntervalRuleCollection;
use FourPaws\DeliveryBundle\Entity\CalculationResult\BaseResult;
use FourPaws\DeliveryBundle\Entity\Interval;
use FourPaws\DeliveryBundle\Entity\IntervalRule\AddDaysRule;
use FourPaws\DeliveryBundle\Entity\IntervalRule\BaseRule;
use FourPaws\DeliveryBundle\Entity\IntervalRule\TimeRuleInterface;
use FourPaws\DeliveryBundle\Exception\NotFoundException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class IntervalService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * IntervalService constructor.
     */
    public function __construct()
    {
        $this->setLogger(LoggerFactory::create('IntervalService'));
    }

    /**
     * @param string $type
     * @param array $data
     *
     * @return BaseRule
     * @throws NotFoundException
     */
    public function createRule(string $type, array $data): BaseRule
    {
        switch ($type) {
            case BaseRule::TYPE_ADD_DAYS:
                return (new AddDaysRule())->setTo($data['TO'] ?? 0)
                                          ->setFrom($data['FROM'] ?? 0)
                                          ->setValue($data['VALUE'] ?? 0);
        }

        throw new NotFoundException(sprintf('Rule type %s not found', $type));
    }

    /**
     * @param string $type
     * @param array $data
     *
     * @return IntervalRuleCollection
     */
    public function createRules(string $type, array $data): IntervalRuleCollection
    {
        $result = new IntervalRuleCollection();
        foreach ($data as $item) {
            try {
                $result->add($this->createRule($type, $item));
            } catch (NotFoundException $e) {
                $this->logger->error(sprintf('unknown rule type %s', $type));
            }
        }

        return $result;
    }

    /**
     * @param BaseResult $delivery
     * @param IntervalCollection $intervals
     */
    public function getFirstInterval(BaseResult $delivery, IntervalCollection $intervals): Interval
    {
        $result = null;

        $min = null;
        /** @var Interval $interval */
        foreach ($intervals as $i => $interval) {
            $tmpDelivery = clone $delivery;
            $tmpDelivery->setSelectedInterval($interval);

            if ((null === $min) || $min > $tmpDelivery->getDeliveryDate()->getTimestamp()) {
                $result = $interval;
            }
        }

        if (!$result instanceof Interval) {
            throw new NotFoundException('Не найдено подходящих интервалов');
        }

        return $result;
    }

    /**
     * @param BaseResult $delivery
     * @param int $dateIndex
     *
     * @return IntervalCollection
     */
    public function getIntervalsByDate(BaseResult $delivery, int $dateIndex): IntervalCollection
    {
        $result = new IntervalCollection();

        if ($dateIndex > (int)$delivery->getPeriodTo() - 1) {
            return $result;
        }

        if (!$delivery->getStockResult()->getDelayed()->isEmpty()) {
            return $result;
        }

        $deliveryDate = clone $delivery->getDeliveryDate();
        if ($dateIndex > 0) {
            $deliveryDate->modify(sprintf('+%s days', $dateIndex));
        }

        /** @var Interval $interval */
        foreach ($delivery->getIntervals() as $interval) {
            $tmpDelivery = clone $delivery;
            $tmpDelivery->setSelectedInterval($interval);

            if ($tmpDelivery->getDeliveryDate() <= $deliveryDate) {
                $result->add($interval);
            }
        }

        return $result;
    }
}
