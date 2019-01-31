<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use FourPaws\MobileApiBundle\Dto\Parts\NewCardNumber;

class ChangeCardValidateRequest implements SimpleUnserializeRequest, PostRequest
{
    use NewCardNumber;
}
