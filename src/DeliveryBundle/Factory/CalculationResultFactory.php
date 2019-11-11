<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\DeliveryBundle\Factory;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Error;
use Bitrix\Sale\Delivery\CalculationResult;
use Bitrix\Sale\Shipment;
use FourPaws\DeliveryBundle\Collection\IntervalCollection;
use FourPaws\DeliveryBundle\Collection\StockResultCollection;
use FourPaws\DeliveryBundle\Entity\CalculationResult\CalculationResultInterface;
use FourPaws\DeliveryBundle\Entity\CalculationResult\DeliveryResult;
use FourPaws\DeliveryBundle\Entity\CalculationResult\DeliveryResultInterface;
use FourPaws\DeliveryBundle\Entity\CalculationResult\DobrolapDeliveryResult;
use FourPaws\DeliveryBundle\Entity\CalculationResult\DostavistaDeliveryResult;
use FourPaws\DeliveryBundle\Entity\CalculationResult\DpdDeliveryResult;
use FourPaws\DeliveryBundle\Entity\CalculationResult\DpdPickupResult;
use FourPaws\DeliveryBundle\Entity\CalculationResult\ExpressDeliveryResult;
use FourPaws\DeliveryBundle\Entity\CalculationResult\PickupResult;
use FourPaws\DeliveryBundle\Exception\DeliveryInitializeException;
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
     * @param BaseService       $service
     * @param Shipment          $shipment
     *
     * @return CalculationResultInterface
     * @throws UnknownDeliveryException
     * @throws DeliveryInitializeException
     * @throws ArgumentNullException
     */
    public static function fromBitrixResult(
        CalculationResult $bitrixResult,
        BaseService $service,
        Shipment $shipment
    ): CalculationResultInterface
    {
        switch ($service->getCode()) {
            case DeliveryService::INNER_PICKUP_CODE:
                $result = new PickupResult();
                break;
            case DeliveryService::INNER_DELIVERY_CODE:
                $result = new DeliveryResult();
                break;
            case DeliveryService::DPD_PICKUP_CODE:
                $result = new DpdPickupResult();
                static::fillDpdData($result, $service->getCode(), $shipment);
                break;
            case DeliveryService::DPD_DELIVERY_CODE:
                $result = new DpdDeliveryResult();
                static::fillDpdData($result, $service->getCode(), $shipment);
                break;
            case DeliveryService::DELIVERY_DOSTAVISTA_CODE:
                $result = new DostavistaDeliveryResult();
                break;
            case DeliveryService::DOBROLAP_DELIVERY_CODE:
                $result = new DobrolapDeliveryResult();
                break;
            case DeliveryService::EXPRESS_DELIVERY:
                $result = new ExpressDeliveryResult();
                break;
            default:
                throw new UnknownDeliveryException(sprintf('Unknown delivery service %s', $service->getCode()));
        }

        switch ($service->getCode()) {
            case DeliveryService::DELIVERY_DOSTAVISTA_CODE:
                static::fillDeliveryDostavistaData($result, $bitrixResult);
                break;
            case DeliveryService::DOBROLAP_DELIVERY_CODE:
                static::fillDeliveryDobrolapData($result, $bitrixResult);
                break;
            default:
                static::fillDeliveryData($result, $bitrixResult);
        }

        return $result;
    }

    /**
     * @param array $order
     *
     * @return string
     */
    public static function getDpdCacheKey(array $order): string
    {
        $result = ['LOCATION_TO' => $order['LOCATION_TO']];

        $itemData = [];
        /** @var array $items */
        $items = $order['ITEMS'];
        foreach ($items as $item) {
            $key = $item['ID'] . '_' . $item['PRODUCT_ID'];
            $itemData[$key] = (int)$item['QUANTITY'];
        }
        $result['ITEMS'] = $itemData;

        return json_encode($result);
    }

    /**
     * @param Shipment $shipment
     *
     * @return string
     * @throws ArgumentNullException
     */
    public static function getDpdCacheKeyByShipment(Shipment $shipment): string
    {
        $order = \CSaleDelivery::convertOrderNewToOld($shipment);

        return static::getDpdCacheKey($order);
    }

    /**
     * @param CalculationResultInterface $result
     * @param string                     $serviceCode
     * @param Shipment                   $shipment
     *
     * @throws DeliveryInitializeException
     * @throws ArgumentNullException
     */
    protected static function fillDpdData(
        CalculationResultInterface $result,
        string $serviceCode,
        Shipment $shipment
    ): void
    {
        $cacheKey = static::getDpdCacheKeyByShipment($shipment);
        $dpdData = static::$dpdData[$cacheKey][$serviceCode];

        if (null === $dpdData) {
            $result->addError(new Error('Ошибка инициализации доставки'));
            throw new DeliveryInitializeException(sprintf('failed to find dpd data, cacheKey: %s', $cacheKey));
        }

        if ($result instanceof DpdPickupResult) {
            $result->setTerminals($dpdData['TERMINALS']);
        }

        $result->setDeliveryCode($serviceCode);
        $result->setInitialPeriod($dpdData['DAYS_FROM']);
        $result->setPeriodTo($dpdData['DAYS_TO']);
        if ($dpdData['STOCK_RESULT'] instanceof StockResultCollection) {
            $result->setStockResult($dpdData['STOCK_RESULT']);
        }

        if ($result instanceof DeliveryResultInterface &&
            $dpdData['INTERVALS'] instanceof IntervalCollection
        ) {
            $result->setIntervals($dpdData['INTERVALS']);
        }

        if ($result instanceof DeliveryResultInterface) {
            $result->setWeekDays([
                1,
                2,
                3,
                4,
                5,
                6,
                7,
            ]);
        }

        $result->setDeliveryZone($dpdData['DELIVERY_ZONE']);
    }

    /**
     * @param CalculationResultInterface $result
     * @param CalculationResult          $bitrixResult
     *
     * @return CalculationResultInterface
     */
    protected static function fillDeliveryData(
        CalculationResultInterface $result,
        CalculationResult $bitrixResult
    ): CalculationResultInterface
    {
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
                    if ($result instanceof DeliveryResultInterface) {
                        $result->setIntervals($value);
                    }
                    break;
                case 'WEEK_DAYS':
                    if ($result instanceof DeliveryResultInterface) {
                        $result->setWeekDays($value);
                    }
            }
        }

        return $result;
    }

    /**
     * @param CalculationResultInterface $result
     * @param CalculationResult          $bitrixResult
     *
     * @return CalculationResultInterface
     */
    protected static function fillDeliveryDostavistaData(
        CalculationResultInterface $result,
        CalculationResult $bitrixResult
    ): CalculationResultInterface
    {
        $result->setDeliveryPrice($bitrixResult->getDeliveryPrice());
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
                case 'FREE_PRICE_FROM':
                    $result->setFreeFrom($value);
                    break;
                case 'STOCK_RESULT':
                    $result->setStockResult($value);
                    break;
            }
        }

        return $result;
    }

    /**
     * @param CalculationResultInterface $result
     * @param CalculationResult          $bitrixResult
     *
     * @return CalculationResultInterface
     */
    protected static function fillDeliveryDobrolapData(
        CalculationResultInterface $result,
        CalculationResult $bitrixResult
    ): CalculationResultInterface
    {
        $result->setDeliveryPrice($bitrixResult->getDeliveryPrice());
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
                case 'STOCK_RESULT':
                    $result->setStockResult($value);
                    break;
            }
        }

        return $result;
    }
}
