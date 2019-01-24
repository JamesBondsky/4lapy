<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Dto\Request;

use FourPaws\MobileApiBundle\Dto\Parts\CardNumber;

class CardActivatedRequest implements GetRequest, SimpleUnserializeRequest
{
    use CardNumber;
}
