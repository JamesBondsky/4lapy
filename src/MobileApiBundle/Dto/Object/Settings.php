<?php

namespace FourPaws\MobileApiBundle\Dto\Object;

use JMS\Serializer\Annotation as Serializer;

class Settings
{
    /**
     * @Serializer\Type("bool")
     * @Serializer\SerializedName("interview_messaging_enabled")
     * @var bool
     */
    protected $sendInterviewMsg = false;

    /**
     * @Serializer\Type("bool")
     * @Serializer\SerializedName("bonus_messaging_enabled")
     * @var bool
     */
    protected $sendBonusMsg = false;

    /**
     * @Serializer\Type("bool")
     * @Serializer\SerializedName("feedback_messaging_enabled")
     * @var bool
     */
    protected $sendFeedbackMsg = false;

    /**
     * @Serializer\Type("bool")
     * @Serializer\SerializedName("push_order_status")
     * @var bool
     */
    protected $sendOrderStatusMsg = false;

    /**
     * @Serializer\Type("bool")
     * @Serializer\SerializedName("push_news")
     * @var bool
     */
    protected $sendNewsMsg = false;

    /**
     * @Serializer\Type("bool")
     * @Serializer\SerializedName("push_account_change")
     * @var bool
     */
    protected $sendBonusChangeMsg = false;

    /**
     * @Serializer\Type("bool")
     * @Serializer\SerializedName("sms_messaging_enabled")
     * @var bool
     */
    protected $sendSmsMsg = false;

    /**
     * @Serializer\Type("bool")
     * @Serializer\SerializedName("email_messaging_enabled")
     * @var bool
     */
    protected $sendEmailMsg = false;

    /**
     * @Serializer\Type("bool")
     * @Serializer\SerializedName("gps_messaging_enabled")
     * @var bool
     */
    protected $gpsAllowed = false;

    /**
     * @return bool
     */
    public function isSendInterviewMsg(): bool
    {
        return $this->sendInterviewMsg;
    }

    /**
     * @param bool $sendInterviewMsg
     *
     * @return Settings
     */
    public function setSendInterviewMsg(bool $sendInterviewMsg): Settings
    {
        $this->sendInterviewMsg = $sendInterviewMsg;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSendBonusMsg(): bool
    {
        return $this->sendBonusMsg;
    }

    /**
     * @param bool $sendBonusMsg
     *
     * @return Settings
     */
    public function setSendBonusMsg(bool $sendBonusMsg): Settings
    {
        $this->sendBonusMsg = $sendBonusMsg;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSendFeedbackMsg(): bool
    {
        return $this->sendFeedbackMsg;
    }

    /**
     * @param bool $sendFeedbackMsg
     *
     * @return Settings
     */
    public function setSendFeedbackMsg(bool $sendFeedbackMsg): Settings
    {
        $this->sendFeedbackMsg = $sendFeedbackMsg;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSendOrderStatusMsg(): bool
    {
        return $this->sendOrderStatusMsg;
    }

    /**
     * @param bool $sendOrderStatusMsg
     *
     * @return Settings
     */
    public function setSendOrderStatusMsg(bool $sendOrderStatusMsg): Settings
    {
        $this->sendOrderStatusMsg = $sendOrderStatusMsg;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSendNewsMsg(): bool
    {
        return $this->sendNewsMsg;
    }

    /**
     * @param bool $sendNewsMsg
     *
     * @return Settings
     */
    public function setSendNewsMsg(bool $sendNewsMsg): Settings
    {
        $this->sendNewsMsg = $sendNewsMsg;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSendBonusChangeMsg(): bool
    {
        return $this->sendBonusChangeMsg;
    }

    /**
     * @param bool $sendBonusChangeMsg
     *
     * @return Settings
     */
    public function setSendBonusChangeMsg(bool $sendBonusChangeMsg): Settings
    {
        $this->sendBonusChangeMsg = $sendBonusChangeMsg;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSendSmsMsg(): bool
    {
        return $this->sendSmsMsg;
    }

    /**
     * @param bool $sendSmsMsg
     *
     * @return Settings
     */
    public function setSendSmsMsg(bool $sendSmsMsg): Settings
    {
        $this->sendSmsMsg = $sendSmsMsg;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSendEmailMsg(): bool
    {
        return $this->sendEmailMsg;
    }

    /**
     * @param bool $sendEmailMsg
     *
     * @return Settings
     */
    public function setSendEmailMsg(bool $sendEmailMsg): Settings
    {
        $this->sendEmailMsg = $sendEmailMsg;
        return $this;
    }

    /**
     * @return bool
     */
    public function isGpsAllowed(): bool
    {
        return $this->gpsAllowed;
    }

    /**
     * @param bool $gpsAllowed
     *
     * @return Settings
     */
    public function setGpsAllowed(bool $gpsAllowed): Settings
    {
        $this->gpsAllowed = $gpsAllowed;
        return $this;
    }

}