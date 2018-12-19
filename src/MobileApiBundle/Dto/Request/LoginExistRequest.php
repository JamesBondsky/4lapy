<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Dto\Request;

use FourPaws\MobileApiBundle\Dto\Parts\Login;

class LoginExistRequest implements SimpleUnserializeRequest, GetRequest
{
    use Login;
}
