<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\DeliveryBundle\Entity;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class DpdLocation
{
    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("ID")
     * @Serializer\Groups(groups={"read","update","delete"})
     * @Assert\NotBlank(groups={"read","update","delete"})
     * @Assert\GreaterThanOrEqual(value="1",groups={"read","update","delete"})
     * @Assert\Blank(groups={"create"})
     */
    public $id = 0;

    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("CITY_ID")
     * @Serializer\Groups(groups={"create", "read","update","delete"})
     * @Assert\NotBlank(groups={"create", "read","update","delete"})
     */
    public $dpdId = 0;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("CITY_CODE")
     * @Serializer\Groups(groups={"create", "read","update","delete"})
     * @Assert\NotBlank(groups={"create", "read","update","delete"})
     */
    public $kladr = '';

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("CITY_NAME")
     * @Serializer\Groups(groups={"create", "read","update","delete"})
     * @Assert\NotBlank(groups={"create", "read","update","delete"})
     */
    public $name = '';

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("REGION_CODE")
     */
    public $regionCode = '';

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("REGION_NAME")
     */
    public $regionName = '';

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("COUNTRY_NAME")
     */
    public $countryName = '';

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("COUNTRY_CODE")
     */
    public $countryCode = '';

    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("LOCATION_ID")
     * @Serializer\Groups(groups={"create", "read","update","delete"})
     * @Assert\NotBlank(groups={"create", "read","update","delete"})
     */
    public $locationId = 0;

    /**
     * @var bool
     * @Serializer\Type("bitrix_bool")
     * @Serializer\SerializedName("IS_CASH_PAY")
     * @Serializer\Groups(groups={"create", "read","update","delete"})
     * @Assert\NotBlank(groups={"create", "read","update","delete"})
     */
    public $isCashPay = false;

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