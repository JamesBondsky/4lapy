<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Dto\SberbankOrderInfo;

use JMS\Serializer\Annotation as Serializer;

class CardAuthInfo
{
    /**
     * @var string
     *
     * @Serializer\SerializedName("expiration")
     * @Serializer\Type("string")
     */
    protected $expiration = '';

    /**
     * @var string
     *
     * @Serializer\SerializedName("cardholderName")
     * @Serializer\Type("string")
     */
    protected $cardHolderName = '';

    /**
     * @var string
     *
     * @Serializer\SerializedName("approvalCode")
     * @Serializer\Type("string")
     */
    protected $approvalCode = '';

    /**
     * @var string
     *
     * @Serializer\SerializedName("pan")
     * @Serializer\Type("string")
     */
    protected $pan = '';

    /**
     * @return string
     */
    public function getExpiration(): string
    {
        return $this->expiration;
    }

    /**
     * @param string $expiration
     * @return CardAuthInfo
     */
    public function setExpiration(string $expiration): CardAuthInfo
    {
        $this->expiration = $expiration;

        return $this;
    }

    /**
     * @return string
     */
    public function getCardHolderName(): string
    {
        return $this->cardHolderName;
    }

    /**
     * @param string $cardHolderName
     * @return CardAuthInfo
     */
    public function setCardHolderName(string $cardHolderName): CardAuthInfo
    {
        $this->cardHolderName = $cardHolderName;

        return $this;
    }

    /**
     * @return string
     */
    public function getApprovalCode(): string
    {
        return $this->approvalCode;
    }

    /**
     * @param string $approvalCode
     * @return CardAuthInfo
     */
    public function setApprovalCode(string $approvalCode): CardAuthInfo
    {
        $this->approvalCode = $approvalCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getPan(): string
    {
        return $this->pan;
    }

    /**
     * @param string $pan
     * @return CardAuthInfo
     */
    public function setPan(string $pan): CardAuthInfo
    {
        $this->pan = $pan;

        return $this;
    }
}
