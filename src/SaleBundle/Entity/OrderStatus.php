<?php

/**
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\SaleBundle\Entity;

use FourPaws\App\Application;
use FourPaws\SaleBundle\Collection\OrderPropertyVariantCollection;
use FourPaws\SaleBundle\Service\OrderPropertyService;
use JMS\Serializer\Annotation as Serializer;

class OrderStatus
{
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("STATUS_ID")
     * @Serializer\Groups(groups={"read", "update", "delete"})
     */
    protected $id;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("NAME")
     * @Serializer\Groups(groups={"read", "update", "delete"})
     */
    protected $name;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("DESCRIPTION")
     * @Serializer\Groups(groups={"read", "update", "delete"})
     */
    protected $description = '';

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return OrderStatus
     */
    public function setId(string $id): OrderStatus
    {
        $this->id = $id;
        return $this;
    }


    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return OrderStatus
     */
    public function setName(string $name): OrderStatus
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return OrderStatus
     */
    public function setDescription(string $description): OrderStatus
    {
        $this->description = $description;
        return $this;
    }
}
