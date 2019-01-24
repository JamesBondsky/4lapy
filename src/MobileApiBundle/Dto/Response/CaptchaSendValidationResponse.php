<?php

namespace FourPaws\MobileApiBundle\Dto\Response;

use FourPaws\MobileApiBundle\Dto\Parts\CaptchaId;
use FourPaws\MobileApiBundle\Dto\Parts\FeedbackText;

class CaptchaSendValidationResponse
{
    use FeedbackText, CaptchaId;
}
