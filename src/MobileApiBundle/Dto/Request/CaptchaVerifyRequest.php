<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use FourPaws\MobileApiBundle\Dto\Parts\Captcha;
use FourPaws\MobileApiBundle\Dto\Parts\Entity;

class CaptchaVerifyRequest implements SimpleUnserializeRequest, PostRequest
{
    use
        Captcha,
        Entity;
}
