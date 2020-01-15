<?php

namespace FourPaws\MobileApiBundle\Dto\Object;

use FourPaws\Decorators\FullHrefDecorator;
use FourPaws\MobileApiBundle\Dto\Parts\Pet;
use JMS\Serializer\Annotation as Serializer;

class PetSizes
{
    /**
     * @Serializer\Type("FourPaws\MobileApiBundle\Dto\Object\PetSize")
     * @Serializer\SerializedName("size")
     * @Serializer\SkipWhenEmpty()
     * @var PetSize
     */
    protected $size = 0;
    
    /**
     * @Serializer\Type("float")
     * @Serializer\SerializedName("back")
     * @Serializer\SkipWhenEmpty()
     * @var float
     */
    protected $back = 0.0;
    
    /**
     * @Serializer\Type("float")
     * @Serializer\SerializedName("neck")
     * @Serializer\SkipWhenEmpty()
     * @var float
     */
    protected $neck = 0.0;
    
    /**
     * @Serializer\Type("float")
     * @Serializer\SerializedName("chest")
     * @Serializer\SkipWhenEmpty()
     * @var float
     */
    protected $chest = 0.0;
    
    /**
     * @return PetSize
     */
    public function getSize(): PetSize
    {
        return $this->size;
    }
    
    /**
     * @param int $size
     * @return $this
     */
    public function setSize($size)
    {
        $this->size = $size;
        return $this;
    }
    
    /**
     * @return float
     */
    public function getBack(): float
    {
        return $this->back;
    }
    
    /**
     * @param float $back
     * @return $this
     */
    public function setBack(float $back)
    {
        $this->back = $back;
        return $this;
    }
    
    /**
     * @return float
     */
    public function getNeck(): float
    {
        return $this->neck;
    }
    
    /**
     * @param float $neck
     * @return $this
     */
    public function setNeck(float $neck)
    {
        $this->neck = $neck;
        return $this;
    }
    
    /**
     * @return float
     */
    public function getChest(): float
    {
        return $this->chest;
    }
    
    /**
     * @param float $chest
     * @return $this
     */
    public function setChest(float $chest)
    {
        $this->chest = $chest;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getSizeTitle(): string
    {
        return $this->sizeTitle;
    }
    
    /**
     * @param string $sizeTitle
     * @return $this
     */
    public function setSizeTitle(string $sizeTitle)
    {
        $this->sizeTitle = $sizeTitle;
        return $this;
    }
}
