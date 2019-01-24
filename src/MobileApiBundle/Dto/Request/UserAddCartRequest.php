<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use FourPaws\MobileApiBundle\Dto\Parts\CardNumber;
use FourPaws\MobileApiBundle\Dto\Parts\CardProfile;
use JMS\Serializer\Annotation as Serializer;

class UserAddCartRequest implements SimpleUnserializeRequest, GetRequest
{
    use CardNumber, CardProfile;

    /**
     * @Serializer\SerializedName("middle_name")
     * @Serializer\Type("string")
     * @var string
     */
    protected $middleName = '';
}
