<?php

namespace FourPaws\AppBundle\Service;

/**
 * Interface LockerInterface
 *
 * @package FourPaws\AppBundle\Service
 */
interface LockerInterface
{
    /**
     * Lock something
     *
     * @param string $target
     *
     * @return void
     */
    public function lock(string $target): void;

    /**
     * Unlock something
     *
     * @param string $target
     *
     * @return void
     */
    public function unlock(string $target): void;

    /**
     * Something is already locked
     *
     * @param string $target
     *
     * @return bool
     */
    public function isLocked(string $target): bool;
}
