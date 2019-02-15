<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use FourPaws\MobileApiBundle\Dto\Parts\NewCardNumber;
use FourPaws\MobileApiBundle\Dto\Request\Types\PostRequest;
use FourPaws\MobileApiBundle\Dto\Request\Types\SimpleUnserializeRequest;

class ChangeCardValidateRequest implements SimpleUnserializeRequest, PostRequest
{
    use NewCardNumber;
}
