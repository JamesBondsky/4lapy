<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Dto\Object;

use FourPaws\UserBundle\Entity\User;
use JMS\Serializer\Annotation as Serializer;

class Settings
{
    /**
     * @Serializer\Type("bool")
     * @Serializer\SerializedName("interview_messaging_enabled")
     * @Serializer\SkipWhenEmpty()
     * @var null|bool
     */
    protected $sendInterviewMsg;

    /**
     * @Serializer\Type("bool")
     * @Serializer\SerializedName("bonus_messaging_enabled")
     * @Serializer\SkipWhenEmpty()
     * @var null|bool
     */
    protected $sendBonusMsg;

    /**
     * @Serializer\Type("bool")
     * @Serializer\SerializedName("feedback_messaging_enabled")
     * @Serializer\SkipWhenEmpty()
     * @var null|bool
     */
    protected $sendFeedbackMsg;

    /**
     * @Serializer\Type("bool")
     * @Serializer\SerializedName("push_order_status")
     * @Serializer\SkipWhenEmpty()
     * @var null|bool
     */
    protected $sendOrderStatusMsg;

    /**
     * @Serializer\Type("bool")
     * @Serializer\SerializedName("push_news")
     * @Serializer\SkipWhenEmpty()
     * @var null|bool
     */
    protected $sendNewsMsg;

    /**
     * @Serializer\Type("bool")
     * @Serializer\SerializedName("push_account_change")
     * @Serializer\SkipWhenEmpty()
     * @var null|bool
     */
    protected $sendBonusChangeMsg;

    /**
     * @Serializer\Type("bool")
     * @Serializer\SerializedName("sms_messaging_enabled")
     * @Serializer\SkipWhenEmpty()
     * @var null|bool
     */
    protected $sendSmsMsg;

    /**
     * @Serializer\Type("bool")
     * @Serializer\SerializedName("email_messaging_enabled")
     * @Serializer\SkipWhenEmpty()
     * @var null|bool
     */
    protected $sendEmailMsg;

    /**
     * @Serializer\Type("bool")
     * @Serializer\SerializedName("gps_messaging_enabled")
     * @Serializer\SkipWhenEmpty()
     * @var null|bool
     */
    protected $gpsAllowed;

    /**
     * @param User $user
     * @return static
     */
    public static function createFromUser(User $user)
    {
        return (new static())
            ->setSendInterviewMsg($user->isSendInterviewMsg())
            ->setSendBonusMsg($user->isSendBonusMsg())
            ->setSendFeedbackMsg($user->isSendFeedbackMsg())
            ->setSendOrderStatusMsg($user->isSendOrderStatusMsg())
            ->setSendNewsMsg($user->isSendNewsMsg())
            ->setSendBonusChangeMsg($user->isSendBonusChangeMsg())
            ->setSendSmsMsg($user->isSendSmsMsg())
            ->setSendEmailMsg($user->isSendEmailMsg())
            ->setGpsAllowed($user->isGpsAllowed());
    }

    /**
     * @param User $user
     */
    public function configureUser(User $user): void
    {
        $user
            ->setSendInterviewMsg($this->getSendInterviewMsg() ?? $user->isSendInterviewMsg())
            ->setSendBonusMsg($this->getSendBonusMsg() ?? $user->isSendBonusMsg())
            ->setSendFeedbackMsg($this->getSendFeedbackMsg() ?? $user->isSendFeedbackMsg())
            ->setSendOrderStatusMsg($this->getSendOrderStatusMsg() ?? $user->isSendOrderStatusMsg())
            ->setSendNewsMsg($this->getSendNewsMsg() ?? $user->isSendNewsMsg())
            ->setSendBonusChangeMsg($this->getSendBonusChangeMsg() ?? $user->isSendBonusChangeMsg())
            ->setSendSmsMsg($this->getSendSmsMsg() ?? $user->isSendSmsMsg())
            ->setSendEmailMsg($this->getSendEmailMsg() ?? $user->isSendEmailMsg())
            ->setGpsAllowed($this->getGpsAllowed() ?? $user->isGpsAllowed());
    }

    /**
     * @return null|bool
     */
    public function getSendInterviewMsg(): ?bool
    {
        return $this->sendInterviewMsg;
    }

    /**
     * @param null|bool $sendInterviewMsg
     * @return Settings
     */
    public function setSendInterviewMsg(?bool $sendInterviewMsg): Settings
    {
        $this->sendInterviewMsg = $sendInterviewMsg;
        return $this;
    }

    /**
     * @return null|bool
     */
    public function getSendBonusMsg(): ?bool
    {
        return $this->sendBonusMsg;
    }

    /**
     * @param null|bool $sendBonusMsg
     * @return Settings
     */
    public function setSendBonusMsg(?bool $sendBonusMsg): Settings
    {
        $this->sendBonusMsg = $sendBonusMsg;
        return $this;
    }

    /**
     * @return null|bool
     */
    public function getSendFeedbackMsg(): ?bool
    {
        return $this->sendFeedbackMsg;
    }

    /**
     * @param null|bool $sendFeedbackMsg
     * @return Settings
     */
    public function setSendFeedbackMsg(?bool $sendFeedbackMsg): Settings
    {
        $this->sendFeedbackMsg = $sendFeedbackMsg;
        return $this;
    }

    /**
     * @return null|bool
     */
    public function getSendOrderStatusMsg(): ?bool
    {
        return $this->sendOrderStatusMsg;
    }

    /**
     * @param null|bool $sendOrderStatusMsg
     * @return Settings
     */
    public function setSendOrderStatusMsg(?bool $sendOrderStatusMsg): Settings
    {
        $this->sendOrderStatusMsg = $sendOrderStatusMsg;
        return $this;
    }

    /**
     * @return null|bool
     */
    public function getSendNewsMsg(): ?bool
    {
        return $this->sendNewsMsg;
    }

    /**
     * @param null|bool $sendNewsMsg
     * @return Settings
     */
    public function setSendNewsMsg(?bool $sendNewsMsg): Settings
    {
        $this->sendNewsMsg = $sendNewsMsg;
        return $this;
    }

    /**
     * @return null|bool
     */
    public function getSendBonusChangeMsg(): ?bool
    {
        return $this->sendBonusChangeMsg;
    }

    /**
     * @param null|bool $sendBonusChangeMsg
     * @return Settings
     */
    public function setSendBonusChangeMsg(?bool $sendBonusChangeMsg): Settings
    {
        $this->sendBonusChangeMsg = $sendBonusChangeMsg;
        return $this;
    }

    /**
     * @return null|bool
     */
    public function getSendSmsMsg(): ?bool
    {
        return $this->sendSmsMsg;
    }

    /**
     * @param null|bool $sendSmsMsg
     * @return Settings
     */
    public function setSendSmsMsg(?bool $sendSmsMsg): Settings
    {
        $this->sendSmsMsg = $sendSmsMsg;
        return $this;
    }

    /**
     * @return null|bool
     */
    public function getSendEmailMsg(): ?bool
    {
        return $this->sendEmailMsg;
    }

    /**
     * @param null|bool $sendEmailMsg
     * @return Settings
     */
    public function setSendEmailMsg(?bool $sendEmailMsg): Settings
    {
        $this->sendEmailMsg = $sendEmailMsg;
        return $this;
    }

    /**
     * @return null|bool
     */
    public function getGpsAllowed(): ?bool
    {
        return $this->gpsAllowed;
    }

    /**
     * @param null|bool $gpsAllowed
     * @return Settings
     */
    public function setGpsAllowed(?bool $gpsAllowed): Settings
    {
        $this->gpsAllowed = $gpsAllowed;
        return $this;
    }
}
