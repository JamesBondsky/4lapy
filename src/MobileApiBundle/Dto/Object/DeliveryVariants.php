<?php

namespace FourPaws\MobileApiBundle\Dto\Object;

use JMS\Serializer\Annotation as Serializer;

class DeliveryVariants
{
    /**
     * @Serializer\Type("bool")
     * @Serializer\SerializedName("hasCourier")
     * @var bool
     */
    public $hasCourier;

    /**
     * @Serializer\Type("bool")
     * @Serializer\SerializedName("hasPickup")
     * @var bool
     */
    public $hasPickup;
}
