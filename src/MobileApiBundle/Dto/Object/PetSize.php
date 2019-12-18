<?php

namespace FourPaws\MobileApiBundle\Dto\Object;

use FourPaws\Decorators\FullHrefDecorator;
use FourPaws\MobileApiBundle\Dto\Parts\Pet;
use JMS\Serializer\Annotation as Serializer;

class PetSize
{
    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("title")
     * @Serializer\SkipWhenEmpty()
     * @var string
     */
    protected $title = '';
    
    /**
     * @Serializer\Type("int")
     * @Serializer\SerializedName("id")
     * @Serializer\SkipWhenEmpty()
     * @var int
     */
    protected $id = 0;
   
    /**
     * @return int
     */
    public function getId(): int
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
    public function getTitle(): string
    {
        return $this->title;
    }
    
    /**
     * @param string $title
     * @return $this
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
        return $this;
    }
}
