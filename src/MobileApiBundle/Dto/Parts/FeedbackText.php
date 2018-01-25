<?php

namespace FourPaws\MobileApiBundle\Dto\Parts;

use JMS\Serializer\Annotation as Serializer;

trait FeedbackText
{
    /**
     * @Serializer\SerializedName("feedback_text")
     * @Serializer\Type("string")
     * @var string
     */
    protected $feedbackText = '';

    /**
     * @return string
     */
    public function getFeedbackText(): string
    {
        return $this->feedbackText;
    }

    /**
     * @param string $feedbackText
     * @return FeedbackText
     */
    public function setFeedbackText(string $feedbackText): FeedbackText
    {
        $this->feedbackText = $feedbackText;
        return $this;
    }
}
