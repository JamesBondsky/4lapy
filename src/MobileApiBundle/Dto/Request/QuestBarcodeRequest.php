<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use FourPaws\MobileApiBundle\Dto\Request\Types\PostRequest;
use FourPaws\MobileApiBundle\Dto\Request\Types\SimpleUnserializeRequest;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class QuestBarcodeRequest implements SimpleUnserializeRequest, PostRequest
{
    /**
     * @Serializer\SerializedName("barcode")
     * @Serializer\Type("string")
     * @Assert\NotBlank()
     * @var $barcode
     */
    protected $barcode;

    /**
     * @return mixed
     */
    public function getBarcode()
    {
        return $this->barcode;
    }

    /**
     * @param $barcode
     * @return QuestBarcodeRequest
     */
    public function setBarcode($barcode): QuestBarcodeRequest
    {
        $this->barcode = $barcode;
        return $this;
    }
}
