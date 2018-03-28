<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\DeliveryBundle\Factory;

use Bitrix\Sale\Delivery\CalculationResult;
use FourPaws\DeliveryBundle\Collection\StockResultCollection;
use FourPaws\DeliveryBundle\Entity\CalculationResult\CalculationResultInterface;
use FourPaws\DeliveryBundle\Entity\CalculationResult\DeliveryResult;
use FourPaws\DeliveryBundle\Entity\CalculationResult\DpdDeliveryResult;
use FourPaws\DeliveryBundle\Entity\CalculationResult\DpdPickupResult;
use FourPaws\DeliveryBundle\Entity\CalculationResult\PickupResult;
use FourPaws\DeliveryBundle\Exception\UnknownDeliveryException;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use Bitrix\Sale\Delivery\Services\Base as BaseService;

class CalculationResultFactory
{
    /**
     * @var array
     */
    public static $dpdData = [];

    /**
     * @param CalculationResult $bitrixResult
     * @param BaseService $service
     * @return CalculationResultInterface
     * @throws UnknownDeliveryException
     */
    public static function fromBitrixResult(
        CalculationResult $bitrixResult,
        BaseService $service
    ): CalculationResultInterface {
        switch ($service->getCode()) {
            case DeliveryService::INNER_PICKUP_CODE:
                $result = new PickupResult();
                break;
            case DeliveryService::INNER_DELIVERY_CODE:
                $result = new DeliveryResult();
                break;
            case DeliveryService::DPD_PICKUP_CODE:
                $result = new DpdPickupResult();
                static::fillDpdData($result, $service->getCode());
                break;
            case DeliveryService::DPD_DELIVERY_CODE:
                $result = new DpdDeliveryResult();
                static::fillDpdData($result, $service->getCode());
                break;
            default:
                throw new UnknownDeliveryException(sprintf('Unknown delivery service %s', $service->getCode()));
        }

        static::fillDeliveryData($result, $bitrixResult);

        return $result;
    }

    /**
     * @param CalculationResultInterface $result
     * @param string $serviceCode
     */
    protected static function fillDpdData(CalculationResultInterface $result, string $serviceCode): void
    {
        if ($result instanceof DpdPickupResult) {
            $result->setTerminals(static::$dpdData[$serviceCode]['TERMINALS']);
        }

        $result->setDeliveryCode($serviceCode);
        $result->setInitialPeriod(static::$dpdData[$serviceCode]['DAYS_FROM']);
        $result->setPeriodTo(static::$dpdData[$serviceCode]['DAYS_TO']);
        if (static::$dpdData[$serviceCode]['STOCK_RESULT'] instanceof StockResultCollection) {
            $result->setStockResult(static::$dpdData[$serviceCode]['STOCK_RESULT']);
        }
        $result->setIntervals(static::$dpdData[$serviceCode]['INTERVALS']);
        $result->setDeliveryZone(static::$dpdData[$serviceCode]['DELIVERY_ZONE']);
        unset(static::$dpdData[$serviceCode]);
    }

    /**
     * @param CalculationResultInterface $result
     * @param CalculationResult $bitrixResult
     * @return CalculationResultInterface
     */
    protected static function fillDeliveryData(
        CalculationResultInterface $result,
        CalculationResult $bitrixResult
    ): CalculationResultInterface {
        $result->setDeliveryPrice($bitrixResult->getDeliveryPrice());
        $result->setExtraServicesPrice($bitrixResult->getExtraServicesPrice());
        $result->setDescription($bitrixResult->getDescription());
        $result->setPacksCount($bitrixResult->getPacksCount());

        if ($bitrixResult->isNextStep()) {
            $result->setAsNextStep();
        }

        $result->setTmpData($bitrixResult->getTmpData());
        $result->setData($bitrixResult->getData());

        $result->setPeriodDescription($bitrixResult->getPeriodDescription());
        $result->setPeriodFrom($bitrixResult->getPeriodFrom());
        $result->setPeriodType($bitrixResult->getPeriodType());
        $result->setPeriodTo($bitrixResult->getPeriodTo());

        if ($bitrixResult->getErrors()) {
            $result->addErrors($bitrixResult->getErrors());
        }

        if ($bitrixResult->getWarnings()) {
            $result->addWarnings($bitrixResult->getWarnings());
        }

        foreach ($bitrixResult->getData() as $key => $value) {
            switch ($key) {
                case 'FREE_FROM':
                    $result->setFreeFrom($value);
                    break;
                case 'STOCK_RESULT':
                    $result->setStockResult($value);
                    break;
                case 'INTERVALS':
                    $result->setIntervals($value);
                    break;
            }
        }

        return $result;
    }
}
