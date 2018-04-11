<?php

namespace FourPaws\AppBundle\Service;

use FourPaws\AppBundle\Bitrix\MemcacheConnection;
use FourPaws\AppBundle\Exception\MemcacheException;
use Memcache;

/**
 * Class LockerMemcacheService
 *
 * @package FourPaws\AppBundle\Service
 */
class LockerMemcacheService implements LockerInterface
{
    private const STATUS_LOCKED = 'l';
    private const STATUS_UNLOCKED = 'u';

    /**
     * @var MemcacheConnection
     */
    private $memcacheConnection;

    /**
     * LockerMemcacheService constructor.
     *
     * @param MemcacheConnection $connection
     */
    public function __construct(MemcacheConnection $connection)
    {
        $this->memcacheConnection = $connection;
    }

    /**
     * @param string $target
     *
     * @throws MemcacheException
     */
    public function lock(string $target): void
    {
        $this->getConnection()->set($this->buildKey($target), self::STATUS_LOCKED);
    }

    /**
     * @param string $target
     *
     * @throws MemcacheException
     */
    public function unlock(string $target): void
    {
        $this->getConnection()->set($this->buildKey($target), self::STATUS_UNLOCKED);
    }

    /**
     * @param string $target
     *
     * @return bool
     *
     * @throws MemcacheException
     */
    public function isLocked(string $target): bool
    {
        return $this->getConnection()->get($this->buildKey($target)) === self::STATUS_LOCKED;
    }

    /**
     * @param string $target
     *
     * @return string
     */
    protected function buildKey(string $target): string
    {
        return md5(\sprintf(
            '%s|%s',
            self::class, $target
        ));
    }

    /**
     * @return Memcache
     *
     * @throws MemcacheException
     */
    public function getConnection(): Memcache
    {
        return $this->memcacheConnection->getConnection();
    }
}
