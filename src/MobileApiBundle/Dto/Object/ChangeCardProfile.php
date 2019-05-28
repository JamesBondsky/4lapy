<?php

namespace FourPaws\MobileApiBundle\Dto\Object;

use FourPaws\MobileApiBundle\Dto\Parts\CardProfile;
use FourPaws\MobileApiBundle\Dto\Parts\NewCardNumber;

/**
 * Объект ЗаменаКартыПрофиль
 * Class ChangeCardProfile
 * @package FourPaws\MobileApiBundle\Dto\Object
 */
class ChangeCardProfile
{
    use NewCardNumber, CardProfile;
}
