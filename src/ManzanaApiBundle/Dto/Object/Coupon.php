<?php


namespace FourPaws\ManzanaApiBundle\Dto\Object;


use DateTime;
use JMS\Serializer\Annotation as Serializer;

class Coupon
{
    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("messageId")
     * @var string
     */
    protected $messageId;

    /**
     * @Serializer\Type("string")
     * @var string
     */
    protected $couponId;

    /**
     * @Serializer\Type("string")
     * @var string
     */
    protected $promoCode;

    /**
     * @Serializer\Type("string")
     * @var string
     */
    protected $ruleCode;

    /**
     * @Serializer\Type("DateTime<'Y-m-d H:i:s'>")
     * @var string
     */
    protected $startDate;

    /**
     * @Serializer\Type("DateTime<'Y-m-d H:i:s'>")
     * @var string
     */
    protected $endDate;

    /**
     * @Serializer\Type("int")
     * @var int
     */
    protected $phone;

    /**
     * @return string
     */
    public function getCouponId(): string
    {
        return $this->couponId;
    }

    /**
     * @return string
     */
    public function getPromoCode(): string
    {
        return $this->promoCode;
    }

    /**
     * @return string
     */
    public function getRuleCode(): string
    {
        return $this->ruleCode;
    }

    /**
     * @return DateTime
     */
    public function getStartDate(): DateTime
    {
        return $this->startDate;
    }

    /**
     * @return DateTime
     */
    public function getEndDate(): DateTime
    {
        return $this->endDate;
    }

    /**
     * @return int
     */
    public function getPhone(): int
    {
        return $this->phone;
    }

    /**
     * @return string
     */
    public function getMessageId(): string
    {
        return $this->messageId;
    }
}