<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Dto\Response;

use JMS\Serializer\Annotation as Serializer;

class FeedbackResponse
{
    /**
     * @Serializer\SerializedName("feedback_text")
     * @Serializer\Type("string")
     * @var string
     */
    protected $feedbackText = '';

    public function __construct(string $feedbackText)
    {
        $this->feedbackText = $feedbackText;
    }

    /**
     * @return string
     */
    public function getFeedbackText(): string
    {
        return $this->feedbackText;
    }

    /**
     * @param string $feedbackText
     * @return FeedbackResponse
     */
    public function setFeedbackText(string $feedbackText): FeedbackResponse
    {
        $this->feedbackText = $feedbackText;
        return $this;
    }
}
