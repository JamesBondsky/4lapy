<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use FourPaws\MobileApiBundle\Dto\Object\Review;
use FourPaws\MobileApiBundle\Dto\Request\Types\PostRequest;
use FourPaws\MobileApiBundle\Dto\Request\Types\SimpleUnserializeRequest;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class FeedbackRequest implements SimpleUnserializeRequest, PostRequest
{
    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("type")
     * @Assert\NotBlank()
     * @Assert\Choice({"email", "callback"})
     * @var string
     */
    protected $type;

    /**
     * @Serializer\Type("FourPaws\MobileApiBundle\Dto\Object\Review")
     * @Serializer\SerializedName("review")
     * @Assert\Valid()
     * @var Review;
     */
    protected $review;


    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return Review
     */
    public function getReview()
    {
        return $this->review;
    }
}
