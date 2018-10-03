<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Enum;

class OrderAvailability
{
    public const AVAILABLE = 'full';
    public const PARTIAL   = 'parts';
    public const SPLIT     = 'split';
    public const DELAYED   = 'delay';
}
