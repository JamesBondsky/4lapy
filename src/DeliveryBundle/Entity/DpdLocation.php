<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\DeliveryBundle\Entity;

class DpdLocation
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $dpdId;

    /**
     * @var string
     */
    public $kladr;

    /**
     * @var string
     */
    public $prefix;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $code;

    /**
     * @var string
     */
    public $areaName;

    /**
     * @var string
     */
    public $regionCode;

    /**
     * @var string
     */
    public $regionName;

    /**
     * @var string
     */
    public $countryName;

    /**
     * @var string
     */
    public $countryCode;

    /**
     * @var int
     */
    public $locationId;

    /**
     * @var bool
     */
    public $isCashPay;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return DpdLocation
     */
    public function setId(int $id): DpdLocation
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getDpdId(): int
    {
        return $this->dpdId;
    }

    /**
     * @param int $dpdId
     * @return DpdLocation
     */
    public function setDpdId(int $dpdId): DpdLocation
    {
        $this->dpdId = $dpdId;
        return $this;
    }

    /**
     * @return string
     */
    public function getKladr(): string
    {
        return $this->kladr;
    }

    /**
     * @param string $kladr
     * @return DpdLocation
     */
    public function setKladr(string $kladr): DpdLocation
    {
        $this->kladr = $kladr;
        return $this;
    }

    /**
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * @param string $prefix
     * @return DpdLocation
     */
    public function setPrefix(string $prefix): DpdLocation
    {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return DpdLocation
     */
    public function setName(string $name): DpdLocation
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return DpdLocation
     */
    public function setCode(string $code): DpdLocation
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return string
     */
    public function getAreaName(): string
    {
        return $this->areaName;
    }

    /**
     * @param string $areaName
     * @return DpdLocation
     */
    public function setAreaName(string $areaName): DpdLocation
    {
        $this->areaName = $areaName;
        return $this;
    }

    /**
     * @return string
     */
    public function getRegionCode(): string
    {
        return $this->regionCode;
    }

    /**
     * @param string $regionCode
     * @return DpdLocation
     */
    public function setRegionCode(string $regionCode): DpdLocation
    {
        $this->regionCode = $regionCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getRegionName(): string
    {
        return $this->regionName;
    }

    /**
     * @param string $regionName
     * @return DpdLocation
     */
    public function setRegionName(string $regionName): DpdLocation
    {
        $this->regionName = $regionName;
        return $this;
    }

    /**
     * @return string
     */
    public function getCountryName(): string
    {
        return $this->countryName;
    }

    /**
     * @param string $countryName
     * @return DpdLocation
     */
    public function setCountryName(string $countryName): DpdLocation
    {
        $this->countryName = $countryName;
        return $this;
    }

    /**
     * @return string
     */
    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    /**
     * @param string $countryCode
     * @return DpdLocation
     */
    public function setCountryCode(string $countryCode): DpdLocation
    {
        $this->countryCode = $countryCode;
        return $this;
    }

    /**
     * @return int
     */
    public function getLocationId(): int
    {
        return $this->locationId;
    }

    /**
     * @param int $locationId
     * @return DpdLocation
     */
    public function setLocationId(int $locationId): DpdLocation
    {
        $this->locationId = $locationId;
        return $this;
    }

    /**
     * @return bool
     */
    public function isCashPay(): bool
    {
        return $this->isCashPay;
    }

    /**
     * @param bool $isCashPay
     * @return DpdLocation
     */
    public function setIsCashPay(bool $isCashPay): DpdLocation
    {
        $this->isCashPay = $isCashPay;
        return $this;
    }
}