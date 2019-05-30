<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Dto\Response;

use FourPaws\MobileApiBundle\Dto\Parts\FeedbackText;
use JMS\Serializer\Annotation as Serializer;

class CardActivatedResponse
{
    use FeedbackText;
    /**
     * @Serializer\Type("boolean")
     * @Serializer\SerializedName("activated")
     * @var bool
     */
    protected $activated = false;

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
}
