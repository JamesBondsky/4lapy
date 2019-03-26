<?php

namespace FourPaws\MobileApiBundle\Dto\Object;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Объект баннер
 * Class Review
 * @package FourPaws\MobileApiBundle\Dto\Object
 */
class Review
{
    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("title")
     * @var string
     */
    protected $title;

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("email")
     * @Assert\Email()
     * @var string
     */
    protected $email;

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("phone")
     * @var string
     */
    protected $phone;

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("summary")
     * @var string
     */
    protected $summary;

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @return string
     */
    public function getSummary()
    {
        return $this->summary;
    }
}
