<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use FourPaws\MobileApiBundle\Dto\Parts\Pet;
use FourPaws\MobileApiBundle\Dto\Request\Types\PostRequest;
use FourPaws\MobileApiBundle\Dto\Request\Types\SimpleUnserializeRequest;
use JMS\Serializer\Annotation as Serializer;

class UserPetAddRequest implements SimpleUnserializeRequest, PostRequest
{
    use Pet;

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("gender")
     * @var string
     */
    protected $gender;
    
    /**
     * @Serializer\Type("int")
     * @Serializer\SerializedName("size")
     * @Serializer\SkipWhenEmpty()
     * @var int
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
     * @return string
     */
    public function getGender(): ?string
    {
        return $this->gender;
    }

    /**
     * @param string $gender
     * @return $this
     */
    public function setGender(string $gender)
    {
        $this->gender = $gender;
        return $this;
    }
    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }
    
    /**
     * @param int $size
     * @return $this
     */
    public function setSize(int $size)
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
}
