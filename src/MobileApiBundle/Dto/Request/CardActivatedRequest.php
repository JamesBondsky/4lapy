<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Dto\Request;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class CardActivatedRequest implements GetRequest, SimpleUnserializeRequest
{
    /**
     * @Assert\NotBlank()
     * @Assert\Length(min="13", max="13")
     * @Assert\Regex("/^(26|27)/")
     * @Serializer\Type("string")
     * @Serializer\SerializedName("number")
     * @var string
     */
    protected $number = '';

    /**
     * @return string
     */
    public function getNumber(): string
    {
        return $this->number;
    }
}
