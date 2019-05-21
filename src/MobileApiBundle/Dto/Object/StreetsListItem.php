<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Dto\Object;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class StreetsListItem
 *
 * @package FourPaws\MobileApiBundle\Dto\Object
 *
 */
class StreetsListItem
{
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("id")
     * @Assert\NotBlank()
     */
    protected $id;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("title")
     * @Assert\NotBlank()
     */
    protected $street;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return StreetsListItem
     */
    public function setId(string $id): StreetsListItem
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getStreet(): string
    {
        return $this->street;
    }

    /**
     * @param string $street
     *
     * @return StreetsListItem
     */
    public function setStreet(string $street): StreetsListItem
    {
        $this->street = $street;
        return $this;
    }
}
