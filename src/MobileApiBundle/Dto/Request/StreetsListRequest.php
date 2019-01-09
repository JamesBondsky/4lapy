<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class StoreListRequest
 * @package FourPaws\MobileApiBundle\Dto\Request
 */
class StreetsListRequest implements SimpleUnserializeRequest, GetRequest
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
     * @Serializer\SerializedName("search_term")
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
     * @return StreetsListRequest
     */
    public function setId(string $id): StreetsListRequest
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
     * @return StreetsListRequest
     */
    public function setStreet(string $street): StreetsListRequest
    {
        $this->street = $street;
        return $this;
    }
}
