<?php

namespace FourPaws\StoreBundle\Entity;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

abstract class Base implements \Serializable
{
    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("ID")
     * @Serializer\Groups(groups={"read","update","delete"})
     * @Assert\NotBlank(groups={"read","update","delete"})
     * @Assert\GreaterThanOrEqual(value="1",groups={"read","update","delete"})
     * @Assert\Blank(groups={"create"})
     */
    protected $id;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setId(int $id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function serialize(): string
    {
        return serialize([
            $this->id
        ]);
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized): void
    {
        [
            $this->id
        ] = unserialize($serialized, ['allowed_classes' => false]);
    }
}
