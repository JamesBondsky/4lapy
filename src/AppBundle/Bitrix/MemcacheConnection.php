<?php

namespace FourPaws\AppBundle\Bitrix;

use FourPaws\AppBundle\Exception\MemcacheException;
use Memcache;


/**
 * Class MemcacheConnection
 *
 * @package FourPaws\AppBundle\Bitrix
 */
final class MemcacheConnection
{
    private const DEFAULT_MEMCACHE_HOST = 'localhost';
    private const DEFAULT_MEMCACHE_PORT = 11211;

    /**
     * @var Memcache
     */
    private static $memcache;

    /**
     * MemcacheConnection constructor.
     *
     * @throws MemcacheException
     */
    public function __construct()
    {
        /** @noinspection PhpUndefinedConstantInspection */
        $port = \defined('BX_MEMCACHE_PORT') && (int)\BX_MEMCACHE_PORT ? (int)\BX_MEMCACHE_PORT : self::DEFAULT_MEMCACHE_PORT;
        /** @noinspection PhpUndefinedConstantInspection */
        $host = \defined('BX_MEMCACHE_HOST') && (int)\BX_MEMCACHE_HOST ? (int)\BX_MEMCACHE_HOST : self::DEFAULT_MEMCACHE_HOST;

        if (!self::$memcache) {
            $memcache = new Memcache();
            if (!$memcache->connect($host, $port)) {
                throw new MemcacheException('Memcache is not configured or down');
            }

            self::$memcache = $memcache;
            register_shutdown_function(self::class, 'closeConnection');
        }
    }

    /**
     * Close
     */
    public function closeConnection(): void
    {
        if (self::$memcache) {
            self::$memcache->close();
        }
    }

    /**
     * @return Memcache
     */
    public function getConnection(): Memcache
    {
        return self::$memcache;
    }
}
