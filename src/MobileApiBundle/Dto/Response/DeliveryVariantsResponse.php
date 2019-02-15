<?php

namespace FourPaws\MobileApiBundle\Dto\Response;

use JMS\Serializer\Annotation as Serializer;
use FourPaws\MobileApiBundle\Dto\Object\DeliveryVariants;

class DeliveryVariantsResponse
{
    /**
     * @Serializer\Type("FourPaws\MobileApiBundle\Dto\Object\DeliveryVariants")
     * @Serializer\SerializedName("deliveryVariants")
     * @var DeliveryVariants
     */
    protected $deliveryVariants;

    public function __construct(DeliveryVariants $deliveryVariants)
    {
        $this->deliveryVariants = $deliveryVariants;
    }

    /**
     * @return DeliveryVariants
     */
    public function getBannerList(): DeliveryVariants
    {
        return $this->deliveryVariants;
    }

    /**
     * @param DeliveryVariants $deliveryVariants
     *
     * @return DeliveryVariantsResponse
     */
    public function setBannerList(DeliveryVariants $deliveryVariants): DeliveryVariantsResponse
    {
        $this->deliveryVariants = $deliveryVariants;
        return $this;
    }

}
