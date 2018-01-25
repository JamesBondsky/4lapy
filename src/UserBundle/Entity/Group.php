<?php

namespace FourPaws\UserBundle\Entity;

use JMS\Serializer\Annotation as Serializer;

class Group
{
    /**
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("GROUP_ID")
     * @var int
     */
    protected $id;

    /**
     * @Serializer\Type("bitrix_bool")
     * @Serializer\SerializedName("GROUP_ACTIVE")
     * @var bool
     */
    protected $active = false;

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("GROUP_CODE")
     * @var string
     */
    protected $code = '';

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("GROUP_NAME")
     * @var string
     */
    protected $name = '';

    /**
     * @return int
     */
    public function getId(): int
    {
        return (int)$this->id;
    }

    /**
     * @param int $id
     *
     * @return Group
     */
    public function setId(int $id): Group
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return (bool)$this->active;
    }

    /**
     * @param bool $active
     *
     * @return Group
     */
    public function setActive(bool $active): Group
    {
        $this->active = $active;
        return $this;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return (string)$this->code;
    }

    /**
     * @param string $code
     *
     * @return Group
     */
    public function setCode(string $code): Group
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return (string)$this->name;
    }

    /**
     * @param string $name
     *
     * @return Group
     */
    public function setName(string $name): Group
    {
        $this->name = $name;
        return $this;
    }
}
