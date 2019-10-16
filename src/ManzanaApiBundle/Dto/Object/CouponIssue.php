<?php


namespace FourPaws\ManzanaApiBundle\Dto\Object;


use JMS\Serializer\Annotation as Serializer;

class CouponIssue
{
    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("messageId")
     * @var string
     */
    protected $messageId;

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("ruleCode")
     * @var string
     */
    protected $ruleCode;

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("description")
     * @var string
     */
    protected $description = '';

    /**
     * @return string
     */
    public function getRuleCode(): string
    {
        return $this->ruleCode;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getMessageId(): string
    {
        return $this->messageId;
    }
}