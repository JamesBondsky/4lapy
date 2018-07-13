<?php

namespace FourPaws\EcommerceBundle\Dto\GoogleEcommerce;

use JMS\Serializer\Annotation as Serializer;

/**
 * Class Promotion
 *
 * @package FourPaws\EcommerceBundle\Dto\GoogleEcommerce
 */
class Promotion
{
    /**
     * Id
     *
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $id;

    /**
     * Название
     *
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $name;

    /**
     * Название креатива
     *
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $creative;

    /**
     * Номер в списке/позиция
     *
     * @Serializer\Type("string")
     * @Serializer\SkipWhenEmpty()
     *
     * @var string
     */
    protected $position;

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
     * @return Promotion
     */
    public function setId(string $id): Promotion
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
     *
     * @return Promotion
     */
    public function setName(string $name): Promotion
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getCreative(): string
    {
        return $this->creative;
    }

    /**
     * @param string $creative
     *
     * @return Promotion
     */
    public function setCreative(string $creative): Promotion
    {
        $this->creative = $creative;

        return $this;
    }

    /**
     * @return string
     */
    public function getPosition(): string
    {
        return $this->position;
    }

    /**
     * @param string $position
     *
     * @return Promotion
     */
    public function setPosition(string $position): Promotion
    {
        $this->position = $position;

        return $this;
    }
}
