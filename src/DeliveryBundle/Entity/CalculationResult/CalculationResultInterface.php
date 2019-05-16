<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\DeliveryBundle\Entity\CalculationResult;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\EntityError;
use Bitrix\Main\Entity\FieldError;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\DeliveryBundle\Collection\StockResultCollection;
use FourPaws\DeliveryBundle\Entity\DeliveryScheduleResult;
use FourPaws\DeliveryBundle\Exception\NotFoundException;
use FourPaws\DeliveryBundle\Collection\DeliveryScheduleResultCollection;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Exception\NotFoundException as StoreNotFoundException;

/**
 * Interface CalculationResultInterface
 * @package FourPaws\DeliveryBundle\Entity\CalculationResult
 */
interface CalculationResultInterface
{
    /**
     * @return \DateTime
     */
    public function getCurrentDate(): \DateTime;

    /**
     * @param \DateTime $currentDate
     *
     * @return CalculationResultInterface
     */
    public function setCurrentDate(\DateTime $currentDate): CalculationResultInterface;

    /**
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws NotFoundException
     * @throws StoreNotFoundException
     * @return \DateTime
     */
    public function getDeliveryDate(): \DateTime;


    public function setDeliveryDate(\DateTime $date): CalculationResultInterface;

    /**
     * @return int
     */
    public function getDeliveryId(): int;

    /**
     * @param int $deliveryId
     *
     * @return CalculationResultInterface
     */
    public function setDeliveryId(int $deliveryId): CalculationResultInterface;

    /**
     * @return string
     */
    public function getDeliveryCode(): string;

    /**
     * @param string $deliveryCode
     *
     * @return CalculationResultInterface
     */
    public function setDeliveryCode(string $deliveryCode): CalculationResultInterface;

    /**
     * @return StockResultCollection
     */
    public function getStockResult(): StockResultCollection;

    /**
     * @return StockResultCollection
     */
    public function getFullStockResult(): StockResultCollection;

    /**
     * @param StockResultCollection $stockResult
     *
     * @return CalculationResultInterface
     */
    public function setStockResult(StockResultCollection $stockResult): CalculationResultInterface;

    /**
     * @return string
     */
    public function getDeliveryName(): string;

    /**
     * @param string $deliveryName
     *
     * @return CalculationResultInterface
     */
    public function setDeliveryName(string $deliveryName): CalculationResultInterface;

    /**
     * @return int
     */
    public function getFreeFrom(): int;

    /**
     * @param int $freeFrom
     *
     * @return CalculationResultInterface
     */
    public function setFreeFrom(int $freeFrom): CalculationResultInterface;

    /**
     * @return string
     */
    public function getDeliveryZone(): string;

    /**
     * @param string $deliveryZone
     *
     * @return CalculationResultInterface
     */
    public function setDeliveryZone(string $deliveryZone): CalculationResultInterface;

    /**
     * @throws NotFoundException
     * @return Store
     */
    public function getSelectedStore(): Store;

    /**
     * @param Store $selectedStore
     * @return CalculationResultInterface
     */
    public function setSelectedStore(Store $selectedStore): CalculationResultInterface;

    /**
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws NotFoundException
     * @throws StoreNotFoundException
     * @return int
     */
    public function getPeriodFrom(): int;

    /**
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws NotFoundException
     * @throws StoreNotFoundException
     * @return string
     */
    public function getPeriodType(): string;

    /**
     * @param bool $internalCall
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws NotFoundException
     * @throws StoreNotFoundException
     * @return bool
     */
    public function isSuccess($internalCall = false);

    /**
     * @return DeliveryScheduleResult|null
     */
    public function getShipmentResults(): ?DeliveryScheduleResultCollection;

    /**
     * @return float
     */
    public function getDeliveryPrice();

    /**
     * @param float $price
     */
    public function setDeliveryPrice($price);

    /**
     * @param float $price
     */
    public function setExtraServicesPrice($price);

    /**
     * @return float
     */
    public function getPrice();

    /**
     * @param string $description
     */
    public function setDescription($description);

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @param string $description
     */
    public function setPeriodDescription($description);

    /**
     * @return string
     */
    public function getPeriodDescription();

    public function setAsNextStep();

    /**
     * @return string
     */
    public function isNextStep();

    /**
     * @param int $count
     */
    public function setPacksCount($count);

    /**
     * @return int
     */
    public function getTmpData();

    /**
     * @param string $data
     */
    public function setTmpData($data);

    /**
     * @param int $periodFrom
     */
    public function setPeriodFrom($periodFrom);

    /**
     * @return int
     */
    public function getPeriodTo();

    /**
     * @param int $periodTo
     */
    public function setPeriodTo($periodTo);

    /**
     * @param int $periodType
     */
    public function setPeriodType($periodType);

    /**
     * @param $id
     * @return $this
     */
    public function setId($id);

    /**
     * @return string
     */
    public function getCurrency();

    /**
     * @param $currency
     * @return CalculationResultInterface
     */
    public function setCurrency($currency): CalculationResultInterface;

    /**
     * @param Error[] $errors
     * @return $this
     */
    public function addErrors(array $errors);

    /**
     * @param array $data
     * @return $this
     */
    public function setData(array $data);

    /**
     * @param array $data
     * @return mixed
     */
    public function addData(array $data);

    /**
     * @param Error $error
     */
    public function addWarning(Error $error);

    /**
     * @return bool
     */
    public function hasWarnings();

    /**
     * @return EntityError[]|FieldError[]
     */
    public function getErrors();

    /**
     * @return int
     */
    public function getId();

    /**
     * @return void
     */
    public function addWarnings(array $errors);

    /**
     * @param Error $error
     * @return void
     */
    public function addError(Error $error);

    /**
     * @return Error[]
     */
    public function getWarnings();

    /**
     * @return array
     */
    public function getErrorMessages();

    /**
     * @return array
     */
    public function getData();

    /**
     * @return array
     */
    public function getWarningMessages();

    /**
     * @return ErrorCollection
     */
    public function getErrorCollection();
}
