<?php

namespace FourPaws\AppBundle\Entity;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class BaseEntity
 *
 * @package FourPaws\AppBundle\Entity
 */
abstract class BaseEntity
{
    const BITRIX_TRUE  = 'Y';
    
    const BITRIX_FALSE = 'N';
    
    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("ID")
     * @Serializer\Groups(groups={"read","update","delete"})
     * @Assert\NotBlank(groups={"read","update","delete"})
     * @Assert\GreaterThanOrEqual(value="1",groups={"read","update","delete"})
     */
    protected $id;
    
    /**
     * @return int
     */
    public function getId() : int
    {
        return $this->id ?? 0;
    }
    
    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }
}