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
     *
     * @throws MemcacheException
     */
    public function getConnection(): Memcache
    {
        if (null === self::$memcache) {
            $this->createConnection();
        }

        return self::$memcache;
    }

    /**
     * @throws MemcacheException
     */
    private function createConnection(): void
    {
        if (!self::$memcache) {
            /** @noinspection PhpUndefinedConstantInspection */
            $port = \defined('BX_MEMCACHE_PORT') && (int)\BX_MEMCACHE_PORT ? (int)\BX_MEMCACHE_PORT : self::DEFAULT_MEMCACHE_PORT;
            /** @noinspection PhpUndefinedConstantInspection */
            $host = \defined('BX_MEMCACHE_HOST') && \BX_MEMCACHE_HOST ? \BX_MEMCACHE_HOST : self::DEFAULT_MEMCACHE_HOST;

            $memcache = new Memcache();
            if (!$memcache->connect($host, $port)) {
                throw new MemcacheException('Memcache is not configured or down');
            }

            self::$memcache = $memcache;
            register_shutdown_function(self::class, 'closeConnection');
        }
    }
}
