<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use FourPaws\MobileApiBundle\Dto\Parts\CardProfile;
use FourPaws\MobileApiBundle\Dto\Parts\NewCardNumber;

class UserAddCartRequest implements SimpleUnserializeRequest, GetRequest
{
    use NewCardNumber, CardProfile;
}
