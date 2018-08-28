<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Dto\SberbankOrderInfo;

use JMS\Serializer\Annotation as Serializer;

class BankInfo
{
    /**
     * @var string
     *
     * @Serializer\SerializedName("bankName")
     * @Serializer\Type("string")
     */
    protected $bankName = '';

    /**
     * @var string
     *
     * @Serializer\SerializedName("bankCountryCode")
     * @Serializer\Type("string")
     */
    protected $bankCountryCode = '';

    /**
     * @var string
     *
     * @Serializer\SerializedName("bankCountryName")
     * @Serializer\Type("string")
     */
    protected $bankCountryName = '';

    /**
     * @return string
     */
    public function getBankName(): string
    {
        return $this->bankName;
    }

    /**
     * @param string $bankName
     * @return BankInfo
     */
    public function setBankName(string $bankName): BankInfo
    {
        $this->bankName = $bankName;

        return $this;
    }

    /**
     * @return string
     */
    public function getBankCountryCode(): string
    {
        return $this->bankCountryCode;
    }

    /**
     * @param string $bankCountryCode
     * @return BankInfo
     */
    public function setBankCountryCode(string $bankCountryCode): BankInfo
    {
        $this->bankCountryCode = $bankCountryCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getBankCountryName(): string
    {
        return $this->bankCountryName;
    }

    /**
     * @param string $bankCountryName
     * @return BankInfo
     */
    public function setBankCountryName(string $bankCountryName): BankInfo
    {
        $this->bankCountryName = $bankCountryName;

        return $this;
    }
}
