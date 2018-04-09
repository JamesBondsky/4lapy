<?php

namespace FourPaws\AppBundle\Service;

use FourPaws\AppBundle\Bitrix\MemcacheConnection;
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
     * @var Memcache
     */
    private $connection;

    /**
     * LockerMemcacheService constructor.
     *
     * @param MemcacheConnection $connection
     */
    public function __construct(MemcacheConnection $connection)
    {
        $this->connection = $connection->getConnection();
    }

    /**
     * @param string $target
     */
    public function lock(string $target): void
    {
        $this->connection->set($this->buildKey($target), self::STATUS_LOCKED);
    }

    /**
     * @param string $target
     */
    public function unlock(string $target): void
    {
        $this->connection->set($this->buildKey($target), self::STATUS_UNLOCKED);
    }

    /**
     * @param string $target
     *
     * @return bool
     */
    public function isLocked(string $target): bool
    {
        return $this->connection->get($this->buildKey($target)) === self::STATUS_LOCKED;
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
}
