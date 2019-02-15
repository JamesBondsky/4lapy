<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use FourPaws\MobileApiBundle\Dto\Parts\CardProfile;
use FourPaws\MobileApiBundle\Dto\Parts\NewCardNumber;
use FourPaws\MobileApiBundle\Dto\Request\Types\GetRequest;
use FourPaws\MobileApiBundle\Dto\Request\Types\SimpleUnserializeRequest;

class UserAddCartRequest implements SimpleUnserializeRequest, GetRequest
{
    use NewCardNumber, CardProfile;
}
