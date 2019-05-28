<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use FourPaws\MobileApiBundle\Dto\Parts\Captcha;
use FourPaws\MobileApiBundle\Dto\Parts\Login;
use FourPaws\MobileApiBundle\Dto\Request\Types\PostRequest;
use FourPaws\MobileApiBundle\Dto\Request\Types\SimpleUnserializeRequest;

class CaptchaVerifyRequest implements SimpleUnserializeRequest, PostRequest
{
    use
        Captcha,
        Login;
}
