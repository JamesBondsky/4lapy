<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use FourPaws\MobileApiBundle\Dto\Request\Types\PostRequest;
use FourPaws\MobileApiBundle\Dto\Request\Types\SimpleUnserializeRequest;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class QuestQuestionRequest implements SimpleUnserializeRequest, PostRequest
{
    /**
     * @Serializer\SerializedName("variant_id")
     * @Serializer\Type("int")
     * @Assert\NotBlank()
     * @var $variantId
     */
    protected $variantId;

    /**
     * @return mixed
     */
    public function getVariantId()
    {
        return $this->variantId;
    }

    /**
     * @param mixed $variantId
     * @return QuestQuestionRequest
     */
    public function setVariantId($variantId): QuestQuestionRequest
    {
        $this->variantId = $variantId;
        return $this;
    }
}
