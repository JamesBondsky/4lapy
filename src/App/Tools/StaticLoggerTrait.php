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
        try {
            $name = (new \ReflectionClass(static::class))->getShortName();
        } catch (\ReflectionException $e) {
            $name = \basename(\str_replace('\\', '/', static::class));
        }

        return LoggerFactory::create($name);
    }
}