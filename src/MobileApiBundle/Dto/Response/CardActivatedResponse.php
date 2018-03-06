<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Dto\Response;

use JMS\Serializer\Annotation as Serializer;

class CardActivatedResponse
{
    /**
     * @Serializer\Type("boolean")
     * @Serializer\SerializedName("activated")
     * @var bool
     */
    protected $activated = false;

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("feedback_text")
     * @Serializer\SkipWhenEmpty()
     * @var string
     */
    protected $feedbackText = '';

    public function __construct(
        bool $activated = false,
        string $feedbackText = ''
    ) {
        $this->setActivated($activated);
        $this->setFeedbackText($feedbackText);
    }

    /**
     * @param bool $activated
     *
     * @return CardActivatedResponse
     */
    public function setActivated(bool $activated): CardActivatedResponse
    {
        $this->activated = $activated;
        return $this;
    }

    /**
     * @param string $feedbackText
     *
     * @return CardActivatedResponse
     */
    public function setFeedbackText(string $feedbackText): CardActivatedResponse
    {
        $this->feedbackText = $feedbackText;
        return $this;
    }
}
