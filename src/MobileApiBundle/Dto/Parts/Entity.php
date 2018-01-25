<?php

namespace FourPaws\MobileApiBundle\Dto\Parts;

use JMS\Serializer\Annotation as Serializer;

trait Entity
{
    /**
     * @todo constraint
     * @Serializer\SerializedName("entity")
     * @Serializer\Type("string")
     * @var string
     */
    protected $entity;

    /**
     * @return string
     */
    public function getEntity(): string
    {
        return $this->entity;
    }

    /**
     * @param string $entity
     * @return Entity
     */
    public function setEntity(string $entity): Entity
    {
        $this->entity = $entity;
        return $this;
    }
}
