<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Dto\Request;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class GoodsSearchBarcodeRequest
{
    /**
     * @Assert\Type("string")
     * @Assert\NotBlank()
     * @Serializer\SerializedName("barcode")
     * @Serializer\Type("string")
     * @var string
     */
    protected $barcode = '';

    /**
     * @return string
     */
    public function getBarcode(): string
    {
        return $this->barcode;
    }
}
