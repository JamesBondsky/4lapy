<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\App\Tools;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Psr\Log\LoggerInterface;

trait StaticLoggerTrait
{
    protected static function getLogger(): LoggerInterface {
        return LoggerFactory::create(str_replace('\\', '_', static::class));
    }
}