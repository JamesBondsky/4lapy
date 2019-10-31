<?php

namespace FourPaws\MobileApiBundle\Dto\Object\Quest;

use JMS\Serializer\Annotation as Serializer;

class AnswerVariant
{
    /**
     * @Serializer\SerializedName("id")
     * @Serializer\Type("int")
     * @var int
     */
    protected $id = 0;

    /**
     * @Serializer\SerializedName("variant")
     * @Serializer\Type("string")
     * @var string
     */
    protected $variant = '';

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return AnswerVariant
     */
    public function setId(int $id): AnswerVariant
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getVariant(): string
    {
        return $this->variant;
    }

    /**
     * @param string $variant
     * @return AnswerVariant
     */
    public function setVariant(string $variant): AnswerVariant
    {
        $this->variant = $variant;
        return $this;
    }
}
