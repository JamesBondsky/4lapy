<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use FourPaws\MobileApiBundle\Dto\Request\Types\PostRequest;
use FourPaws\MobileApiBundle\Dto\Request\Types\SimpleUnserializeRequest;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class QuestBarcodeRequest implements SimpleUnserializeRequest, PostRequest
{
    /**
     * @Serializer\SerializedName("vendor_code")
     * @Serializer\Type("string")
     * @Assert\NotBlank()
     * @var $vendorCode
     */
    protected $vendorCode;

    /**
     * @return mixed
     */
    public function getVendorCode()
    {
        return $this->vendorCode;
    }

    /**
     * @param mixed $vendorCode
     * @return QuestBarcodeRequest
     */
    public function setVendorCode($vendorCode): QuestBarcodeRequest
    {
        $this->vendorCode = $vendorCode;
        return $this;
    }
}
