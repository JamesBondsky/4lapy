<?php

namespace FourPaws\MobileApiBundle\Dto\Response;

use FourPaws\MobileApiBundle\Dto\Object\Banner;
use FourPaws\MobileApiBundle\Dto\Object\Order;
use JMS\Serializer\Annotation as Serializer;

class PayResponse
{
    /**
     * URL для редиректа на форму оплаты
     * @Serializer\Type("string")
     * @Serializer\SerializedName("formUrl")
     * @var string
     */
    protected $formUrl;

    /**
     * @return string
     */
    public function getFormUrl(): string
    {
        return $this->formUrl;
    }

    /**
     * @param string $formUrl
     * @return PayResponse
     */
    public function setFormUrl(string $formUrl): PayResponse
    {
        $this->formUrl = $formUrl;
        return $this;
    }
}
