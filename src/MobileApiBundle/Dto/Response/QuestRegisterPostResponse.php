<?php

namespace FourPaws\MobileApiBundle\Dto\Response;

use FourPaws\MobileApiBundle\Dto\Object\Quest\Pet;
use JMS\Serializer\Annotation as Serializer;

class QuestRegisterPostResponse
{
    /**
     * @Serializer\SerializedName("pet_types")
     * @Serializer\Type("array<FourPaws\MobileApiBundle\Dto\Object\Quest\Pet>")
     * @var Pet[]
     */
    protected $petTypes = [];

    /**
     * @return Pet[]
     */
    public function getPetTypes(): array
    {
        return $this->petTypes;
    }

    /**
     * @param Pet[] $petTypes
     * @return QuestRegisterPostResponse
     */
    public function setPetTypes(array $petTypes): QuestRegisterPostResponse
    {
        $this->petTypes = $petTypes;
        return $this;
    }
}
